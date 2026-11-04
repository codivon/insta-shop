<?php

namespace App\Livewire\Admin;

use App\Livewire\Admin\Concerns\GuardsPermissions;
use App\Models\ActivityLog;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\Seller;
use App\Models\Shop;
use App\Support\SchemaReady;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Admin dashboard — real system-wide statistics.
 * Replaces the Phase-1 placeholder ("roadmap") page.
 */
#[Layout('layouts.admin-slot')]
class Dashboard extends Component
{
    use GuardsPermissions;

    protected static string $permission = 'admin.dashboard';

    // ── Computed stats ───────────────────────────────────────────────
    // Every aggregate below goes through Cache::remember with a 60s TTL —
    // the dashboard re-runs these GROUP BY queries on every render and on
    // slow shared hosting they were the biggest page-load cost (N9).
    private const STATS_TTL = 60;

    // v2: JSON-ONLY payloads. config/cache.php ships «serializable_classes =>
    // false» (gadget-chain hardening), so any OBJECT read back from cache
    // unserializes to __PHP_Incomplete_Class — the chart Collection crashed
    // every dashboard load with «Return value must be of type ...Collection,
    // __PHP_Incomplete_Class returned». Strings survive the round-trip
    // untouched; the new key namespace also orphans any poisoned old entry.
    private function cached(string $key, callable $compute): mixed
    {
        $cacheKey = 'admin.dash.v2.'.$key;

        try {
            $hit = Cache::get($cacheKey);

            if (is_string($hit)) {
                try {
                    $decoded = json_decode($hit, true, 512, JSON_THROW_ON_ERROR);
                } catch (\JsonException) {
                    $decoded = null; // corrupted payload → recompute below
                }

                if (is_array($decoded)) {
                    return $decoded;
                }
            }

            $fresh = $compute();

            Cache::put($cacheKey, json_encode($fresh, JSON_UNESCAPED_UNICODE), self::STATS_TTL);

            return $fresh;
        } catch (\Throwable) {
            return $compute(); // cache store unavailable → compute directly
        }
    }

    public function getSellersProperty(): array
    {
        return $this->cached('sellers', function (): array {
            $byStatus = Seller::query()
                ->selectRaw('status, count(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status');

            return [
                'total' => (int) $byStatus->sum(),
                'pending' => (int) ($byStatus['pending'] ?? 0),
                'approved' => (int) ($byStatus['approved'] ?? 0),
            ];
        });
    }

    public function getCustomersProperty(): array
    {
        return $this->cached('customers', function (): array {
            $byStatus = Customer::query()
                ->selectRaw('status, count(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status');

            return [
                'total' => (int) $byStatus->sum(),
                'blocked' => (int) ($byStatus['blocked'] ?? 0),
            ];
        });
    }

    public function getShopsProperty(): array
    {
        return $this->cached('shops', function (): array {
            $byStatus = Shop::query()
                ->selectRaw('status, count(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status');

            return [
                'total' => (int) $byStatus->sum(),
                'active' => (int) ($byStatus['active'] ?? 0),
            ];
        });
    }

    public function getProductsProperty(): array
    {
        return $this->cached('products', function (): array {
            $byStatus = Product::query()
                ->selectRaw('status, count(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status');

            return [
                'total' => (int) $byStatus->sum(),
                'published' => (int) ($byStatus['published'] ?? 0),
                'draft' => (int) ($byStatus['draft'] ?? 0),
            ];
        });
    }

    public function getOrderStatsProperty(): array
    {
        return $this->cached('orderStats', function (): array {
            $byStatus = Order::query()
                ->selectRaw('status, count(*) as total, coalesce(sum(total_amount),0) as amount')
                ->groupBy('status')
                ->get()
                ->keyBy('status');

            $paidAmount = (int) ($byStatus['paid']->amount ?? 0);

            $today = Order::query()
                ->whereDate('created_at', Carbon::today())
                ->selectRaw('count(*) as total, coalesce(sum(case when status = ? then total_amount else 0 end),0) as paid_amount', ['paid'])
                ->first();

            return [
                'total' => (int) $byStatus->sum('total'),
                'paid' => (int) ($byStatus['paid']->total ?? 0),
                'pending' => (int) ($byStatus['pending']->total ?? 0),
                'paidAmount' => $paidAmount,
                'todayCount' => (int) ($today->total ?? 0),
                'todayPaid' => (int) ($today->paid_amount ?? 0),
            ];
        });
    }

    /** Orders per day for the last 7 days (count + paid amount).
     *  Computes a PLAIN ARRAY (JSON-cache friendly) and wraps it in a
     *  Collection so the view keeps its ->sum()/->max() API. */
    public function getChartProperty(): Collection
    {
        return collect($this->cached('chart', function (): array {
            $from = Carbon::today()->subDays(6);

            $rows = Order::query()
                ->whereBetween('created_at', [$from->copy()->startOfDay(), Carbon::today()->endOfDay()])
                ->selectRaw("date(created_at) as day, count(*) as orders,
                    coalesce(sum(case when status = 'paid' then total_amount else 0 end),0) as amount")
                ->groupBy('day')
                ->get()
                ->keyBy(fn ($row) => (string) $row->day);

            $out = [];

            for ($i = 0; $i < 7; $i++) {
                $day = $from->copy()->addDays($i);
                $key = $day->toDateString();
                $row = $rows[$key] ?? null;

                $out[] = [
                    'label' => $day->locale('fa')->dayName,
                    'orders' => (int) ($row->orders ?? 0),
                    'amount' => (int) ($row->amount ?? 0),
                    'today' => $i === 6,
                ];
            }

            return $out;
        }));
    }

    public function getRecentOrdersProperty(): Collection
    {
        return Order::query()
            ->with(['customer:id,full_name,phone'])
            ->latest('id')
            ->limit(8)
            ->get();
    }

    public function render()
    {
        return view('livewire.admin.dashboard', [
            'sellers' => $this->sellers,
            'customers' => $this->customers,
            'shops' => $this->shops,
            'products' => $this->products,
            'orderStats' => $this->orderStats,
            'chart' => $this->chart,
            'recentOrders' => $this->recentOrders,
            'recentActivities' => $this->recentActivities(),
        ])->title('داشبورد مدیریت')
            ->layout('layouts.admin-slot', ['breadcrumb' => 'مدیریت کل — داشبورد']);
    }

    /** Latest audit-trail entries for the «فعالیت‌های اخیر» widget. */
    protected function recentActivities()
    {
        try {
            if (! SchemaReady::check('activity_logs')) {
                return collect();
            }

            return ActivityLog::query()->latest()->limit(7)->get();
        } catch (\Throwable) {
            return collect();
        }
    }
}
