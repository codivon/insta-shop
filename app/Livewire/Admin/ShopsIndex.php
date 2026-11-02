<?php

namespace App\Livewire\Admin;

use App\Livewire\Admin\Concerns\GuardsPermissions;
use App\Livewire\Concerns\NotifiesOnValidation;

use App\Models\Seller;
use App\Models\Shop;
use App\Models\WalletTransaction;
use App\Support\ActivityLogger;
use App\Support\Fmt;
use App\Support\Phone;
use App\Support\Wallet\WalletService;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Admin — shops management (list / search / activate-pause) with FULL
 * control for the admin: create a shop for any seller, edit every shop
 * field and delete shops — «همه چیز برای مدیر امکان‌پذیر است».
 */
#[Layout('layouts.admin-slot')]
class ShopsIndex extends Component
{
    use GuardsPermissions;
    use NotifiesOnValidation;

    protected static string $permission = 'admin.shops';
    public string $search = '';

    #[Url]
    public string $status = 'all'; // all|active|paused

    public int $perPage = 12;

    public bool $hasMore = true;

    private int $lastLoadAt = 0;

    public function updating(string $property): void
    {
        if (in_array($property, ['search', 'status'], true)) {
            $this->perPage = 12;
            $this->hasMore = true;
        }
    }

    public function loadMore(): void
    {
        $now = (int) (microtime(true) * 1000);

        if ($now - $this->lastLoadAt < 400) {
            return;
        }

        $this->lastLoadAt = $now;
        $this->perPage += 12;
    }

    public function toggleStatus(int $shopId): void
    {
        $shop = Shop::query()->findOrFail($shopId);

        $shop->forceFill(['status' => $shop->status === 'active' ? 'paused' : 'active'])->save();

        // seller notification: «وضعیت فروشگاه»
        \App\Support\SellerNotifier::notify(
            $shop->seller_id,
            'shop.status',
            $shop->status === 'active' ? 'فروشگاه شما فعال شد ✅' : 'فروشگاه شما موقتاً متوقف شد ⛔',
            $shop->status === 'active'
                ? 'فروشگاه «'.$shop->name.'» برای مشتریان قابل مشاهده است.'
                : 'فروشگاه «'.$shop->name.'» از دید مشتریان خارج شد — با پشتیبانی تماس بگیرید.',
            route('seller.shop.edit'),
            'store',
            $shop,
        );

        session()->flash('admin-catalog-flash',
            ($shop->status === 'active' ? 'فروشگاه «' : 'فروشگاه «').$shop->name.'» '.($shop->status === 'active' ? 'فعال شد.' : 'موقتاً متوقف شد و از دید مشتریان خارج است.'));
    }

    // ── Wallet withdrawal requests (seller wallet approvals) ────────

    public function approveWithdrawal(int $txId): void
    {
        $tx = WalletTransaction::query()->find($txId);

        if (! $tx) {
            return;
        }

        WalletService::approveWithdrawal($tx);

        session()->flash('admin-catalog-flash', 'درخواست برداشت تأیید شد؛ مبلغ به شبا فروشنده واریز شده در نظر گرفته می‌شود.');
    }

    public function rejectWithdrawal(int $txId): void
    {
        $tx = WalletTransaction::query()->find($txId);

        if (! $tx) {
            return;
        }

        WalletService::rejectWithdrawal($tx);

        session()->flash('admin-catalog-flash', 'درخواست برداشت رد شد؛ مبلغ به کیف پول فروشنده بازگشت.');
    }

    // ── Full shop CRUD for the admin ─────────────────────────────────

    /* ── edit modal ── */
    public bool $editOpen = false;

    public ?int $editingId = null;

    public string $shopName = '';

    public string $shopInstagram = '';

    public string $shopPhone = '';

    public string $shopAddress = '';

    public string $shopAbout = '';

    public string $shopStatus = 'active';

    /* ── create modal ── */
    public bool $createOpen = false;

    public int $createSellerId = 0;

    public string $createName = '';

    /* ── delete confirm ── */
    public ?int $deleteId = null;

    public function openShopEdit(int $id): void
    {
        $shop = Shop::query()->find($id);

        if (! $shop) {
            return;
        }

        $this->editingId     = $shop->id;
        $this->shopName      = (string) $shop->name;
        $this->shopInstagram = (string) ($shop->instagram_username ?? '');
        $this->shopPhone     = (string) ($shop->phone ?? '');
        $this->shopAddress   = (string) ($shop->address ?? '');
        $this->shopAbout     = (string) ($shop->about ?? '');
        $this->shopStatus    = $shop->status === 'paused' ? 'paused' : 'active';
        $this->editOpen      = true;
    }

    public function closeShopEdit(): void
    {
        $this->editOpen  = false;
        $this->editingId = null;
    }

    public function saveShop(): void
    {
        if ($this->editingId === null) {
            return;
        }

        $this->validateWithToast(fn () => $this->validate([
            'shopName'      => ['required', 'string', 'min:2', 'max:120'],
            'shopInstagram' => ['nullable', 'string', 'max:60'],
            'shopPhone'     => ['nullable', 'string', function (string $attribute, mixed $value, \Closure $fail) {
                if ($value === '' || $value === null) {
                    return;
                }

                $normalized = Phone::normalize((string) $value);

                if (! Phone::isValid($normalized)) {
                    $fail('شماره تماس را صحیح وارد کنید (مثال: ۰۹۱۲۳۴۵۶۷۸۹).');
                }
            }],
            'shopAddress'   => ['nullable', 'string', 'max:300'],
            'shopAbout'     => ['nullable', 'string', 'max:2000'],
            'shopStatus'    => ['required', 'in:active,paused'],
        ], [
            'shopName.required' => 'نام فروشگاه را وارد کنید.',
            'shopName.min'      => 'نام فروشگاه حداقل ۲ کاراکتر است.',
        ]), 'ذخیره فروشگاه ناموفق بود');

        $shop = Shop::query()->find($this->editingId);

        if (! $shop) {
            $this->closeShopEdit();

            return;
        }

        $before = $shop->only(['name', 'instagram_username', 'phone', 'address', 'about', 'status']);

        $shop->update([
            'name'               => trim($this->shopName),
            'instagram_username' => trim($this->shopInstagram) !== '' ? ltrim(trim($this->shopInstagram), '@') : null,
            'phone'              => $this->shopPhone !== '' ? Phone::normalize($this->shopPhone) : null,
            'address'            => trim($this->shopAddress) ?: null,
            'about'              => trim($this->shopAbout) ?: null,
            'status'             => $this->shopStatus,
        ]);

        ActivityLogger::log(
            'shop.updated',
            'فروشگاه «'.$shop->name.'» توسط مدیر ویرایش شد',
            subject: $shop,
            before: $before,
            after: $shop->only(['name', 'instagram_username', 'phone', 'address', 'about', 'status']),
        );

        $this->closeShopEdit();

        session()->flash('admin-catalog-flash', 'فروشگاه «'.$shop->name.'» ذخیره شد. ✅');
    }

    public function openShopCreate(): void
    {
        $this->resetErrorBag();
        $this->createSellerId = 0;
        $this->createName     = '';
        $this->createOpen     = true;
    }

    public function closeShopCreate(): void
    {
        $this->createOpen = false;
    }

    /** Sellers that do NOT have a shop yet (shops.seller_id is unique). */
    public function getSellersWithoutShopProperty(): Collection
    {
        return Seller::query()
            ->whereNotIn('id', Shop::query()->select('seller_id'))
            ->orderBy('id')
            ->get(['id', 'full_name', 'instagram_username', 'phone']);
    }

    public function createShop(): void
    {
        $this->validateWithToast(fn () => $this->validate([
            'createSellerId' => ['required', 'integer', 'exists:sellers,id'],
            'createName'     => ['required', 'string', 'min:2', 'max:120'],
        ], [
            'createSellerId.required' => 'فروشنده را انتخاب کنید.',
            'createSellerId.exists'   => 'فروشندهٔ انتخاب‌شده یافت نشد.',
            'createName.required'     => 'نام فروشگاه را وارد کنید.',
        ]), 'ایجاد فروشگاه ناموفق بود');

        $seller = Seller::query()->find($this->createSellerId);

        if (! $seller) {
            return;
        }

        $shop = Shop::query()->create([
            'seller_id' => $seller->id,
            'name'      => trim($this->createName),
            'instagram_username' => $seller->instagram_username,
            'status'    => 'active',
        ]);

        ActivityLogger::log('shop.created', 'فروشگاه «'.$shop->name.'» توسط مدیر ایجاد شد', subject: $shop, after: ['seller_id' => $seller->id]);

        \App\Support\SellerNotifier::notify(
            $seller->id,
            'shop.status',
            'فروشگاه شما ساخته شد ✅',
            'فروشگاه «'.$shop->name.'» توسط پشتیبانی برای شما ایجاد شد.',
            route('seller.shop.edit'),
            'store',
            $shop,
        );

        $this->closeShopCreate();

        session()->flash('admin-catalog-flash', 'فروشگاه «'.$shop->name.'» ایجاد شد. ✅');
    }

    public function askShopDelete(int $id): void
    {
        $this->deleteId = $id;
    }

    public function cancelShopDelete(): void
    {
        $this->deleteId = null;
    }

    public function deleteShop(): void
    {
        if ($this->deleteId === null) {
            return;
        }

        $shop = Shop::query()->find($this->deleteId);
        $this->cancelShopDelete();

        if (! $shop) {
            return;
        }

        $name = $shop->name;
        $id   = $shop->id;

        // children cascade in the DB (products, categories, attributes,
        // carriers, filters…); order history keeps its shop_id via nullOnDelete
        $shop->delete();

        ActivityLogger::log('shop.deleted', 'فروشگاه «'.$name.'» توسط مدیر حذف شد', after: ['shop_id' => $id, 'name' => $name]);

        session()->flash('admin-catalog-flash', 'فروشگاه «'.$name.'» حذف شد.');
    }

    // ── Computed data ────────────────────────────────────────────────

    public function getStatsProperty(): array
    {
        $byStatus = Shop::query()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        return [
            'all' => (int) $byStatus->sum(),
            'active' => (int) ($byStatus['active'] ?? 0),
            'paused' => (int) ($byStatus['paused'] ?? 0),
        ];
    }

    public function getShopsProperty(): Collection
    {
        $term = Fmt::toLatin($this->search);

        $query = Shop::query()
            ->when($term !== '', fn ($q) => $q->where(fn ($w) => $w
                ->where('name', 'like', "%{$this->search}%")
                ->orWhere('slug', 'like', "%{$this->search}%")
                ->orWhere('instagram_username', 'like', '%'.ltrim($this->search, '@').'%')
                ->orWhereHas('seller', fn ($s) => $s->where('phone', 'like', "%{$term}%"))))
            ->when($this->status !== 'all', fn ($q) => $q->where('status', $this->status))
            ->with(['seller:id,full_name,instagram_username,status,phone'])
            ->withCount(['products', 'publishedProducts'])
            ->latest('id');

        $total = (clone $query)->count();
        $rows = $query->limit($this->perPage)->get();

        $this->hasMore = $total > $this->perPage;

        return $rows;
    }

    public function render()
    {
        return view('livewire.admin.shops-index', [
            'shops' => $this->shops,
            'stats' => $this->stats,
            'hasMore' => $this->hasMore,
            'withdrawals' => $this->pendingWithdrawals,
        ])->title('مدیریت فروشگاه‌ها')
            ->layout('layouts.admin-slot', ['breadcrumb' => 'مدیریت کل — فروشگاه‌ها']);
    }

    /** Pending withdrawal requests with their shop/wallet context. */
    public function getPendingWithdrawalsProperty(): Collection
    {
        return WalletTransaction::query()
            ->where('type', WalletTransaction::TYPE_WITHDRAWAL)
            ->where('status', WalletTransaction::STATUS_PENDING)
            ->with(['wallet.seller:id,full_name,instagram_username'])
            ->latest('id')
            ->limit(10)
            ->get();
    }
}
