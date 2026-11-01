<?php

namespace App\Livewire\Seller;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductView;
use App\Support\Fmt;
use App\Support\JDate;
use Carbon\CarbonImmutable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Seller — گزارش‌ها (reports):
 * reached from the profile. Orders (paid/pending/failed/canceled +
 * revenue + unique buyers) and product views (today / 7d / 30d +
 * a 14-day visits bar chart + top viewed products).
 */
#[Layout('layouts.seller-slot')]
class ReportsPage extends Component
{
    public function render()
    {
        $seller = auth('seller')->user();
        $shop   = $seller?->shop;
        $shopId = (int) ($shop?->id ?? 0);

        $orderStats = $this->orderStats($shopId);
        $viewStats  = $this->viewStats($shopId);
        $visitChart = $this->visitChart($shopId);

        return view('livewire.seller.reports-page', [
            'shop'        => $shop,
            'orderStats'  => $orderStats,
            'viewStats'   => $viewStats,
            'visitChart'  => $visitChart,
            'chartMax'    => max($visitChart->max('value'), 1),
            'topProducts' => $this->topProducts($shopId),
        ])->title('گزارش‌ها');
    }

    /* ───────────────────────── data ───────────────────────── */

    /** Order funnel of this shop (via order_items) + revenue + buyers. */
    private function orderStats(int $shopId): array
    {
        if ($shopId === 0) {
            return [
                'statuses'   => ['paid' => 0, 'pending' => 0, 'failed' => 0, 'canceled' => 0],
                'revenue'    => 0,
                'buyers'     => 0,
                'lastPaidAt' => null,
            ];
        }

        $byStatus = ['paid' => 0, 'pending' => 0, 'failed' => 0, 'canceled' => 0];

        $counts = OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('order_items.shop_id', $shopId)
            ->selectRaw('orders.status as s, COUNT(DISTINCT orders.id) as c')
            ->groupBy('orders.status')
            ->pluck('c', 's');

        foreach ($byStatus as $status => $_) {
            $byStatus[$status] = (int) ($counts[$status] ?? 0);
        }

        $revenue = (int) OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('order_items.shop_id', $shopId)
            ->where('orders.status', 'paid')
            ->selectRaw('COALESCE(SUM(order_items.unit_price * order_items.quantity), 0) as total')
            ->value('total');

        $buyers = Order::query()
            ->where('status', 'paid')
            ->whereHas('items', fn ($q) => $q->where('shop_id', $shopId))
            ->distinct('customer_id')
            ->count('customer_id');

        $lastPaidAt = Order::query()
            ->where('status', 'paid')
            ->whereHas('items', fn ($q) => $q->where('shop_id', $shopId))
            ->max('paid_at');

        return [
            'statuses'   => $byStatus,
            'revenue'    => $revenue,
            'buyers'     => $buyers,
            'lastPaidAt' => $lastPaidAt ? JDate::dateTime(Carbon::parse($lastPaidAt)) : null,
        ];
    }

    /** Total + windowed view counters (cumulative products.views + daily log). */
    private function viewStats(int $shopId): array
    {
        if ($shopId === 0) {
            return ['total' => 0, 'today' => 0, 'd7' => 0, 'd30' => 0];
        }

        $today = CarbonImmutable::today();

        $sumSince = fn (CarbonImmutable $from) => (int) ProductView::query()
            ->where('shop_id', $shopId)
            ->where('day', '>=', $from->toDateString())
            ->sum('views');

        return [
            'total' => (int) Product::query()->where('shop_id', $shopId)->sum('views'),
            'today' => $sumSince($today),
            'd7'    => $sumSince($today->subDays(6)),
            'd30'   => $sumSince($today->subDays(29)),
        ];
    }

    /** Last 14 days of visits as chart bars (label/value/today). */
    private function visitChart(int $shopId): Collection
    {
        $from = CarbonImmutable::today()->subDays(13)->startOfDay();

        $rows = ProductView::query()
            ->where('shop_id', $shopId)
            ->where('day', '>=', $from->toDateString())
            ->selectRaw('day, SUM(views) as v')
            ->groupBy('day')
            ->pluck('v', 'day');

        $buckets = [];

        for ($i = 0; $i < 14; $i++) {
            $day           = $from->addDays($i);
            $key           = $day->toDateString();
            $buckets[$key] = [
                'label' => Fmt::fa($day->locale('fa')->minDayName),
                'value' => (int) ($rows[$key] ?? 0),
                'today' => $i === 13,
            ];
        }

        return collect($buckets)->values();
    }

    /** Top 6 products by cumulative views. */
    private function topProducts(int $shopId): Collection
    {
        return Product::query()
            ->where('shop_id', $shopId)
            ->with('mainImage')
            ->orderByDesc('views')
            ->orderByDesc('id')
            ->limit(6)
            ->get(['id', 'shop_id', 'name', 'price', 'views', 'status']);
    }
}
