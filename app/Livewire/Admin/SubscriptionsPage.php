<?php

namespace App\Livewire\Admin;

use App\Livewire\Admin\Concerns\GuardsPermissions;
use App\Livewire\Concerns\NotifiesOnValidation;
use App\Models\Plan;
use App\Models\Seller;
use App\Models\SellerSubscription;
use App\Support\ActivityLogger;
use App\Support\Fmt;
use App\Support\Subscription\SubscriptionService;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Admin — «اشتراک فروشندگان» (v0.22.0): the subscription ledger with
 * stats (active / expired / revenue), seller search, the manual GRANT
 * modal (free gift or the received amount for the books) and revoke.
 * Stacking/replacement rules live in SubscriptionService::grant().
 */
#[Layout('layouts.admin-slot')]
class SubscriptionsPage extends Component
{
    use GuardsPermissions;
    use NotifiesOnValidation;

    protected static string $permission = 'admin.subscriptions';

    #[Url]
    public string $status = 'all'; // all | active | expired

    #[Url]
    public string $search = '';

    public int $perPage = 15;

    public bool $hasMore = true;

    private int $lastLoadAt = 0;

    /* ── grant modal ── */
    public bool $grantOpen = false;

    public ?int $grantSellerId = null;

    public string $grantSellerQuery = '';

    public string $grantPlanId = '';

    public string $grantPrice = '';

    public string $grantNote = '';

    public function updating(string $property): void
    {
        if (in_array($property, ['status', 'search'], true)) {
            $this->perPage = 15;
            $this->hasMore = true;
        }
    }

    public function updatedSearch(): void
    {
        if ($this->grantOpen) {
            return; // the grant picker has its own query
        }
    }

    public function loadMore(): void
    {
        $now = (int) (microtime(true) * 1000);

        if ($now - $this->lastLoadAt < 400) {
            return;
        }

        $this->lastLoadAt = $now;
        $this->perPage += 15;
    }

    /* ───────────────── grant modal ───────────────── */

    public function openGrant(): void
    {
        if (Plan::query()->where('is_active', true)->count() === 0) {
            $this->dispatch('panel-toast', message: 'اول از «پلن‌های اشتراک» یک پلن فعال بسازید. ⛔', type: 'error');

            return;
        }

        $this->resetErrorBag();
        $this->grantOpen = true;
        $this->grantSellerId = null;
        $this->grantSellerQuery = '';
        $this->grantPlanId = '';
        $this->grantPrice = '';
        $this->grantNote = '';
    }

    public function closeGrant(): void
    {
        $this->grantOpen = false;
        $this->grantSellerId = null;
        $this->grantSellerQuery = '';
        $this->grantPlanId = '';
        $this->grantPrice = '';
        $this->grantNote = '';
        $this->resetErrorBag();
    }

    /** Pick a seller inside the grant modal. */
    public function pickSeller(int $sellerId): void
    {
        $this->grantSellerId = $sellerId;
        $this->grantSellerQuery = '';
    }

    /** Prefill the price input with the plan's own price. */
    public function updatedGrantPlanId(): void
    {
        $plan = Plan::query()->find((int) $this->grantPlanId);
        $this->grantPrice = $plan ? (string) $plan->price : '';
    }

    public function submitGrant(): void
    {
        $this->validateWithToast(fn () => $this->validate([
            'grantSellerId' => ['required', 'integer', 'exists:sellers,id'],
            'grantPlanId' => ['required', 'integer'],
            'grantPrice' => ['nullable', 'integer', 'min:0', 'max:999999999999'],
            'grantNote' => ['nullable', 'string', 'max:200'],
        ], [
            'grantSellerId.required' => 'فروشنده را انتخاب کنید.',
            'grantPlanId.required' => 'پلن را انتخاب کنید.',
            'grantPrice.integer' => 'مبلغ باید عدد باشد.',
            'grantNote.max' => 'توضیح حداکثر ۲۰۰ نویسه است.',
        ]));

        $plan = Plan::query()->findOrFail((int) $this->grantPlanId);
        $seller = Seller::query()->findOrFail($this->grantSellerId);
        $price = (int) $this->grantPrice;

        $row = SubscriptionService::grantAdmin($seller, $plan, $price, $this->grantNote !== '' ? $this->grantNote : null);

        ActivityLogger::log(
            'subscription.granted',
            'اشتراک «'.$plan->title.'» برای فروشنده @'.($seller->instagram_username ?: $seller->phone).' اعطا شد ('.($price > 0 ? 'مبلغ '.Fmt::money($price) : 'هدیه').') — اعتبار تا '.$row->ends_at->format('Y-m-d'),
            subject: $row,
            after: ['seller_id' => $seller->id, 'plan_id' => $plan->id, 'price_paid' => $price],
        );

        $this->dispatch('panel-toast', message: 'اشتراک «'.$plan->title.'» برای فروشنده فعال شد ✅', type: 'success');
        $this->closeGrant();
    }

    /* ───────────────── revoke ───────────────── */

    public function revoke(int $rowId): void
    {
        $row = SellerSubscription::query()->with('seller')->findOrFail($rowId);

        if (! SubscriptionService::revoke($row)) {
            $this->dispatch('panel-toast', message: 'این اشتراک قبلاً منقضی شده است. ⛔', type: 'error');

            return;
        }

        ActivityLogger::log(
            'subscription.revoked',
            'اشتراک «'.$row->title().'» فروشنده @'.($row->seller?->instagram_username ?: '—').' توسط مدیر لغو شد',
            subject: $row,
        );

        $this->dispatch('panel-toast', message: 'اشتراک لغو شد و تاریخچه حفظ شد. ✅', type: 'success');
    }

    /* ───────────────── data ───────────────── */

    /** Active subscription of each seller id (for the NO-plan filter info). */
    public function getStatsProperty(): array
    {
        $activeRows = SellerSubscription::query()->where('ends_at', '>', now());

        $activeBySeller = (clone $activeRows)->selectRaw('seller_id, count(*) as total')->groupBy('seller_id')->get();
        $activeSellers = SellerSubscription::query()->where('ends_at', '>', now())->distinct('seller_id')->count('seller_id');
        $activeCount = (clone $activeRows)->count();
        $revenue = (int) SellerSubscription::query()->where('price_paid', '>', 0)->sum('price_paid');
        $sellersTotal = Seller::query()->count();

        return [
            'active' => $activeCount,
            'active_sellers' => $activeSellers,
            'without' => max(0, $sellersTotal - $activeSellers),
            'revenue' => $revenue,
            'renewals' => SellerSubscription::query()->count(),
        ];
    }

    /** The ledger rows — latest first, optional status filter + search. */
    public function getRowsProperty(): Collection
    {
        $query = SellerSubscription::query()
            ->with(['seller:id,phone,instagram_username,full_name', 'plan:id,title'])
            ->orderByDesc('id');

        if ($this->status === 'active') {
            $query->where('ends_at', '>', now());
        } elseif ($this->status === 'expired') {
            $query->where('ends_at', '<=', now());
        }

        $q = trim($this->search);

        if ($q !== '') {
            $query->whereHas('seller', function ($seller) use ($q) {
                $seller->where('phone', 'like', '%'.$q.'%')
                    ->orWhere('instagram_username', 'like', '%'.$q.'%')
                    ->orWhere('full_name', 'like', '%'.$q.'%');
            });
        }

        $rows = $query->limit(min(max($this->perPage, 1), 100))->get();
        $this->hasMore = $rows->count() >= min(max($this->perPage, 1), 100);

        return $rows;
    }

    /** Sellers for the grant picker (search by name/handle/phone). */
    public function getSellerOptionsProperty(): Collection
    {
        $q = trim($this->grantSellerQuery);

        return Seller::query()
            ->when($q !== '', fn ($seller) => $seller
                ->where('phone', 'like', '%'.$q.'%')
                ->orWhere('instagram_username', 'like', '%'.$q.'%')
                ->orWhere('full_name', 'like', '%'.$q.'%'))
            ->orderByDesc('id')
            ->limit(8)
            ->get(['id', 'phone', 'instagram_username', 'full_name', 'status']);
    }

    public function getPlansProperty(): Collection
    {
        return Plan::query()->where('is_active', true)->orderBy('sort')->orderBy('id')->get(['id', 'title', 'duration_days', 'price', 'commission_type', 'commission_value']);
    }

    public function render()
    {
        return view('livewire.admin.subscriptions-page', [
            'rows' => $this->rows,
            'stats' => $this->stats,
            'sellerOptions' => $this->sellerOptions,
            'plans' => $this->plans,
            'hasMore' => $this->hasMore,
        ])->title('اشتراک فروشندگان')
            ->layout('layouts.admin-slot', ['breadcrumb' => 'مدیریت کل — اشتراک فروشندگان']);
    }
}
