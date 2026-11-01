<?php

namespace App\Livewire\Seller;

use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Shop;
use App\Models\ShopCategory;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.seller-slot')]
class Home extends Component
{
    /* ── chart controls ── */
    public string $chartMode = 'daily'; // daily | monthly | yearly

    public string $chartFrom = '';

    public string $chartTo = '';

    public function mount(): void
    {
        // default window: last 7 days (paid sales chart)
        $this->chartTo   = CarbonImmutable::today()->toDateString();
        $this->chartFrom = CarbonImmutable::today()->subDays(6)->toDateString();
    }

    public function setChartMode(string $mode): void
    {
        $this->chartMode = in_array($mode, ['daily', 'monthly', 'yearly'], true) ? $mode : 'daily';

        // a sensible window per mode
        $today = CarbonImmutable::today();

        $this->chartTo = $today->toDateString();

        $this->chartFrom = match ($this->chartMode) {
            'daily'   => $today->subDays(6)->toDateString(),
            'monthly' => $today->subMonths(5)->startOfMonth()->toDateString(),
            default   => $today->subYears(3)->startOfYear()->toDateString(),
        };
    }

    public function render()
    {
        $seller = auth('seller')->user();
        $shop   = Shop::ensureForSeller($seller);

        $base = Product::query()->where('shop_id', $shop->id);

        $stats = [
            'total'      => (clone $base)->count(),
            'published'  => (clone $base)->where('status', 'published')->count(),
            'outOfStock' => (clone $base)->where('stock', 0)->count(),
            'categories' => ShopCategory::query()->where('shop_id', $shop->id)->count(),
        ];

        $chart = $this->salesChart($shop);

        return view('livewire.seller.home', [
            'shop'           => $shop,
            'stats'          => $stats,
            'chart'          => $chart['series'],
            'chartTotal'     => $chart['total'],
            'chartOrders'    => $chart['orders'],
            'chartMax'       => $chart['max'],
            'recentProducts' => (clone $base)->with('mainImage')->latest()->take(4)->get(),
        ])->title('خانه فروشنده');
    }

    /**
     * Paid-sales series for the selected window & granularity.
     * Returns [series (label/value/count/today), total, orders, max].
     */
    private function salesChart(Shop $shop): array
    {
        try {
            $from = CarbonImmutable::parse($this->chartFrom)->startOfDay();
            $to   = CarbonImmutable::parse($this->chartTo)->endOfDay();
        } catch (\Throwable) {
            $from = CarbonImmutable::today()->subDays(6)->startOfDay();
            $to   = CarbonImmutable::today()->endOfDay();
        }

        if ($from->gt($to)) {
            [$from, $to] = [$to->startOfDay(), $from->endOfDay()];
        }

        // hard caps keep the query + loop light on shared hosting
        $from = match ($this->chartMode) {
            'daily'   => $from->max(CarbonImmutable::today()->subDays(91)->startOfDay()),
            'monthly' => $from->max(CarbonImmutable::today()->subYears(3)->startOfDay()),
            default   => $from->max(CarbonImmutable::today()->subYears(9)->startOfDay()),
        };

        /* paid order_items of this shop inside the window */
        $rows = OrderItem::query()
            ->selectRaw('order_items.unit_price, order_items.quantity, orders.paid_at, orders.id as order_id')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('order_items.shop_id', $shop->id)
            ->where('orders.status', 'paid')
            ->whereBetween('orders.paid_at', [$from, $to])
            ->get();

        $ordersCount = $rows->pluck('order_id')->unique()->count();
        $total       = (int) $rows->sum(fn ($r) => (int) $r->unit_price * (int) $r->quantity);

        /* bucketize */
        $buckets = [];

        if ($this->chartMode === 'daily') {
            $cursor = $from->startOfDay();
            $steps  = $from->diffInDays($to->endOfDay()) + 1;
            for ($i = 0; $i < min((int) $steps, 92); $i++) {
                $key             = $cursor->toDateString();
                $buckets[$key]   = [
                    'label' => $cursor->locale('fa')->dayName.' '.\App\Support\Fmt::fa($cursor->day),
                    'sort'  => $key,
                    'today' => $key === CarbonImmutable::today()->toDateString(),
                ];
                $cursor = $cursor->addDay();
            }
        } elseif ($this->chartMode === 'monthly') {
            $cursor = $from->startOfDay();
            $steps  = $from->diffInMonths($to->endOfDay()) + 1;
            for ($i = 0; $i < min((int) $steps, 36); $i++) {
                $key           = $cursor->format('Y-m');
                $buckets[$key] = [
                    'label' => $cursor->locale('fa')->monthName,
                    'sort'  => $key,
                    'today' => $key === CarbonImmutable::today()->format('Y-m'),
                ];
                $cursor = $cursor->addMonth()->startOfMonth();
            }
        } else {
            $cursor = $from->startOfDay();
            $steps  = $from->diffInYears($to->endOfDay()) + 1;
            for ($i = 0; $i < min((int) $steps, 10); $i++) {
                $key           = $cursor->format('Y');
                $buckets[$key] = [
                    'label' => \App\Support\Fmt::fa($cursor->year),
                    'sort'  => $key,
                    'today' => $key === CarbonImmutable::today()->format('Y'),
                ];
                $cursor = $cursor->addYear()->startOfYear();
            }
        }

        foreach ($rows as $row) {
            if (! $row->paid_at) {
                continue;
            }

            $paid = CarbonImmutable::parse($row->paid_at);

            $key = match ($this->chartMode) {
                'daily'   => $paid->toDateString(),
                'monthly' => $paid->format('Y-m'),
                default   => $paid->format('Y'),
            };

            if (isset($buckets[$key])) {
                $buckets[$key]['value'] = ($buckets[$key]['value'] ?? 0) + (int) $row->unit_price * (int) $row->quantity;
            }
        }

        $series = collect($buckets)
            ->sortBy('sort')
            ->map(fn ($b) => [
                'label' => $b['label'],
                'value' => (int) ($b['value'] ?? 0),
                'today' => $b['today'],
            ])
            ->values();

        return [
            'series' => $series,
            'total'  => $total,
            'orders' => $ordersCount,
            'max'    => max($series->max('value'), 1),
        ];
    }
}
