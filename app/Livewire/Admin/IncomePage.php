<?php

namespace App\Livewire\Admin;

use App\Livewire\Admin\Concerns\GuardsPermissions;
use App\Models\Order;
use App\Models\SellerSubscription;
use App\Models\Shop;
use App\Support\Fmt;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Admin — platform income (batch 81): the commissions the platform
 * received from PAID orders, computed per-shop by WalletService.
 * Chart + shop/date filters + the receipts table (each row shows the
 * percent/fixed basis and the Toman amount of the commission).
 */
#[Layout('layouts.admin-slot')]
class IncomePage extends Component
{
    use GuardsPermissions;

    protected static string $permission = 'admin.income';

    #[Url]
    public int $shopId = 0;

    #[Url]
    public string $range = '30'; // 7 | 30 | 90 | all

    public int $perPage = 15;

    public bool $hasMore = true;

    private int $lastLoadAt = 0;

    public function mount(): void
    {
        if (! in_array($this->range, ['7', '30', '90', 'all'], true)) {
            $this->range = '30';
        }
    }

    public function updating(string $property): void
    {
        if (in_array($property, ['shopId', 'range'], true)) {
            $this->perPage = 15;
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
        $this->perPage += 15;
    }

    /** Paid orders whose commissions snapshot exists, filtered. */
    private function ordersQuery()
    {
        $from = match ($this->range) {
            '7' => Carbon::today()->subDays(6)->startOfDay(),
            '30' => Carbon::today()->subDays(29)->startOfDay(),
            '90' => Carbon::today()->subDays(89)->startOfDay(),
            default => null,
        };

        return Order::query()
            ->where('status', 'paid')
            ->whereNotNull('commissions')
            ->when($from !== null, fn ($q) => $q->whereBetween('paid_at', [$from, now()]))
            ->latest('paid_at');
    }

    /** Flattened commission rows: one entry per shop per order. */
    public function getRowsProperty(): Collection
    {
        $orders = $this->ordersQuery()->limit(200)->get();

        $rows = $orders->flatMap(function ($order) {
            return collect($order->commissions ?? [])
                ->filter(fn (array $row) => $this->shopId === 0 || (int) $row['shop_id'] === $this->shopId)
                ->map(fn (array $row) => (object) [
                    'order_id' => (int) $order->id,
                    'order_code' => str_pad((string) $order->id, 5, '0', STR_PAD_LEFT),
                    'paid_at' => $order->paid_at,
                    'shop_id' => (int) ($row['shop_id'] ?? 0),
                    'shop_name' => (string) ($row['shop_name'] ?? '—'),
                    'items_total' => (int) ($row['items_total'] ?? 0),
                    'type' => (string) ($row['commission_type'] ?? 'percent'),
                    'value' => (int) ($row['commission_value'] ?? 0),
                    'commission' => (int) ($row['commission_amount'] ?? 0),
                    'payout' => (int) ($row['payout'] ?? 0),
                ]);
        })->values();

        $this->hasMore = $rows->count() > $this->perPage;

        return $rows->take($this->perPage);
    }

    public function getTotalsProperty(): array
    {
        $orders = $this->ordersQuery()->get();

        $rows = $orders->flatMap(fn ($order) => collect($order->commissions ?? []))
            ->filter(fn (array $row) => $this->shopId === 0 || (int) $row['shop_id'] === $this->shopId);

        // v0.22.0 — subscription sales in the SAME date window (wallet
        // purchases + admin-granted paid subscriptions; grants marked free
        // contribute 0 by definition).
        $subs = SellerSubscription::query()
            ->where('price_paid', '>', 0)
            ->when($this->range !== 'all', fn ($q) => $q->whereBetween('created_at', $this->rangeWindow()))
            ->when($this->shopId > 0, fn ($q) => $q->whereHas('seller.shop', fn ($shop) => $shop->where('id', $this->shopId)));

        return [
            'commission' => (int) $rows->sum('commission_amount'),
            'goods' => (int) $rows->sum('items_total'),
            'payout' => (int) $rows->sum('payout'),
            'orders' => $orders->count(),
            'subscription' => (int) $subs->sum('price_paid'),
            // v0.23.0 — «جمع همهٔ درآمد» = commissions received + subscription sales
            'total' => (int) $rows->sum('commission_amount') + (int) $subs->sum('price_paid'),
        ];
    }

    /** [startOfDay, endOfDay] of the active range (mirror of ordersQuery). */
    private function rangeWindow(): array
    {
        return match ($this->range) {
            '7' => [Carbon::today()->subDays(6)->startOfDay(), now()],
            '90' => [Carbon::today()->subDays(89)->startOfDay(), now()],
            default => [Carbon::today()->subDays(29)->startOfDay(), now()],
        };
    }

    /** 14-day STACKED revenue chart (v0.23.0): commissions + subscription sales per day. */
    public function getChartProperty(): Collection
    {
        $from = Carbon::today()->subDays(13);

        $window = [$from->copy()->startOfDay(), now()];

        $orders = Order::query()
            ->where('status', 'paid')
            ->whereNotNull('commissions')
            ->whereBetween('paid_at', $window)
            ->get(['paid_at', 'commissions']);

        $commissionByDay = $orders->groupBy(fn ($order) => $order->paid_at?->format('Y-m-d'))
            ->map(fn ($dayOrders) => (int) $dayOrders
                ->flatMap(fn ($order) => collect($order->commissions ?? []))
                ->filter(fn (array $row) => $this->shopId === 0 || (int) $row['shop_id'] === $this->shopId)
                ->sum('commission_amount'));

        $subscriptionByDay = SellerSubscription::query()
            ->where('price_paid', '>', 0)
            ->whereBetween('created_at', $window)
            ->when($this->shopId > 0, fn ($q) => $q->whereHas('seller.shop', fn ($shop) => $shop->where('id', $this->shopId)))
            ->get(['created_at', 'price_paid'])
            ->groupBy(fn ($row) => $row->created_at?->format('Y-m-d'))
            ->map(fn ($rows) => (int) $rows->sum('price_paid'));

        return collect(range(0, 13))->map(function (int $i) use ($from, $commissionByDay, $subscriptionByDay) {
            $day = $from->copy()->addDays($i);
            $key = $day->format('Y-m-d');
            $commission = (int) ($commissionByDay[$key] ?? 0);
            $subscription = (int) ($subscriptionByDay[$key] ?? 0);

            return [
                'label' => $day->locale('fa')->minDayName.' '.Fmt::toFa($day->day),
                'commission' => $commission,
                'subscription' => $subscription,
                'total' => $commission + $subscription,
            ];
        });
    }

    public function getShopOptionsProperty(): Collection
    {
        return Shop::query()->orderBy('name')->get(['id', 'name']);
    }

    public function render()
    {
        return view('livewire.admin.income-page', [
            'rows' => $this->rows,
            'totals' => $this->totals,
            'chart' => $this->chart,
            'shopOptions' => $this->shopOptions,
            'hasMore' => $this->hasMore,
        ])->title('درآمد پلتفرم')
            ->layout('layouts.admin-slot', ['breadcrumb' => 'مدیریت کل — درآمد']);
    }
}
