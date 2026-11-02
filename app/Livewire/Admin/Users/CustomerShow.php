<?php

namespace App\Livewire\Admin\Users;

use App\Livewire\Admin\Concerns\GuardsPermissions;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Support\Fmt;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Admin — customer SHOW page (batch 77): identity card + stat cards +
 * orders chart, with separate URL-persisted tabs:
 *   overview | orders (search + status) | favorites
 */
#[Layout('layouts.admin-slot')]
class CustomerShow extends Component
{
    use GuardsPermissions;

    protected static string $permission = 'admin.users.customers';

    #[Url]
    public string $tab = 'overview';

    #[Url]
    public string $orderStatus = 'all';

    public int $perPage = 12;

    public bool $hasMore = true;

    private int $lastLoadAt = 0;

    public int $customerId = 0;

    public function mount(Customer $customer): void
    {
        $this->customerId = $customer->id;

        if (! in_array($this->tab, ['overview', 'orders', 'favorites'], true)) {
            $this->tab = 'overview';
        }

        if (! in_array($this->orderStatus, array_merge(['all'], Order::STATUSES), true)) {
            $this->orderStatus = 'all';
        }
    }

    public function updating(string $property): void
    {
        if ($property === 'orderStatus') {
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
        $this->perPage   += 12;
    }

    public function getCustomerProperty(): ?Customer
    {
        return Customer::query()->find($this->customerId);
    }

    /** 14-day chart of this customer's PAID orders. */
    public function getChartProperty(): Collection
    {
        $from = Carbon::today()->subDays(13);

        $rows = Order::query()
            ->where('customer_id', $this->customerId)
            ->where('status', 'paid')
            ->whereBetween('paid_at', [$from->copy()->startOfDay(), now()])
            ->get(['paid_at', 'total_amount'])
            ->groupBy(fn ($order) => $order->paid_at?->format('Y-m-d'));

        return collect(range(0, 13))->map(function (int $i) use ($from, $rows) {
            $day = $from->copy()->addDays($i);
            $key = $day->format('Y-m-d');

            return [
                'label'  => Fmt::toFa($day->format('m/d')),
                'orders' => (int) ($rows[$key] ?? collect())->count(),
                'total'  => (int) ($rows[$key] ?? collect())->sum('total_amount'),
            ];
        });
    }

    public function getStatsProperty(): array
    {
        $paid = Order::query()
            ->where('customer_id', $this->customerId)
            ->where('status', 'paid');

        return [
            'orders'      => Order::query()->where('customer_id', $this->customerId)->count(),
            'paid_orders' => (clone $paid)->count(),
            'spent'       => (int) (clone $paid)->sum('total_amount'),
            'favorites'   => $this->customer?->favorites()->count() ?? 0,
            'addresses'   => \App\Models\CustomerAddress::query()->where('customer_id', $this->customerId)->count(),
        ];
    }

    public function getOrdersProperty(): Collection
    {
        return Order::query()
            ->where('customer_id', $this->customerId)
            ->when($this->orderStatus !== 'all', fn ($q) => $q->where('status', $this->orderStatus))
            ->latest('id')
            ->limit($this->perPage)
            ->get();
    }

    public function getFavoritesProperty(): Collection
    {
        $ids = $this->customer?->favoriteProductIds() ?? [];

        if ($ids === []) {
            return collect();
        }

        return Product::query()
            ->whereIn('id', $ids)
            ->with(['mainImage', 'shop:id,name'])
            ->latest('id')
            ->limit(24)
            ->get();
    }

    public function render()
    {
        $customer = $this->customer;

        abort_if(! $customer, 404);

        return view('livewire.admin.users.customer-show', [
            'customer'  => $customer,
            'stats'     => $this->stats,
            'chart'     => $this->chart,
            'orders'    => $this->orders,
            'favorites' => $this->favorites,
            'hasMore'   => $this->hasMore,
        ])->title('مشتری: '.($customer->full_name ?: Fmt::toFa($customer->phone)))
            ->layout('layouts.admin-slot', ['breadcrumb' => 'مدیریت کل — مشتریان']);
    }
}
