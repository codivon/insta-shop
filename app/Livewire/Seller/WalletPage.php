<?php

namespace App\Livewire\Seller;

use App\Models\Shop;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Support\Fmt;
use App\Support\Wallet\WalletService;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Seller — کیف پول (wallet tab):
 * balance card, income/withdraw stats, transaction ledger with lazy
 * loading and a withdrawal-request modal (balance reserved immediately,
 * admin approves/rejects from the shops management page).
 */
#[Layout('layouts.seller-slot')]
class WalletPage extends Component
{
    public int $perPage = 10;

    public bool $hasMore = true;

    private int $lastLoadAt = 0;

    /* ── withdraw modal ── */
    public bool $withdrawOpen = false;

    public string $withdrawAmount = '';

    public string $withdrawSheba = '';

    public function loadMore(): void
    {
        $now = (int) (microtime(true) * 1000);

        if ($now - $this->lastLoadAt < 400) {
            return;
        }

        $this->lastLoadAt = $now;
        $this->perPage    += 10;
    }

    /* ───────────────────────── modal ───────────────────────── */

    public function openWithdraw(): void
    {
        $this->resetErrorBag();
        $this->withdrawAmount = '';
        $this->withdrawSheba  = '';
        $this->withdrawOpen   = true;
    }

    public function closeWithdraw(): void
    {
        $this->withdrawOpen   = false;
        $this->withdrawAmount = '';
        $this->withdrawSheba  = '';
        $this->resetErrorBag();
    }

    public function updatedWithdrawAmount(mixed $value): void
    {
        $this->withdrawAmount = Fmt::toLatin((string) $value);
    }

    public function updatedWithdrawSheba(mixed $value): void
    {
        // Persian/Arabic digits → Latin, KEEP the IR letters, drop spaces
        $v = strtr((string) $value, [
            '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
            '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
            '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
        ]);

        $this->withdrawSheba = strtoupper(preg_replace('/\s+/u', '', $v) ?? '');
    }

    public function submitWithdraw(): void
    {
        $wallet = $this->wallet();

        $this->validate([
            'withdrawAmount' => ['required', 'numeric', 'min:50000', 'max:'.$wallet->balance],
            'withdrawSheba'  => ['required', 'string', 'regex:/^IR\d{24}$/'],
        ], [
            'withdrawAmount.required' => 'مبلغ برداشت را وارد کنید.',
            'withdrawAmount.min'      => 'حداقل مبلغ برداشت ۵۰٬۰۰۰ تومان است.',
            'withdrawAmount.max'      => 'مبلغ درخواستی بیشتر از موجودی قابل برداشت است.',
            'withdrawSheba.required'  => 'شماره شبا را وارد کنید.',
            'withdrawSheba.regex'     => 'شماره شبا باید با IR شروع شود و ۲۴ رقم بعد از آن باشد (مثال: IR062980000000123456789001).',
        ]);

        $amount = (int) $this->withdrawAmount;

        $tx = WalletService::requestWithdrawal($wallet, $amount, $this->withdrawSheba);

        if ($tx === null) {
            $this->addError('withdrawAmount', 'موجودی کافی نیست.');

            return;
        }

        $this->withdrawOpen   = false;
        $this->withdrawAmount = '';
        $this->withdrawSheba  = '';

        session()->flash('wallet-flash', 'درخواست برداشت ثبت شد؛ پس از تأیید مدیر، مبلغ به شبا واریز می‌شود.');
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

    public function render()
    {
        Shop::ensureForSeller(auth('seller')->user()); // defensive shop bootstrap

        $wallet   = $this->wallet();
        $txQuery  = WalletTransaction::query()->where('wallet_id', $wallet->id);

        $stats = [
            'income'    => (int) (clone $txQuery)->where('type', WalletTransaction::TYPE_DEPOSIT)->sum('amount'),
            'withdrawn' => (int) (clone $txQuery)->where('type', WalletTransaction::TYPE_WITHDRAWAL)->where('status', WalletTransaction::STATUS_DONE)->sum('amount'),
            'pending'   => $this->pendingWithdrawals(),
        ];

        $transactions = (clone $txQuery)
            ->with('order')
            ->limit($this->perPage)
            ->get();

        $txHasMore = $transactions->count() >= $this->perPage;

        return view('livewire.seller.wallet-page', [
            'wallet'       => $wallet,
            'stats'        => $stats,
            'transactions' => $transactions,
            'txHasMore'    => $txHasMore,
        ])->title('کیف پول');
    }
}
