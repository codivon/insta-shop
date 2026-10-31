<?php

namespace App\Livewire\Admin;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\Seller;
use App\Models\Shop;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Admin dashboard — real system-wide statistics.
 * Replaces the Phase-1 placeholder ("roadmap") page.
 */
#[Layout('layouts.admin-slot')]
class Dashboard extends Component
{
    // ── Computed stats ───────────────────────────────────────────────

    public function getSellersProperty(): array
    {
        $byStatus = Seller::query()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return [
            'total' => (int) $byStatus->sum(),
            'pending' => (int) ($byStatus['pending'] ?? 0),
            'approved' => (int) ($byStatus['approved'] ?? 0),
        ];
    }

    public function getCustomersProperty(): array
    {
        $byStatus = Customer::query()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return [
            'total' => (int) $byStatus->sum(),
            'blocked' => (int) ($byStatus['blocked'] ?? 0),
        ];
    }

    public function getShopsProperty(): array
    {
        $byStatus = Shop::query()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return [
            'total' => (int) $byStatus->sum(),
            'active' => (int) ($byStatus['active'] ?? 0),
        ];
    }

    public function getProductsProperty(): array
    {
        $byStatus = Product::query()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return [
            'total' => (int) $byStatus->sum(),
            'published' => (int) ($byStatus['published'] ?? 0),
            'draft' => (int) ($byStatus['draft'] ?? 0),
        ];
    }

    public function getOrderStatsProperty(): array
    {
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
    }

    /** Orders per day for the last 7 days (count + paid amount). */
    public function getChartProperty(): Collection
    {
        $from = Carbon::today()->subDays(6);

        $rows = Order::query()
            ->whereBetween('created_at', [$from->copy()->startOfDay(), Carbon::today()->endOfDay()])
            ->selectRaw("date(created_at) as day, count(*) as orders,
                coalesce(sum(case when status = 'paid' then total_amount else 0 end),0) as amount")
            ->groupBy('day')
            ->get()
            ->keyBy(fn ($row) => (string) $row->day);

        $out = collect();

        for ($i = 0; $i < 7; $i++) {
            $day = $from->copy()->addDays($i);
            $key = $day->toDateString();
            $row = $rows[$key] ?? null;

            $out->push([
                'label' => $day->locale('fa')->dayName,
                'orders' => (int) ($row->orders ?? 0),
                'amount' => (int) ($row->amount ?? 0),
                'today' => $i === 6,
            ]);
        }

        return $out;
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
        ])->title('داشبورد مدیریت')
            ->layout('layouts.admin-slot', ['breadcrumb' => 'مدیریت کل — داشبورد']);
    }
}
