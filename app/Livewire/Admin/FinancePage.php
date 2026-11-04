<?php

namespace App\Livewire\Admin;

use App\Livewire\Admin\Concerns\GuardsPermissions;
use App\Models\Order;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Support\ActivityLogger;
use App\Support\Fmt;
use App\Support\JDate;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Admin — «گزارش مالی» (مالی و حسابداری): the full financial picture of
 * the platform in one page:
 *   • headline cards (paid orders volume, commission base, settled total,
 *     seller wallet float);
 *   • settlement-share composition (platform vs sellers vs pending);
 *   • payments & wallets status panel (pending withdrawals…);
 *   • a «۶ ماه اخیر» Jalali bar chart of paid volume;
 *   • a per-Jalali-month breakdown table;
 *   • the wallet transactions ledger with search / type / status / date
 *     (Jalali) filters + CSV export.
 */
#[Layout('layouts.admin-slot')]
#[Title('گزارش مالی')]
class FinancePage extends Component
{
    use GuardsPermissions;

    protected static string $permission = 'admin.finance';

    /* ── ledger filters ── */
    public string $search = '';

    public string $typeFilter = 'all'; // all | deposit | withdrawal

    public string $statusFilter = 'all'; // all | done | pending | rejected

    /** Jalali «YYYY/MM/DD» shown to the user; stored ISO internally. */
    public string $fromDate = '';

    public string $toDate = '';

    public int $perPage = 25;

    public bool $hasMore = true;

    private int $lastLoadAt = 0;

    public function updating(string $property): void
    {
        if (in_array($property, ['search', 'typeFilter', 'statusFilter', 'fromDate', 'toDate'], true)) {
            $this->perPage = 25;
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
        $this->perPage += 25;
    }

    /** Resolved [from, to] Carbon bounds from the Jalali inputs (null = open). */
    protected function range(): array
    {
        $from = JDate::parse($this->fromDate)?->startOfDay();
        $to = JDate::parse($this->toDate)?->endOfDay();

        if ($from && $to && $from->gt($to)) {
            [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
        }

        return [$from, $to];
    }

    /** Paid orders inside the selected range. */
    protected function paidOrders()
    {
        [$from, $to] = $this->range();

        return Order::query()
            ->where('status', 'paid')
            ->when($from, fn ($q) => $q->where('paid_at', '>=', $from))
            ->when($to, fn ($q) => $q->where('paid_at', '<=', $to))
            ->get(['id', 'total_amount', 'commissions', 'paid_at']);
    }

    /* ══════════════ headline metrics (cached per render) ══════════════ */

    public function getMetricsProperty(): array
    {
        $orders = $this->paidOrders();

        $commissionRows = $orders->flatMap(fn ($o) => collect($o->commissions ?? []));

        $settled = (int) WalletTransaction::query()->where('type', 'withdrawal')->where('status', 'done')->sum('amount');
        $pendingW = WalletTransaction::query()->where('type', 'withdrawal')->where('status', 'pending');
        $pendingW = (clone $pendingW);
        $pendingSum = (int) $pendingW->sum('amount');
        $pendingCount = (int) $pendingW->count();
        $walletFloat = (int) Wallet::query()->sum('balance');

        return [
            'paid_count' => $orders->count(),
            'paid_total' => (int) $orders->sum('total_amount'),
            'goods_total' => (int) $commissionRows->sum('items_total'),
            'commission' => (int) $commissionRows->sum('commission_amount'),
            'payout' => (int) $commissionRows->sum('payout'),
            'settled' => $settled,
            'pending_sum' => $pendingSum,
            'pending_count' => $pendingCount,
            'wallet_float' => $walletFloat,
        ];
    }

    /* ══════════════ 6-month Jalali chart + monthly table ═════════════ */

    public function getMonthlyProperty(): Collection
    {
        [$from, $to] = $this->range();

        $orders = Order::query()
            ->where('status', 'paid')
            ->when($from, fn ($q) => $q->where('paid_at', '>=', $from))
            ->when($to, fn ($q) => $q->where('paid_at', '<=', $to))
            ->where('paid_at', '>=', Carbon::today()->subMonths(6)->startOfDay())
            ->get(['total_amount', 'commissions', 'paid_at']);

        $withdrawals = WalletTransaction::query()
            ->where('type', 'withdrawal')
            ->where('status', 'done')
            ->when($from, fn ($q) => $q->where('created_at', '>=', $from))
            ->when($to, fn ($q) => $q->where('created_at', '<=', $to))
            ->where('created_at', '>=', Carbon::today()->subMonths(6)->startOfDay())
            ->get(['amount', 'created_at']);

        // build the LAST 6 Jalali month buckets (oldest → newest)
        $buckets = [];
        $cursor = Carbon::today()->subMonths(5)->startOfDay();

        for ($i = 0; $i < 6; $i++) {
            $key = JDate::monthKey($cursor);

            $buckets[$key] = [
                'key' => $key,
                'label' => JDate::monthName((int) explode('-', $key)[1]).' '.Fmt::fa(explode('-', $key)[0]),
                'orders' => 0,
                'paid' => 0,
                'goods' => 0,
                'commission' => 0,
                'settled' => 0,
            ];

            $cursor->addMonth();
        }

        foreach ($orders as $order) {
            $key = JDate::monthKey($order->paid_at ?? $order->created_at);

            if (! isset($buckets[$key])) {
                continue;
            }

            $buckets[$key]['orders']++;
            $buckets[$key]['paid'] += (int) $order->total_amount;

            foreach (collect($order->commissions ?? []) as $row) {
                $buckets[$key]['goods'] += (int) ($row['items_total'] ?? 0);
                $buckets[$key]['commission'] += (int) ($row['commission_amount'] ?? 0);
            }
        }

        foreach ($withdrawals as $wd) {
            $key = JDate::monthKey($wd->created_at ?? now());

            if (isset($buckets[$key])) {
                $buckets[$key]['settled'] += (int) $wd->amount;
            }
        }

        return collect(array_values($buckets));
    }

    /* ══════════════ transactions ledger ═══════════════════════════════ */

    protected function ledgerQuery()
    {
        [$from, $to] = $this->range();

        return WalletTransaction::query()
            ->with(['wallet.seller:id,full_name', 'order:id,discount_code'])
            ->when($this->typeFilter !== 'all', fn ($q) => $q->where('type', $this->typeFilter))
            ->when($this->statusFilter !== 'all', fn ($q) => $q->where('status', $this->statusFilter))
            ->when(trim($this->search) !== '', function ($q) {
                $needle = mb_strtolower(trim($this->search));

                $q->where(function ($q2) use ($needle) {
                    $q2->whereRaw('LOWER(description) LIKE ?', ['%'.$needle.'%'])
                        ->orWhereRaw('LOWER(sheba) LIKE ?', ['%'.$needle.'%'])
                        ->orWhere('order_id', (int) preg_replace('/\D/', '', $this->search) ?: 0);
                });
            })
            ->when($from, fn ($q) => $q->where('created_at', '>=', $from))
            ->when($to, fn ($q) => $q->where('created_at', '<=', $to))
            ->latest('id');
    }

    public function getLedgerProperty(): Collection
    {
        $rows = $this->ledgerQuery()->limit($this->perPage)->get();
        $total = (clone $this->ledgerQuery())->count();

        $this->hasMore = $total > $this->perPage;

        return $rows;
    }

    public function getLedgerTotalProperty(): int
    {
        return (clone $this->ledgerQuery())->count();
    }

    /** CSV export of the CURRENTLY filtered ledger. */
    public function exportCsv(): StreamedResponse
    {
        $rows = $this->ledgerQuery()->limit(5000)->get();

        ActivityLogger::log('finance.export', 'خروجی CSV گزارش مالی گرفته شد ('.Fmt::fa($rows->count()).' تراکنش)');

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');

            // UTF-8 BOM so Excel shows Persian correctly
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['type', 'date', 'amount', 'status', 'order', 'description']);

            foreach ($rows as $row) {
                fputcsv($out, [
                    $row->typeLabel(),
                    $row->created_at ? $row->created_at->format('Y-m-d H:i') : '',
                    $row->amount,
                    $row->statusLabel(),
                    $row->order_id ?: '',
                    $row->description,
                ]);
            }

            fclose($out);
        }, 'instashop-finance-'.now()->format('Ymd-His').'.csv');
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->typeFilter = 'all';
        $this->statusFilter = 'all';
        $this->fromDate = '';
        $this->toDate = '';
        $this->perPage = 25;
        $this->hasMore = true;
    }

    public function render()
    {
        return view('livewire.admin.finance-page', [
            'metrics' => $this->metrics,
            'monthly' => $this->monthly,
            'ledger' => $this->ledger,
            'ledgerTotal' => $this->ledgerTotal,
        ])->title('گزارش مالی')
            ->layout('layouts.admin-slot', ['breadcrumb' => 'مدیریت کل — مالی و حسابداری — گزارش مالی']);
    }
}
