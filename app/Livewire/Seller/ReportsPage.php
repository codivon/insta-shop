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
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Seller — گزارش‌ها (reports), fully period-aware:
 *
 *  • orders funnel (paid/pending/failed/canceled + revenue + buyers)
 *  • SALES chart   — paid order_items, bucketized daily/weekly/monthly/yearly
 *                    over a Jalali date range, downloadable as CSV
 *  • VIEWS chart   — product_views day rows, same granularities & range,
 *                    downloadable as CSV
 *  • top viewed products (→ full ranking page seller.reports.views)
 */
#[Layout('layouts.seller-slot')]
class ReportsPage extends Component
{
    public const MODES = ['daily', 'weekly', 'monthly', 'yearly'];

    /* ── sales chart controls ── */
    public string $salesMode = 'daily';

    public string $salesFrom = '';

    public string $salesTo = '';

    /* ── views chart controls ── */
    public string $viewsMode = 'daily';

    public string $viewsFrom = '';

    public string $viewsTo = '';

    public function mount(): void
    {
        $today = CarbonImmutable::today();

        // default windows: sales → last 7 days, views → last 14 days
        $this->salesTo   = $today->toDateString();
        $this->salesFrom = $today->subDays(6)->toDateString();
        $this->viewsTo   = $today->toDateString();
        $this->viewsFrom = $today->subDays(13)->toDateString();
    }

    public function setSalesMode(string $mode): void
    {
        $this->salesMode = $this->normalizeMode($mode);

        [$from, $to] = $this->defaultWindow($this->salesMode);
        $this->salesFrom = $from;
        $this->salesTo   = $to;
    }

    public function setViewsMode(string $mode): void
    {
        $this->viewsMode = $this->normalizeMode($mode);

        [$from, $to] = $this->defaultWindow($this->viewsMode);
        $this->viewsFrom = $from;
        $this->viewsTo   = $to;
    }

    /* ───────────────────────── CSV exports ───────────────────────── */

    /** CSV of paid sales line-items inside the selected sales window. */
    public function exportSalesCsv()
    {
        $seller = auth('seller')->user();
        $shopId = (int) ($seller?->shop?->id ?? 0);

        [$from, $to] = $this->resolveRange($this->salesFrom, $this->salesTo, $this->salesMode);

        $rows = collect();

        if ($shopId !== 0) {
            $rows = OrderItem::query()
                ->join('orders', 'orders.id', '=', 'order_items.order_id')
                ->where('order_items.shop_id', $shopId)
                ->where('orders.status', 'paid')
                ->whereBetween('orders.paid_at', [$from, $to])
                ->orderBy('orders.paid_at')
                ->get([
                    'orders.id as order_id',
                    'orders.paid_at',
                    'order_items.product_name',
                    'order_items.variant_label',
                    'order_items.quantity',
                    'order_items.unit_price',
                ]);
        }

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');

            // UTF-8 BOM so Excel shows Persian correctly
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['order_id', 'paid_at', 'product', 'variant', 'quantity', 'unit_price', 'line_total']);

            foreach ($rows as $row) {
                fputcsv($out, [
                    $row->order_id,
                    $row->paid_at ? Carbon::parse($row->paid_at)->format('Y-m-d H:i') : '',
                    $row->product_name,
                    $row->variant_label,
                    (int) $row->quantity,
                    (int) $row->unit_price,
                    (int) $row->unit_price * (int) $row->quantity,
                ]);
            }

            fclose($out);
        }, 'instashop-sales-'.now()->format('Ymd-His').'.csv');
    }

    /** CSV of per-product views inside the selected views window. */
    public function exportViewsCsv()
    {
        $seller = auth('seller')->user();
        $shopId = (int) ($seller?->shop?->id ?? 0);

        [$from, $to] = $this->resolveRange($this->viewsFrom, $this->viewsTo, $this->viewsMode);

        $products = collect();

        if ($shopId !== 0) {
            $periodViews = ProductView::query()
                ->where('shop_id', $shopId)
                ->where('day', '>=', $from->toDateString())
                ->where('day', '<=', $to->toDateString())
                ->selectRaw('product_id, SUM(views) as v')
                ->groupBy('product_id')
                ->pluck('v', 'product_id');

            $products = Product::query()
                ->where('shop_id', $shopId)
                ->orderByDesc('views')
                ->get(['id', 'name', 'status', 'views'])
                ->map(fn ($p) => [
                    'name'   => $p->name,
                    'status' => $p->status === 'published' ? 'published' : 'draft',
                    'period' => (int) ($periodViews[$p->id] ?? 0),
                    'total'  => (int) $p->views,
                ]);
        }

        return response()->streamDownload(function () use ($products) {
            $out = fopen('php://output', 'w');

            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['product', 'status', 'views_in_period', 'views_total']);

            foreach ($products as $p) {
                fputcsv($out, [$p['name'], $p['status'], $p['period'], $p['total']]);
            }

            fclose($out);
        }, 'instashop-product-views-'.now()->format('Ymd-His').'.csv');
    }

    /* ───────────────────────── render ───────────────────────── */

    public function render()
    {
        $seller = auth('seller')->user();
        $shop   = $seller?->shop;
        $shopId = (int) ($shop?->id ?? 0);

        [$salesFrom, $salesTo] = $this->resolveRange($this->salesFrom, $this->salesTo, $this->salesMode);
        [$viewsFrom, $viewsTo] = $this->resolveRange($this->viewsFrom, $this->viewsTo, $this->viewsMode);

        $sales = $this->salesChart($shopId, $this->salesMode, $salesFrom, $salesTo);
        $views = $this->viewsChart($shopId, $this->viewsMode, $viewsFrom, $viewsTo);

        $viewsDays = max(1, (int) $viewsFrom->diffInDays($viewsTo->endOfDay()) + 1);

        return view('livewire.seller.reports-page', [
            'shop'        => $shop,
            'orderStats'  => $this->orderStats,
            'viewStats'   => $this->viewStats,
            'sales'       => $sales,
            'views'       => $views,
            'viewsDays'   => $viewsDays,
            'topProducts' => $this->topProducts,
        ])->title('گزارش‌ها');
    }

    /* ───────────────────────── data ─────────────────────────
     * Period-independent stats are memoized computed properties
     * AND 60 s cached — a mode/range click must NOT re-run them.
     * The two charts are cached per (shop, mode, window) for 60 s
     * so flipping granularities back and forth is near-instant.
     * Only the chart the user actually switched may take time. */

    /** Order funnel of this shop (via order_items) + revenue + buyers. */
    public function getOrderStatsProperty(): array
    {
        $shopId = (int) (auth('seller')->user()?->shop?->id ?? 0);

        return Cache::remember(
            'ip:reports:order-stats:v2:'.$shopId,
            60,
            fn () => $this->computeOrderStats($shopId),
        );
    }

    private function computeOrderStats(int $shopId): array
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
    public function getViewStatsProperty(): array
    {
        $shopId = (int) (auth('seller')->user()?->shop?->id ?? 0);

        return Cache::remember(
            'ip:reports:view-stats:v2:'.$shopId,
            60,
            fn () => $this->computeViewStats($shopId),
        );
    }

    private function computeViewStats(int $shopId): array
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

    /** Top 6 products by cumulative views (computed only — Eloquent models
     * must never go through the cache, which unserializes with
     * allowed_classes=false and turns them into __PHP_Incomplete_Class). */
    public function getTopProductsProperty(): Collection
    {
        $shopId = (int) (auth('seller')->user()?->shop?->id ?? 0);

        return Product::query()
            ->where('shop_id', $shopId)
            ->with('mainImage')
            ->orderByDesc('views')
            ->orderByDesc('id')
            ->limit(6)
            ->get(['id', 'shop_id', 'name', 'price', 'views', 'status']);
    }

    /** Paid-sales series: buckets for the mode+range, values from paid order_items. */
    private function salesChart(int $shopId, string $mode, CarbonImmutable $from, CarbonImmutable $to): array
    {
        return Cache::remember(
            'ip:reports:sales:v2:'.$shopId.':'.$mode.':'.$from->format('YmdHi').':'.$to->format('YmdHi'),
            60,
            fn () => $this->computeSalesChart($shopId, $mode, $from, $to),
        );
    }

    private function computeSalesChart(int $shopId, string $mode, CarbonImmutable $from, CarbonImmutable $to): array
    {
        $buckets = $this->periodBuckets($mode, $from, $to);
        $total   = 0;
        $orders  = 0;

        if ($shopId !== 0) {
            $rows = OrderItem::query()
                ->selectRaw('order_items.unit_price, order_items.quantity, orders.paid_at, orders.id as order_id')
                ->join('orders', 'orders.id', '=', 'order_items.order_id')
                ->where('order_items.shop_id', $shopId)
                ->where('orders.status', 'paid')
                ->whereBetween('orders.paid_at', [$from, $to])
                ->get();

            $orders = $rows->pluck('order_id')->unique()->count();
            $total  = (int) $rows->sum(fn ($r) => (int) $r->unit_price * (int) $r->quantity);

            foreach ($rows as $row) {
                if (! $row->paid_at) {
                    continue;
                }

                $key = $this->bucketKey($mode, CarbonImmutable::parse($row->paid_at));

                if (isset($buckets[$key])) {
                    $buckets[$key]['value'] += (int) $row->unit_price * (int) $row->quantity;
                }
            }
        }

        return $this->chartResult($buckets, ['total' => $total, 'orders' => $orders]);
    }

    /** Views series: buckets for the mode+range, values from product_views day rows. */
    private function viewsChart(int $shopId, string $mode, CarbonImmutable $from, CarbonImmutable $to): array
    {
        return Cache::remember(
            'ip:reports:views:v2:'.$shopId.':'.$mode.':'.$from->format('YmdHi').':'.$to->format('YmdHi'),
            60,
            fn () => $this->computeViewsChart($shopId, $mode, $from, $to),
        );
    }

    private function computeViewsChart(int $shopId, string $mode, CarbonImmutable $from, CarbonImmutable $to): array
    {
        $buckets = $this->periodBuckets($mode, $from, $to);
        $total   = 0;

        if ($shopId !== 0) {
            $rows = ProductView::query()
                ->where('shop_id', $shopId)
                ->where('day', '>=', $from->toDateString())
                ->where('day', '<=', $to->toDateString())
                ->selectRaw('day, SUM(views) as v')
                ->groupBy('day')
                ->pluck('v', 'day');

            foreach ($rows as $day => $v) {
                $key = $this->bucketKey($mode, CarbonImmutable::parse($day.' 00:00:00'));

                if (isset($buckets[$key])) {
                    $buckets[$key]['value'] += (int) $v;
                }
            }

            $total = array_sum(array_column($buckets, 'value'));
        }

        return $this->chartResult($buckets, ['total' => $total]);
    }

    /* ───────────────────── bucket machinery ───────────────────── */

    private function normalizeMode(string $mode): string
    {
        return in_array($mode, self::MODES, true) ? $mode : 'daily';
    }

    /** Sensible default window per granularity. */
    private function defaultWindow(string $mode): array
    {
        $today = CarbonImmutable::today();

        return match ($mode) {
            'weekly'  => [$today->subWeeks(11)->startOfWeek(Carbon::SATURDAY)->toDateString(), $today->toDateString()],
            'monthly' => [$today->subMonths(11)->startOfMonth()->toDateString(), $today->toDateString()],
            'yearly'  => [$today->subYears(4)->startOfYear()->toDateString(), $today->toDateString()],
            default   => [$today->subDays(6)->toDateString(), $today->toDateString()],
        };
    }

    /** Parse + sanitize a Jalali/Gregorian range and clamp it per mode caps. */
    private function resolveRange(string $from, string $to, string $mode): array
    {
        try {
            $f = CarbonImmutable::parse($from)->startOfDay();
            $t = CarbonImmutable::parse($to)->endOfDay();
        } catch (\Throwable) {
            [$fs, $ts] = $this->defaultWindow($mode);
            $f = CarbonImmutable::parse($fs)->startOfDay();
            $t = CarbonImmutable::parse($ts)->endOfDay();
        }

        if ($f->gt($t)) {
            [$f, $t] = [$t->startOfDay(), $f->endOfDay()];
        }

        // hard caps keep the query + loop light on shared hosting
        $today = CarbonImmutable::today();

        $f = match ($mode) {
            'daily'   => $f->max($today->subDays(91)->startOfDay()),
            'weekly'  => $f->max($today->subDays(364)->startOfDay()),
            'monthly' => $f->max($today->subYears(3)->startOfDay()),
            default   => $f->max($today->subYears(9)->startOfDay()),
        };

        return [$f, $t->min($today->endOfDay())];
    }

    /** Which bucket does this timestamp belong to for the given mode? */
    private function bucketKey(string $mode, CarbonImmutable $date): string
    {
        return match ($mode) {
            'weekly'  => $date->startOfWeek(Carbon::SATURDAY)->toDateString(),
            'monthly' => $date->format('Y-m'),
            'yearly'  => $date->format('Y'),
            default   => $date->toDateString(),
        };
    }

    /** Labeled, zero-filled buckets covering the range for the given mode. */
    private function periodBuckets(string $mode, CarbonImmutable $from, CarbonImmutable $to): array
    {
        $buckets = [];
        $today   = CarbonImmutable::today()->toDateString();

        if ($mode === 'daily') {
            $cursor = $from->startOfDay();
            $steps  = min((int) $from->diffInDays($to->endOfDay()) + 1, 92);

            for ($i = 0; $i < $steps; $i++) {
                $key = $cursor->toDateString();

                $buckets[$key] = [
                    'label' => $cursor->locale('fa')->minDayName.' '.Fmt::fa($cursor->day),
                    'sort'  => $key,
                    'value' => 0,
                    'today' => $key === $today,
                ];
                $cursor = $cursor->addDay();
            }
        } elseif ($mode === 'weekly') {
            $cursor = $from->startOfWeek(Carbon::SATURDAY)->startOfDay();
            $steps  = min((int) $cursor->diffInWeeks($to->endOfDay()) + 1, 52);

            for ($i = 0; $i < $steps; $i++) {
                $key = $cursor->toDateString();

                $buckets[$key] = [
                    'label' => Fmt::fa($cursor->day).' '.$cursor->locale('fa')->monthName,
                    'sort'  => $key,
                    'value' => 0,
                    'today' => $key === $this->bucketKey('weekly', CarbonImmutable::today()),
                ];
                $cursor = $cursor->addWeeks(1);
            }
        } elseif ($mode === 'monthly') {
            $cursor  = $from->startOfMonth();
            $steps   = min((int) $cursor->diffInMonths($to->endOfDay()) + 1, 36);
            $spanYrs = $cursor->diffInYears($to->endOfDay()) >= 1;

            for ($i = 0; $i < $steps; $i++) {
                $key = $cursor->format('Y-m');

                $buckets[$key] = [
                    'label' => $cursor->locale('fa')->monthName.($spanYrs ? ' '.Fmt::fa($cursor->year) : ''),
                    'sort'  => $key,
                    'value' => 0,
                    'today' => $key === CarbonImmutable::today()->format('Y-m'),
                ];
                $cursor = $cursor->addMonths(1)->startOfMonth();
            }
        } else {
            $cursor = $from->startOfYear();
            $steps  = min((int) $cursor->diffInYears($to->endOfDay()) + 1, 10);

            for ($i = 0; $i < $steps; $i++) {
                $key = $cursor->format('Y');

                $buckets[$key] = [
                    'label' => Fmt::fa($cursor->year),
                    'sort'  => $key,
                    'value' => 0,
                    'today' => $key === CarbonImmutable::today()->format('Y'),
                ];
                $cursor = $cursor->addYears(1)->startOfYear();
            }
        }

        return $buckets;
    }

    /** Normalize buckets into the series shape the blade renders.
     * The series MUST be a plain array — the cache unserializes with
     * allowed_classes=false, so Collections/objects do not survive
     * a cache round-trip. */
    private function chartResult(array $buckets, array $extra = []): array
    {
        $series = collect($buckets)
            ->sortBy('sort')
            ->map(fn ($b) => [
                'label' => $b['label'],
                'value' => (int) $b['value'],
                'today' => $b['today'],
            ])
            ->values();

        return array_merge([
            'series' => $series->all(),
            'total'  => (int) $series->sum('value'),
            'max'    => max($series->max('value'), 1),
        ], $extra);
    }
}
