<?php

namespace App\Livewire\Admin;

use App\Livewire\Admin\Concerns\GuardsPermissions;
use App\Models\Order;
use App\Models\Shop;
use App\Models\WalletTransaction;
use App\Support\ActivityLogger;
use App\Support\Csv;
use App\Support\Fmt;
use App\Support\JDate;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Admin — «تسویه‌ها» (مالی و حسابداری): settlement view of the platform —
 * how the money of paid orders is split per shop, how much went out as
 * settlements and what is still waiting. Cards + Jalali bar chart +
 * date-range filters (شمسی) + per-shop settlement table + CSV.
 */
#[Layout('layouts.admin-slot')]
#[Title('تسویه‌ها')]
class SettlementsPage extends Component
{
    use GuardsPermissions;

    protected static string $permission = 'admin.finance';

    #[Url]
    public int $shopId = 0;

    public string $fromDate = '';

    public string $toDate = '';

    public int $perPage = 20;

    public bool $hasMore = true;

    private int $lastLoadAt = 0;

    public function updating(string $property): void
    {
        if (in_array($property, ['shopId', 'fromDate', 'toDate'], true)) {
            $this->perPage = 20;
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
        $this->perPage += 20;
    }

    protected function range(): array
    {
        $from = JDate::parse($this->fromDate)?->startOfDay();
        $to = JDate::parse($this->toDate)?->endOfDay();

        if ($from && $to && $from->gt($to)) {
            [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
        }

        return [$from, $to];
    }

    /** Commission rows flattened from paid orders, filtered by range + shop. */
    protected function commissionRows(): Collection
    {
        [$from, $to] = $this->range();

        $orders = Order::query()
            ->where('status', 'paid')
            ->whereNotNull('commissions')
            ->when($from, fn ($q) => $q->where('paid_at', '>=', $from))
            ->when($to, fn ($q) => $q->where('paid_at', '<=', $to))
            ->get(['id', 'commissions', 'paid_at']);

        return $orders->flatMap(function ($order) {
            return collect($order->commissions ?? [])->map(fn (array $row) => (object) [
                'order_id' => (int) $order->id,
                'paid_at' => $order->paid_at,
                'shop_id' => (int) ($row['shop_id'] ?? 0),
                'shop_name' => (string) ($row['shop_name'] ?? '—'),
                'items_total' => (int) ($row['items_total'] ?? 0),
                'commission' => (int) ($row['commission_amount'] ?? 0),
                'payout' => (int) ($row['payout'] ?? 0),
            ]);
        })->when($this->shopId > 0, fn ($c) => $c->filter(fn ($r) => $r->shop_id === $this->shopId))->values();
    }

    public function getMetricsProperty(): array
    {
        [$from, $to] = $this->range();

        $rows = $this->commissionRows();

        $withdrawals = WalletTransaction::query()
            ->where('type', 'withdrawal')
            ->when($from, fn ($q) => $q->where('created_at', '>=', $from))
            ->when($to, fn ($q) => $q->where('created_at', '<=', $to));

        $done = (clone $withdrawals)->where('status', 'done')->get(['amount']);
        $pending = (clone $withdrawals)->where('status', 'pending');

        return [
            'orders_count' => $rows->pluck('order_id')->unique()->count(),
            'goods' => (int) $rows->sum('items_total'),
            'commission' => (int) $rows->sum('commission'),
            'payout' => (int) $rows->sum('payout'),
            'settled' => (int) $done->sum('amount'),
            'pending_sum' => (int) $pending->clone()->sum('amount'),
            'pending_count' => (int) $pending->count(),
        ];
    }

    /** Weekly (last 8 weeks) settled bar chart data. */
    public function getWeeklyProperty(): Collection
    {
        $buckets = collect();

        for ($i = 7; $i >= 0; $i--) {
            $start = Carbon::today()->subWeeks($i)->startOfWeek()->copy();
            $end = $start->copy()->endOfWeek();

            $q = WalletTransaction::query()
                ->where('type', 'withdrawal')
                ->where('status', 'done')
                ->whereBetween('created_at', [$start, $end]);

            if ($this->shopId > 0) {
                $sellerId = Shop::query()->find($this->shopId)?->seller_id;

                $q->whereHas('wallet', fn ($w) => $w->where('seller_id', (int) $sellerId));
            }

            $buckets->push([
                'label' => JDate::date($start),
                'settled' => (int) $q->sum('amount'),
            ]);
        }

        return $buckets;
    }

    /** Per-shop settlement aggregates for the table. */
    public function getShopsProperty(): Collection
    {
        $rows = $this->commissionRows();

        return $rows
            ->groupBy('shop_id')
            ->map(fn ($group, $shopId) => (object) [
                'shop_id' => (int) $shopId,
                'shop_name' => $group->first()->shop_name,
                'orders' => $group->pluck('order_id')->unique()->count(),
                'goods' => (int) $group->sum('items_total'),
                'commission' => (int) $group->sum('commission'),
                'payout' => (int) $group->sum('payout'),
            ])
            ->sortByDesc('payout')
            ->values();
    }

    public function getShopOptionsProperty(): Collection
    {
        return Shop::query()->orderBy('name')->get(['id', 'name']);
    }

    public function exportCsv(): StreamedResponse
    {
        $shops = $this->shops;

        ActivityLogger::log('finance.export', 'خروجی CSV تسویه‌ها گرفته شد ('.Fmt::fa($shops->count()).' فروشگاه)');

        return response()->streamDownload(function () use ($shops) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['shop', 'orders', 'goods', 'commission', 'payout']);

            foreach ($shops as $s) {
                fputcsv($out, Csv::row([$s->shop_name, $s->orders, $s->goods, $s->commission, $s->payout]));
            }

            fclose($out);
        }, 'instashop-settlements-'.now()->format('Ymd-His').'.csv');
    }

    public function resetFilters(): void
    {
        $this->shopId = 0;
        $this->fromDate = '';
        $this->toDate = '';
    }

    public function render()
    {
        return view('livewire.admin.settlements-page', [
            'metrics' => $this->metrics,
            'weekly' => $this->weekly,
            'shops' => $this->shops,
            'shopOptions' => $this->shopOptions,
        ])->title('تسویه‌ها')
            ->layout('layouts.admin-slot', ['breadcrumb' => 'مدیریت کل — مالی و حسابداری — تسویه‌ها']);
    }
}
