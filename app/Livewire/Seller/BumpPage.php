<?php

namespace App\Livewire\Seller;

use App\Livewire\Concerns\NotifiesOnValidation;
use App\Models\BumpPlan;
use App\Models\ProductBump;
use App\Models\Shop;
use App\Models\ShopBump;
use App\Models\Wallet;
use App\Support\ActivityLogger;
use App\Support\Bump\BumpService;
use App\Support\Fmt;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Seller — «نردبان» (v0.25.0, plan-based):
 *
 *   ① نردبانِ خودِ فروشگاه (client: «نردبان خود فروشگاه هم به صفحه نردبان
 *      اضافه کن، که فروشنده بتونه خود فروشگاهش را هم نردبان کنه») — کارتِ
 *      فروشگاه با دکمهٔ خرید طرح؛ پرداخت از کیف پول؛ تمدید = انباشت از
 *      انتهای نردبان فعلی.
 *   ② فهرستِ نردبان‌شده‌ها با جستجو و lazy load (client: «در صفحه نردبان،
 *      محصولاتی که نردبان میشوند را نمایش بده با لیزی لودینگ و سرچ») —
 *      «افزودن نردبان» از این صفحه رفت و روی کارت محصول در «فروشگاه من»
 *      نشست (ShopIndex).
 *
 * When the admin's master toggle is OFF («تنظیمات فروشگاه‌ها»), the whole
 * section is hidden from the seller panel (home strip) and this page
 * shows a friendly disabled state.
 */
#[Layout('layouts.seller-slot')]
class BumpPage extends Component
{
    use NotifiesOnValidation;

    /* ── shop purchase confirm modal ── */
    public bool $buyShopOpen = false;

    public ?int $buyShopPlanId = null;

    /* ── bumped list (search + lazy load) ── */
    public string $bumpSearch = '';

    public int $perPage = 8;

    public bool $hasMore = true;

    private int $lastLoadAt = 0;

    /* ───────────── shop bump purchase ───────────── */

    public function openShopBuy(): void
    {
        if (! BumpService::enabled()) {
            return;
        }

        $this->resetErrorBag();
        $this->buyShopPlanId = BumpService::plans()->first()?->id;
        $this->buyShopOpen = true;
    }

    public function closeShopBuy(): void
    {
        $this->buyShopOpen = false;
        $this->buyShopPlanId = null;
        $this->resetErrorBag();
    }

    public function setShopPlan(int $planId): void
    {
        $this->buyShopPlanId = BumpService::plans()->contains('id', $planId) ? $planId : $this->buyShopPlanId;
    }

    /** Confirm modal → debit the wallet and start/extend the SHOP bump. */
    public function confirmShopBuy(): void
    {
        $seller = auth('seller')->user();
        $plan = BumpPlan::query()->where('is_active', true)->find($this->buyShopPlanId);

        if ($seller === null || $plan === null) {
            $this->closeShopBuy();

            return;
        }

        [$ok, $row, $error] = BumpService::purchaseShopFromWallet($seller, $plan);

        if (! $ok) {
            $this->addError('buyShop', $error ?? 'خرید نردبان فروشگاه انجام نشد.');

            return;
        }

        $shop = Shop::ensureForSeller($seller);

        ActivityLogger::log(
            'bump.purchased',
            'فروشنده @'.($seller->instagram_username ?: $seller->phone).' فروشگاه «'.$shop->name.'» را با طرح «'.$plan->title.'» ('.$plan->daysLabel().') نردبان کرد — '.Fmt::money($row->price_paid),
            subject: $row,
            after: ['shop_id' => $shop->id, 'bump_plan_id' => $plan->id, 'ends_at' => $row->ends_at->toDateTimeString()],
        );

        $this->buyShopOpen = false;
        $this->buyShopPlanId = null;

        session()->flash('bump-flash', 'نردبان فروشگاه «'.$shop->name.'» فعال شد 🚀 مبلغ از کیف پول شما کسر شد.');
    }

    /* ───────────── bumped list (search + lazy load) ───────────── */

    public function updatingBumpSearch(): void
    {
        $this->perPage = 8;
        $this->hasMore = true;
    }

    /** Triggered by the IntersectionObserver sentinel at the bottom of the list. */
    #[On('bump-load-more')]
    public function loadMore(): void
    {
        $now = (int) (microtime(true) * 1000);

        if ($now - $this->lastLoadAt < 500) {
            return;
        }

        $this->lastLoadAt = $now;
        $this->perPage += 8;
    }

    /* ───────────────────────── data ───────────────────────── */

    /** The seller's bump rows (products only), searchable + paginated. */
    public function getBumpRowsProperty(): array
    {
        $seller = auth('seller')->user();
        $sellerId = (int) $seller?->id;
        $search = trim($this->bumpSearch);

        $query = ProductBump::query()
            ->where('seller_id', $sellerId)
            ->when($search !== '', fn ($q) => $q->whereHas('product', fn ($p) => $p->where('name', 'like', '%'.$search.'%')))
            ->with('product:id,name,shop_id')
            ->orderByDesc('ends_at') // running bumps (future ends) naturally sit above ended ones
            ->orderByDesc('id');

        $total = (clone $query)->count();
        $limit = min(max($this->perPage, 1), 60);
        $rows = $query->limit($limit)->get();

        $this->hasMore = $total > $limit;

        return $rows->all();
    }

    /** The plan chosen in the shop-purchase confirm modal (price preview). */
    public function getBuyShopPlanProperty(): ?BumpPlan
    {
        return $this->buyShopPlanId !== null ? BumpPlan::query()->find($this->buyShopPlanId) : null;
    }

    public function render()
    {
        $seller = auth('seller')->user();
        $shop = Shop::ensureForSeller($seller);

        $shopBump = ShopBump::runningForShop((int) $shop->id);
        $activeCount = ProductBump::query()
            ->where('seller_id', (int) $seller?->id)
            ->where('ends_at', '>', now())
            ->count();

        $wallet = $seller ? Wallet::ensureForSeller($seller->id) : null;

        return view('livewire.seller.bump-page', [
            'enabled' => BumpService::enabled(),
            'plans' => BumpService::plans(),
            'shop' => $shop,
            'shopBump' => $shopBump,
            'activeCount' => $activeCount,
            'bumpRows' => $this->bumpRows,
            'walletBalance' => (int) ($wallet?->balance ?? 0),
            'buyShopPlan' => $this->buyShopPlan,
        ])->title('نردبان');
    }
}
