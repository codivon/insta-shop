<?php

namespace App\Livewire\Admin;

use App\Livewire\Admin\Concerns\GuardsPermissions;
use App\Livewire\Concerns\NotifiesOnValidation;
use App\Models\WalletTransaction;
use App\Support\ActivityLogger;
use App\Support\Banking;
use App\Support\Fmt;
use App\Support\JDate;
use App\Support\Wallet\WalletService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Admin — «مدیریت برداشت‌ها»: every seller withdrawal request with
 * approve / reject (reject refunds the reserved balance back to the
 * seller wallet), status + Jalali date filters and headline cards.
 */
#[Layout('layouts.admin-slot')]
#[Title('مدیریت برداشت‌ها')]
class WithdrawalsPage extends Component
{
    use GuardsPermissions;
    use NotifiesOnValidation;

    protected static string $permission = 'admin.finance';

    public string $statusFilter = 'pending'; // pending | done | rejected | all

    /** Jalali «YYYY/MM/DD» inputs. */
    public string $fromDate = '';

    public string $toDate = '';

    public int $perPage = 20;

    public bool $hasMore = true;

    private int $lastLoadAt = 0;

    /* ── confirm modal ── */
    public ?int $confirmId = null;

    public string $confirmAction = ''; // approve | reject

    /** Admin note (توضیحات) — mandatory-ish helper text sent to the seller. */
    public string $confirmNote = '';

    /**
     * One-tap preset notes per action — clicking a chip drops the text into
     * the note box (the seller reads it verbatim in their wallet page).
     */
    public const NOTE_PRESETS = [
        'approve' => [
            'مبلغ به کارت بانکی شما واریز شد. ✅',
            'واریز انجام شد؛ در صورت تأخیر بانکی حداکثر تا ۷۲ ساعت صبر کنید.',
            'واریز از طرف حساب مرکزی انجام شد — رسید نزد پشتیبانی موجود است.',
            'تسویهٔ دورهٔ قبل شما پرداخت شد؛ درخواست‌های بعدی طبق روال بررسی می‌شود.',
        ],
        'reject' => [
            'شماره کارت و شبا صحیح نمی‌باشد؛ لطفاً کارت را اصلاح و دوباره درخواست بدهید.',
            'اطلاعات حساب با نام صاحب حساب هم‌خوانی ندارد؛ کارت به نام خودتان باشد.',
            'کارت بانکی مسدود یا غیرفعال است؛ لطفاً کارت دیگری ثبت کنید.',
            'خطای شبکهٔ بانکی رخ داد — لطفاً بعداً دوباره درخواست بدهید.',
            'درخواست شما پس از بررسی رد شد؛ برای اطلاعات بیشتر با پشتیبانی تماس بگیرید.',
        ],
    ];

    public function updating(string $property): void
    {
        if (in_array($property, ['statusFilter', 'fromDate', 'toDate'], true)) {
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

    protected function query()
    {
        [$from, $to] = $this->range();

        return WalletTransaction::query()
            ->where('type', 'withdrawal')
            ->when($this->statusFilter !== 'all', fn ($q) => $q->where('status', $this->statusFilter))
            ->when($from, fn ($q) => $q->where('created_at', '>=', $from))
            ->when($to, fn ($q) => $q->where('created_at', '<=', $to))
            ->with(['wallet.seller:id,full_name'])
            ->latest('id');
    }

    public function getRowsProperty()
    {
        $rows = $this->query()->limit($this->perPage)->get();
        $total = (clone $this->query())->count();

        $this->hasMore = $total > $this->perPage;

        return $rows;
    }

    public function getTotalProperty(): int
    {
        return (clone $this->query())->count();
    }

    public function getMetricsProperty(): array
    {
        $base = WalletTransaction::query()->where('type', 'withdrawal')
            ->when($from = $this->range()[0], fn ($q) => $q->where('created_at', '>=', $from))
            ->when($to = $this->range()[1], fn ($q) => $q->where('created_at', '<=', $to));

        $pending = (clone $base)->where('status', 'pending');
        $done = (clone $base)->where('status', 'done');
        $rej = (clone $base)->where('status', 'rejected');

        return [
            'pending_sum' => (int) $pending->sum('amount'),
            'pending_count' => (int) $pending->count(),
            'done_sum' => (int) $done->sum('amount'),
            'rejected_sum' => (int) $rej->sum('amount'),
            'rejected_count' => (int) $rej->count(),
        ];
    }

    /* ── confirm flow ── */

    public function askApprove(int $id): void
    {
        $this->confirmId = $id;
        $this->confirmAction = 'approve';
        $this->confirmNote = '';
    }

    public function askReject(int $id): void
    {
        $this->confirmId = $id;
        $this->confirmAction = 'reject';
        $this->confirmNote = '';
    }

    public function cancelConfirm(): void
    {
        $this->confirmId = null;
        $this->confirmAction = '';
        $this->confirmNote = '';
    }

    /** Drop a preset note into the confirm modal's note box (by index). */
    public function useNotePreset(int $index): void
    {
        $this->confirmNote = self::NOTE_PRESETS[$this->confirmAction][$index] ?? '';
    }

    public function execute(): void
    {
        if ($this->confirmId === null) {
            return;
        }

        $tx = WalletTransaction::query()->with('wallet')->find($this->confirmId);
        $mode = $this->confirmAction; // capture BEFORE the confirm state resets
        $note = $this->confirmNote;   // same — cancelConfirm() wipes it
        $this->cancelConfirm();

        if (! $tx) {
            return;
        }

        if ($mode === 'approve') {
            $ok = WalletService::approveWithdrawal($tx, $note);

            ActivityLogger::log(
                'wallet.withdraw',
                'درخواست برداشت '.Fmt::money($tx->amount).' تأیید شد — مقصد: '.($tx->card_number ? 'کارت '.Banking::maskCard($tx->card_number) : ($tx->sheba ?: '—')).($note !== '' ? ' | توضیح: '.$note : ''),
                subject: $tx,
                after: ['status' => 'done', 'amount' => $tx->amount],
            );

            $this->dispatch('panel-toast', message: $ok ? 'برداشت تأیید شد و به فروشنده اطلاع داده شد. ✅' : 'این درخواست قبلاً پردازش شده است.', type: $ok ? 'success' : 'error');

            return;
        }

        $ok = WalletService::rejectWithdrawal($tx, $note);

        ActivityLogger::log(
            'wallet.withdraw',
            'درخواست برداشت '.Fmt::money($tx->amount).' رد شد — مبلغ به کیف پول بازگشت'.($note !== '' ? ' | توضیح: '.$note : ''),
            subject: $tx,
            after: ['status' => 'rejected', 'amount' => $tx->amount],
        );

        $this->dispatch('panel-toast', message: $ok ? 'درخواست رد شد و مبلغ به کیف پول فروشنده بازگشت.' : 'این درخواست قبلاً پردازش شده است.', type: $ok ? 'success' : 'error');
    }

    public function render()
    {
        return view('livewire.admin.withdrawals-page', [
            'rows' => $this->rows,
            'total' => $this->total,
            'metrics' => $this->metrics,
        ])->title('مدیریت برداشت‌ها')
            ->layout('layouts.admin-slot', ['breadcrumb' => 'مدیریت کل — مالی و حسابداری — مدیریت برداشت‌ها']);
    }
}
