<?php

namespace App\Livewire\Admin;

use App\Livewire\Admin\Concerns\GuardsPermissions;
use App\Models\BnplSettlement;
use App\Support\Fmt;
use App\Support\Payments\BnplSettlements;
use App\Support\Payments\PaymentService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Admin — «تسویه اقساطی» (v0.30.0): the standalone section that mirrors
 * the monthly rhythm of the BNPL gateways (دیجی‌پی / اسنپ‌پی / ترب‌پی).
 *
 * A paid installment order created one PENDING BnplSettlement row per
 * shop (the seller wallet is NOT credited instantly — these gateways
 * settle their merchants monthly). Every pending row is listed here with
 * its gateway / seller / amount breakdown / due date, plus:
 *   • «واریز دستی» — deposit a single pending row into the seller wallet
 *     right now (same atomic engine the daily cron uses),
 *   • «لغو» — mark a row canceled (rarely: refunded orders) with a note.
 *
 * The daily cron `bnpl:settle` (scheduled 06:00 in routes/console.php)
 * deposits every pending row whose due_date has arrived.
 */
#[Layout('layouts.admin-slot')]
#[Title('تسویه اقساطی')]
class BnplSettlementsPage extends Component
{
    use GuardsPermissions;
    use WithPagination;

    protected static string $permission = 'admin.bnpl.settlements';

    /** 'all' | gateway key */
    public string $gateway = 'all';

    /** 'all' | 'pending' | 'overdue' | 'paid' | 'canceled' */
    public string $status = 'all';

    /** free search: order id / seller name */
    public string $search = '';

    public function updatedGateway(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->gateway = 'all';
        $this->status = 'all';
        $this->search = '';

        $this->resetPage();
    }

    /** واریز دستی — deposit ONE pending settlement into the seller wallet. */
    public function depositNow(int $id): void
    {
        $settlement = BnplSettlement::query()->find($id);

        if (! $settlement) {
            return;
        }

        if (BnplSettlements::deposit($settlement, manual: true, note: 'واریز دستی توسط مدیر')) {
            session()->flash('bnpl-admin-flash', 'مبلغ '.Fmt::money($settlement->amount)
                .' به کیف پول «'.($settlement->seller?->full_name ?? $settlement->seller?->instagram_username ?? '#'.$settlement->seller_id)
                .'» واریز شد.');
        } else {
            session()->flash('bnpl-admin-flash', 'این تسویه قبلاً واریز شده یا دیگر در انتظار نیست.');
        }
    }

    /**
     * v0.31.0 — واریز گروهی: deposit EVERY pending settlement whose due
     * date has arrived (ALL gateways — even manual-mode ones, because the
     * admin clicked the button on purpose). Shares the same atomic engine
     * as the cron; each row lands with the «واریز گروهی توسط مدیر» note.
     */
    public function depositDueNow(): void
    {
        $deposited = 0;
        $amount = 0;

        BnplSettlement::query()
            ->where('status', 'pending')
            ->whereDate('due_date', '<=', today())
            ->orderBy('due_date')
            ->chunkById(200, function ($rows) use (&$deposited, &$amount): void {
                foreach ($rows as $row) {
                    if (BnplSettlements::deposit($row, manual: true, note: 'واریز گروهی توسط مدیر')) {
                        $deposited++;
                        $amount += (int) $row->amount;
                    }
                }
            });

        session()->flash('bnpl-admin-flash', $deposited > 0
            ? Fmt::fa($deposited).' تسویهٔ سررسیدشده واریز شد — مجموع '.Fmt::money($amount).' به کیف پول فروشندگان.'
            : 'هیچ تسویهٔ سررسیدشده‌ای برای واریز گروهی وجود ندارد.');
    }

    /** لغو تسویه — mark canceled (refunded/canceled order) with a note. */
    public function cancelSettlement(int $id, string $note = ''): void
    {
        $claimed = BnplSettlement::query()
            ->whereKey($id)
            ->where('status', 'pending')
            ->update([
                'status' => 'canceled',
                'note' => mb_substr(trim($note) !== '' ? trim($note) : 'لغو توسط مدیر', 0, 190),
            ]);

        session()->flash('bnpl-admin-flash', $claimed > 0
            ? 'تسویه لغو شد و دیگر واریز نمی‌شود.'
            : 'این تسویه دیگر در انتظار نیست.');
    }

    /** Totals for the summary chips (pending / overdue / paid per scope). */
    public function getStatsProperty(): array
    {
        $base = BnplSettlement::query()
            ->when($this->gateway !== 'all', fn ($q) => $q->where('gateway', $this->gateway));

        return [
            'pending_count' => (clone $base)->where('status', 'pending')->count(),
            'pending_amount' => (int) (clone $base)->where('status', 'pending')->sum('amount'),
            'paid_amount' => (int) (clone $base)->where('status', 'paid')->sum('amount'),
            'due_now_count' => (clone $base)->where('status', 'pending')->whereDate('due_date', '<=', today())->count(),
            'overdue_count' => (clone $base)->where('status', 'pending')->whereDate('due_date', '<', today())->count(),
            'overdue_amount' => (int) (clone $base)->where('status', 'pending')->whereDate('due_date', '<', today())->sum('amount'),
        ];
    }

    public function getSettlementsProperty(): LengthAwarePaginator
    {
        return BnplSettlement::query()
            ->with(['order:id,status,total_amount,bnpl_gateway,bnpl_fee', 'seller:id,phone,instagram_username,full_name', 'wallet:id,seller_id,balance'])
            ->when($this->gateway !== 'all', fn ($q) => $q->where('gateway', $this->gateway))
            ->when(in_array($this->status, ['pending', 'paid', 'canceled'], true), fn ($q) => $q->where('status', $this->status))
            // v0.31.0 — «معوق»: pending AND past its due date (the cron of
            // manual-mode gateways will NEVER take these — admin decides).
            ->when($this->status === 'overdue', fn ($q) => $q
                ->where('status', 'pending')
                ->whereDate('due_date', '<', today()))
            ->when(trim($this->search) !== '', function ($q): void {
                $term = trim($this->search);

                $q->where(function ($inner) use ($term): void {
                    $inner->whereHas('seller', fn ($s) => $s
                        ->where('full_name', 'like', "%{$term}%")
                        ->orWhere('instagram_username', 'like', "%{$term}%")
                        ->orWhere('phone', 'like', "%{$term}%"));

                    if (ctype_digit($term)) {
                        // the displayed «code» is the zero-padded order id
                        $inner->orWhere('order_id', (int) $term);
                    }
                });
            })
            ->orderByRaw(DB::raw("CASE status WHEN 'pending' THEN 0 ELSE 1 END"))
            ->orderBy('due_date')
            ->orderByDesc('id')
            ->paginate(15);
    }

    public function render()
    {
        return view('livewire.admin.bnpl.settlements-page', [
            'settlements' => $this->settlements,
            'stats' => $this->stats,
            'bnplGateways' => PaymentService::BNPL_GATEWAYS,
            'statuses' => [
                'pending' => 'در انتظار تسویه',
                'overdue' => 'معوق (گذشته از سررسید)',
                'paid' => 'واریز شده',
                'canceled' => 'لغو شده',
            ],
            // v0.31.0 — gateways in MANUAL mode (the cron never touches
            // their rows → the table shows a «تسویهٔ دستی» hint chip).
            'manualGateways' => collect(PaymentService::BNPL_GATEWAYS)
                ->keys()
                ->filter(fn (string $g): bool => PaymentService::bnplSettlementMode($g) === 'manual')
                ->values()
                ->all(),
        ])->title('تسویه اقساطی')
            ->layout('layouts.admin-slot', ['breadcrumb' => 'مدیریت کل — تسویه اقساطی']);
    }
}
