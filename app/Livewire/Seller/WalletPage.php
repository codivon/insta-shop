<?php

namespace App\Livewire\Seller;

use App\Livewire\Concerns\NotifiesOnValidation;
use App\Models\BankCard;
use App\Models\BnplSettlement;
use App\Models\Shop;
use App\Models\Wallet;
use App\Models\WalletDeposit;
use App\Models\WalletTransaction;
use App\Support\ActivityLogger;
use App\Support\AdminNotifier;
use App\Support\Banking;
use App\Support\Fmt;
use App\Support\JDate;
use App\Support\Payments\PaymentService;
use App\Support\Wallet\WalletService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Seller — کیف پول (wallet tab):
 * balance card, income/withdraw stats, deposit-vs-withdrawal CHART
 * (daily/weekly/monthly/yearly + Jalali range), the transaction
 * ledger («گردش حساب») with a date filter + lazy loading + a
 * per-transaction details modal (store share / commission percent),
 * and a withdrawal-request modal (balance reserved immediately,
 * admin approves/rejects from the shops management page).
 */
#[Layout('layouts.seller-slot')]
class WalletPage extends Component
{
    use NotifiesOnValidation;

    public const MODES = ['daily', 'weekly', 'monthly', 'yearly'];

    public int $perPage = 10;

    public bool $hasMore = true;

    private int $lastLoadAt = 0;

    /* ── ledger date filter (گردش حساب) ── */
    public string $txFrom = '';

    public string $txTo = '';

    /* ── chart controls (نمودار واریز و برداشت) ── */
    public string $chartMode = 'daily';

    public string $chartFrom = '';

    public string $chartTo = '';

    /* ── withdraw modal ── */
    public bool $withdrawOpen = false;

    public string $withdrawAmount = '';

    /* ── deposit modal (v0.24.0 — واریز از درگاه پرداخت) ── */
    public bool $depositOpen = false;

    public string $depositAmount = '';

    public string $depositGateway = '';

    /* ── transaction details modal ── */
    public ?int $detailId = null;

    public function mount(): void
    {
        $today = CarbonImmutable::today();

        // default chart window: last 7 days
        $this->chartTo = $today->toDateString();
        $this->chartFrom = $today->subDays(6)->toDateString();
    }

    public function updating(string $property): void
    {
        // a fresh date filter must restart the lazy-loaded ledger
        if (in_array($property, ['txFrom', 'txTo'], true)) {
            $this->perPage = 10;
            $this->hasMore = true;
        }
    }

    public function setChartMode(string $mode): void
    {
        $this->chartMode = $this->normalizeMode($mode);

        [$from, $to] = $this->defaultWindow($this->chartMode);
        $this->chartFrom = $from;
        $this->chartTo = $to;
    }

    /** Clear the «گردش حساب» date filter. */
    public function clearTxFilter(): void
    {
        $this->txFrom = '';
        $this->txTo = '';
        $this->perPage = 10;
        $this->hasMore = true;
    }

    public function loadMore(): void
    {
        $now = (int) (microtime(true) * 1000);

        if ($now - $this->lastLoadAt < 400) {
            return;
        }

        $this->lastLoadAt = $now;
        $this->perPage += 10;
    }

    /* ───────────────────────── withdraw modal ───────────────────────── */

    /** The seller's shop (defensively bootstrapped, same as render). */
    protected function shop(): ?Shop
    {
        $seller = auth('seller')->user();

        return $seller ? Shop::ensureForSeller($seller) : null;
    }

    /** The withdrawal destination — the seller's DEFAULT bank card (null when none). */
    protected function defaultCard(): ?BankCard
    {
        return BankCard::query()
            ->where('seller_id', auth('seller')->id())
            ->orderByDesc('is_default')
            ->orderBy('id')
            ->first();
    }

    public function openWithdraw(): void
    {
        $this->resetErrorBag();
        $this->withdrawAmount = '';
        $this->withdrawOpen = true;
    }

    public function closeWithdraw(): void
    {
        $this->withdrawOpen = false;
        $this->withdrawAmount = '';
        $this->resetErrorBag();
    }

    public function updatedWithdrawAmount(mixed $value): void
    {
        $this->withdrawAmount = Fmt::toLatin((string) $value);
    }

    /* ───────────────────────── deposit modal (واریز از درگاه) ───────────────────────── */

    public function openDeposit(): void
    {
        $this->resetErrorBag();
        $this->depositAmount = '';
        $this->depositGateway = array_key_first(PaymentService::availableGateways()) ?? '';
        $this->depositOpen = true;
    }

    public function closeDeposit(): void
    {
        $this->depositOpen = false;
        $this->depositAmount = '';
        $this->resetErrorBag();
    }

    public function updatedDepositAmount(mixed $value): void
    {
        $this->depositAmount = Fmt::toLatin((string) $value);
    }

    /**
     * Create the PENDING deposit row and hand the browser to the gateway
     * (WalletDepositGo renders the redirection form). The wallet is
     * credited ONLY after the gateway callback verifies.
     */
    public function submitDeposit()
    {
        $gateways = PaymentService::availableGateways();

        $this->validateWithToast(fn () => $this->validate([
            'depositAmount' => ['required', 'integer', 'min:10000', 'max:999999999'],
            'depositGateway' => ['required', 'in:'.implode(',', array_keys($gateways))],
        ], [
            'depositAmount.required' => 'مبلغ واریز را وارد کنید.',
            'depositAmount.integer' => 'مبلغ واریز باید عدد باشد.',
            'depositAmount.min' => 'حداقل مبلغ واریز ۱۰٬۰۰۰ تومان است.',
            'depositAmount.max' => 'مبلغ واریز بیش از حد مجاز است.',
            'depositGateway.required' => 'درگاه پرداخت را انتخاب کنید.',
            'depositGateway.in' => 'درگاه انتخابی در دسترس نیست.',
        ]));

        $seller = auth('seller')->user();
        $wallet = $seller ? Wallet::ensureForSeller($seller->id) : null;

        if (! $wallet) {
            $this->addError('depositAmount', 'ابتدا وارد حساب فروشنده شوید.');

            return;
        }

        $deposit = WalletDeposit::query()->create([
            'seller_id' => $seller->id,
            'wallet_id' => $wallet->id,
            'amount' => (int) $this->depositAmount,
            'gateway' => $this->depositGateway,
            'status' => WalletDeposit::STATUS_PENDING,
        ]);

        ActivityLogger::log(
            'wallet.deposit.requested',
            'فروشنده @'.($seller->instagram_username ?: $seller->phone).' برای شارژ '.Fmt::money($deposit->amount).' تومان کیف پول، درگاه '.$gateways[$deposit->gateway].' را انتخاب کرد',
            subject: $deposit,
            after: ['amount' => (int) $deposit->amount, 'gateway' => $deposit->gateway],
        );

        // full-page hand-off to the gateway (renders the redirection form)
        return $this->redirect(route('seller.wallet.deposit.go', $deposit), navigate: false);
    }

    public function submitWithdraw(): void
    {
        $wallet = $this->wallet();

        // the destination is ALWAYS the seller's default bank card — a
        // seller without any card cannot request a withdrawal (the submit
        // button is disabled client-side; this is the server-side net)
        $card = $this->defaultCard();

        if (! $card || (string) $card->card_number === '') {
            $this->addError('withdrawCard', 'هنوز کارت بانکی ثبت نکرده‌اید؛ اول از صفحهٔ «کارت‌های بانکی» کارت خود را وارد کنید، بعد درخواست بدهید.');

            return;
        }

        $this->validateWithToast(fn () => $this->validate([
            'withdrawAmount' => ['required', 'numeric', 'min:50000', 'max:'.$wallet->balance],
        ], [
            'withdrawAmount.required' => 'مبلغ برداشت را وارد کنید.',
            'withdrawAmount.min' => 'حداقل مبلغ برداشت ۵۰٬۰۰۰ تومان است.',
            'withdrawAmount.max' => 'مبلغ درخواستی بیشتر از موجودی قابل برداشت است.',
        ]));

        $amount = (int) $this->withdrawAmount;

        $tx = WalletService::requestWithdrawal($wallet, $amount, (string) $card->sheba, (string) $card->card_number);

        if ($tx === null) {
            $this->addError('withdrawAmount', 'موجودی کافی نیست.');

            return;
        }

        ActivityLogger::log(
            'wallet.withdraw',
            'درخواست برداشت '.Fmt::money($amount).' از کیف پول فروشگاه '.($this->shop()?->name ?: '—').' ثبت شد — مقصد: '.Banking::maskCard($card->card_number),
            subject: $tx,
            after: ['amount' => $amount, 'card' => '••••'.mb_substr((string) $card->card_number, -4)],
        );

        AdminNotifier::notify(
            'wallet.withdraw',
            'درخواست برداشت 💰 '.Fmt::money($amount),
            'فروشگاه '.($this->shop()?->name ?: '—').' درخواست برداشت به کارت '.Banking::maskCard($card->card_number).' ثبت کرد — نیاز به تایید شما.',
            route('admin.withdrawals'),
            'wallet',
            $tx,
        );

        $this->withdrawOpen = false;
        $this->withdrawAmount = '';

        session()->flash('wallet-flash', 'درخواست برداشت ثبت شد؛ پس از تأیید مدیر، مبلغ به کارت بانکی شما واریز می‌شود.');
    }

    /* ───────────────── transaction details modal ───────────────── */

    public function openTxDetails(int $id): void
    {
        $tx = WalletTransaction::query()->find($id);

        // ownership: the transaction must belong to THIS seller's wallet
        if ($tx && (int) $tx->wallet_id === (int) $this->wallet()->id) {
            $this->detailId = $id;
        }
    }

    public function closeTxDetails(): void
    {
        $this->detailId = null;
    }

    public function getTxDetailProperty(): ?WalletTransaction
    {
        if ($this->detailId === null) {
            return null;
        }

        return WalletTransaction::query()
            ->where('wallet_id', $this->wallet()->id)
            ->with('order:id,status')
            ->find($this->detailId);
    }

    /** Gross goods total of a deposit (net payout + kept commission). */
    public function txItemsTotal(WalletTransaction $tx): int
    {
        return (int) $tx->amount + (int) $tx->commission;
    }

    /** Commission percent of a deposit, rounded to a whole «٪». */
    public function txCommissionPercent(WalletTransaction $tx): int
    {
        $itemsTotal = $this->txItemsTotal($tx);

        return $itemsTotal > 0 ? (int) round($tx->commission * 100 / $itemsTotal) : 0;
    }

    /* ───────────────────────── data ───────────────────────── */

    protected function wallet(): Wallet
    {
        return once(function () {
            $seller = auth('seller')->user();

            return Wallet::ensureForSeller($seller->id);
        });
    }

    /** Pending withdrawal total (already deducted from balance). */
    protected function pendingWithdrawals(): int
    {
        return (int) WalletTransaction::query()
            ->where('wallet_id', $this->wallet()->id)
            ->where('type', WalletTransaction::TYPE_WITHDRAWAL)
            ->where('status', WalletTransaction::STATUS_PENDING)
            ->sum('amount');
    }

    /** Parse + sanitize the ledger date filter into [startOfDay, endOfDay]. */
    private function resolveTxWindow(): array
    {
        try {
            $f = $this->txFrom !== '' ? CarbonImmutable::parse($this->txFrom)->startOfDay() : null;
        } catch (\Throwable) {
            $f = null;
        }

        try {
            $t = $this->txTo !== '' ? CarbonImmutable::parse($this->txTo)->endOfDay() : null;
        } catch (\Throwable) {
            $t = null;
        }

        if ($f && $t && $f->gt($t)) {
            [$f, $t] = [$t->startOfDay(), $f->endOfDay()];
        }

        return [$f, $t];
    }

    /* ───────────────── deposit/withdraw chart machinery ───────────────── */

    private function normalizeMode(string $mode): string
    {
        return in_array($mode, self::MODES, true) ? $mode : 'daily';
    }

    /** Sensible default window per granularity. */
    private function defaultWindow(string $mode): array
    {
        $today = CarbonImmutable::today();

        return match ($mode) {
            'weekly' => [$today->subWeeks(11)->startOfWeek(Carbon::SATURDAY)->toDateString(), $today->toDateString()],
            'monthly' => [$today->subMonths(11)->startOfMonth()->toDateString(), $today->toDateString()],
            'yearly' => [$today->subYears(4)->startOfYear()->toDateString(), $today->toDateString()],
            default => [$today->subDays(6)->toDateString(), $today->toDateString()],
        };
    }

    /** Parse + sanitize the chart range and clamp it per mode caps. */
    private function resolveChartRange(string $from, string $to, string $mode): array
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
            'daily' => $f->max($today->subDays(91)->startOfDay()),
            'weekly' => $f->max($today->subDays(364)->startOfDay()),
            'monthly' => $f->max($today->subYears(3)->startOfDay()),
            default => $f->max($today->subYears(9)->startOfDay()),
        };

        return [$f, $t->min($today->endOfDay())];
    }

    /** Which bucket does this timestamp belong to for the given mode? */
    private function bucketKey(string $mode, CarbonImmutable $date): string
    {
        return match ($mode) {
            'weekly' => $date->startOfWeek(Carbon::SATURDAY)->toDateString(),
            'monthly' => JDate::monthKey($date),
            'yearly' => JDate::yearKey($date),
            default => $date->toDateString(),
        };
    }

    /** Labeled, zero-filled two-series buckets covering the range for the given mode. */
    private function periodBuckets(string $mode, CarbonImmutable $from, CarbonImmutable $to): array
    {
        $buckets = [];
        $today = CarbonImmutable::today()->toDateString();

        if ($mode === 'daily') {
            $cursor = $from->startOfDay();
            $steps = min((int) $from->diffInDays($to->endOfDay()) + 1, 92);

            for ($i = 0; $i < $steps; $i++) {
                $key = $cursor->toDateString();

                $buckets[$key] = [
                    'label' => $cursor->locale('fa')->minDayName.' '.Fmt::fa($cursor->day),
                    'sort' => $key,
                    'deposit' => 0,
                    'withdrawal' => 0,
                    'today' => $key === $today,
                ];
                $cursor = $cursor->addDay();
            }
        } elseif ($mode === 'weekly') {
            $cursor = $from->startOfWeek(Carbon::SATURDAY)->startOfDay();
            $steps = min((int) $cursor->diffInWeeks($to->endOfDay()) + 1, 52);

            for ($i = 0; $i < $steps; $i++) {
                $key = $cursor->toDateString();

                $buckets[$key] = [
                    'label' => JDate::short($cursor),
                    'sort' => $key,
                    'deposit' => 0,
                    'withdrawal' => 0,
                    'today' => $key === $this->bucketKey('weekly', CarbonImmutable::today()),
                ];
                $cursor = $cursor->addWeeks(1);
            }
        } elseif ($mode === 'monthly') {
            $buckets = JDate::monthBuckets($from, $to);
        } else {
            $buckets = JDate::yearBuckets($from, $to);
        }

        return $buckets;
    }

    /** Two-series chart: deposits vs withdrawals over the mode+range (60 s cache). */
    private function txChart(int $walletId, string $mode, CarbonImmutable $from, CarbonImmutable $to): array
    {
        return Cache::remember(
            'ip:wallet:tx-chart:v1:'.$walletId.':'.$mode.':'.$from->format('YmdHi').':'.$to->format('YmdHi'),
            60,
            function () use ($walletId, $mode, $from, $to) {
                $buckets = $this->periodBuckets($mode, $from, $to);

                WalletTransaction::query()
                    ->where('wallet_id', $walletId)
                    ->whereBetween('created_at', [$from, $to])
                    ->get(['type', 'status', 'amount', 'created_at'])
                    ->each(function (WalletTransaction $tx) use (&$buckets, $mode) {
                        $key = $this->bucketKey($mode, CarbonImmutable::parse($tx->created_at));

                        if (! isset($buckets[$key])) {
                            return;
                        }

                        if ($tx->type === WalletTransaction::TYPE_DEPOSIT) {
                            $buckets[$key]['deposit'] = ($buckets[$key]['deposit'] ?? 0) + (int) $tx->amount;
                        } elseif ($tx->status !== WalletTransaction::STATUS_REJECTED) {
                            // pending + done withdrawals: money actually leaving the wallet
                            $buckets[$key]['withdrawal'] = ($buckets[$key]['withdrawal'] ?? 0) + (int) $tx->amount;
                        }
                    });

                $series = collect($buckets)
                    ->sortBy('sort')
                    ->map(fn ($b) => [
                        'label' => $b['label'],
                        'deposit' => (int) ($b['deposit'] ?? 0),
                        'withdrawal' => (int) ($b['withdrawal'] ?? 0),
                        'today' => $b['today'],
                    ])
                    ->values();

                // plain array — the cache unserializes with allowed_classes=false,
                // so Collections/objects do NOT survive a cache round-trip
                return [
                    'series' => $series->all(),
                    'depositTotal' => (int) $series->sum('deposit'),
                    'withdrawalTotal' => (int) $series->sum('withdrawal'),
                    'max' => max($series->max(fn ($b) => max($b['deposit'], $b['withdrawal'])) ?? 1, 1),
                ];
            },
        );
    }

    public function render()
    {
        Shop::ensureForSeller(auth('seller')->user()); // defensive shop bootstrap

        $wallet = $this->wallet();
        $txQuery = WalletTransaction::query()->where('wallet_id', $wallet->id);

        // all-time stats stay stable regardless of the ledger date filter
        $stats = [
            'income' => (int) (clone $txQuery)->where('type', WalletTransaction::TYPE_DEPOSIT)->sum('amount'),
            'withdrawn' => (int) (clone $txQuery)->where('type', WalletTransaction::TYPE_WITHDRAWAL)->where('status', WalletTransaction::STATUS_DONE)->sum('amount'),
            'pending' => $this->pendingWithdrawals(),
        ];

        // «گردش حساب» date filter (optional from/to)
        if ($this->txFrom !== '' || $this->txTo !== '') {
            [$f, $t] = $this->resolveTxWindow();
            $txQuery->when($f !== null, fn ($q) => $q->where('created_at', '>=', $f))
                ->when($t !== null, fn ($q) => $q->where('created_at', '<=', $t));
        }

        // newest first — the latest transaction must surface immediately
        $txQuery->orderByDesc('id');

        $txTotal = (clone $txQuery)->count();
        $transactions = (clone $txQuery)
            ->with('order')
            ->limit(min(max($this->perPage, 1), 60))
            ->get();

        $txHasMore = $transactions->count() >= min(max($this->perPage, 1), 60);

        [$chartFrom, $chartTo] = $this->resolveChartRange($this->chartFrom, $this->chartTo, $this->chartMode);

        return view('livewire.seller.wallet-page', [
            'wallet' => $wallet,
            'stats' => $stats,
            'transactions' => $transactions,
            'txHasMore' => $txHasMore,
            'txTotal' => $txTotal,
            'txChart' => $this->txChart($wallet->id, $this->chartMode, $chartFrom, $chartTo),
            'txDetail' => $this->txDetail,
            'depositGateways' => PaymentService::availableGateways(),
            'pendingDeposits' => WalletDeposit::query()
                ->where('wallet_id', $wallet->id)
                ->where('status', WalletDeposit::STATUS_PENDING)
                ->orderByDesc('id')
                ->limit(3)
                ->get(),
            'cards' => BankCard::query()
                ->where('seller_id', auth('seller')->id())
                ->orderByDesc('is_default')
                ->orderBy('id')
                ->get(),
            // v0.30.0 — تسویه‌های اقساطی در انتظار واریز (دیجی‌پی/اسنپ‌پی/ترب‌پی):
            // the money is NOT in the wallet yet — these rows render faded
            // with the «بعد از تسویه {درگاه} به کیف پول شما واریز می‌شود»
            // promise until the daily cron / admin deposit pays them out.
            'bnplPending' => BnplSettlement::query()
                ->where('seller_id', auth('seller')->id())
                ->where('status', 'pending')
                ->with('order:id,status,total_amount')
                ->orderBy('due_date')
                ->limit(20)
                ->get(),
        ])->title('کیف پول');
    }
}
