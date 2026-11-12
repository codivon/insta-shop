<?php

namespace App\Support\Payments;

use App\Models\WalletDeposit;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use Shetabit\Multipay\Invoice;
use Shetabit\Multipay\Receipt;
use Shetabit\Multipay\RedirectionForm;
use Shetabit\Payment\Facade\Payment;
use Throwable;

/**
 * Wallet-deposit payment engine (v0.24.0) — the twin of PaymentService,
 * operating on WalletDeposit instead of Order:
 *
 *   The SELLER types an amount on «کیف پول» + picks a gateway
 *   (PaymentService::availableGateways), a pending WalletDeposit row is
 *   created and the browser is handed to the gateway via
 *   WalletDepositGo → purchase(). The PUBLIC callback
 *   (seller.wallet.deposit.callback → WalletDepositCallback) verifies
 *   with the SAME cryptographic verification as orders and ONLY THEN
 *   credits the wallet (TYPE_DEPOSIT transaction) — a forged/guessed
 *   callback never touches money.
 *
 * SECURITY (mirrors PaymentService):
 *  • per-deposit HMAC token in the callback URL (hash prefix
 *    «deposit-callback:» — never the order token);
 *  • the local TEST gateway is fail-closed outside local environments;
 *  • Zibal verify amount-mismatch check (paidAmount in RIAL) identical
 *    to the order path;
 *  • credit path is idempotent: only a PENDING row can be finalized.
 */
class WalletDepositService
{
    public static function callbackToken(WalletDeposit $deposit): string
    {
        return hash_hmac('sha256', 'deposit-callback:'.$deposit->id, (string) config('app.key'));
    }

    public static function callbackTokenValid(WalletDeposit $deposit, string $token): bool
    {
        return $token !== ''
            && hash_equals(self::callbackToken($deposit), $token);
    }

    public static function callbackUrl(WalletDeposit $deposit): string
    {
        return route('seller.wallet.deposit.callback', ['d' => $deposit->id, 't' => self::callbackToken($deposit)]);
    }

    /**
     * Purchase the deposit through the chosen gateway; returns the
     * redirection form. $deposit->gateway must be an AVAILABLE gateway
     * (validated at submit time; re-checked here).
     *
     * @throws Throwable when the gateway refuses the purchase
     */
    public static function purchase(WalletDeposit $deposit): RedirectionForm
    {
        $gateway = array_key_exists($deposit->gateway, PaymentService::GATEWAYS)
            ? $deposit->gateway
            : PaymentService::activeGateway();

        // Fail CLOSED: outside local, the test gateway never touches money.
        if ($gateway === 'local' && ! PaymentService::localGatewayAllowed()) {
            $deposit->forceFill([
                'status' => WalletDeposit::STATUS_FAILED,
                'message' => 'درگاه آزمایشی روی سرور عملیاتی غیرفعال است',
            ])->save();

            throw new \RuntimeException('درگاه آزمایشی روی سرور عملیاتی غیرفعال است — از پنل مدیریت یک درگاه واقعی تنظیم کنید.');
        }

        $invoice = new Invoice;
        $invoice->amount(max(1, (int) $deposit->amount));
        $invoice->detail('orderId', 'DEP-'.$deposit->id);

        // Credentials ride config('payment') — see PaymentService::
        // applyRuntimeConfig's docblock (the old Payment::config() approach
        // was silently dropped by shetabit's via()). Deposits are
        // platform-bound money — never split.
        PaymentService::applyRuntimeConfig($gateway);

        $manager = Payment::via($gateway)
            ->callbackUrl(self::callbackUrl($deposit));

        try {
            $manager->purchase($invoice, function ($driver, string $transactionId) use ($deposit, $gateway): void {
                $deposit->forceFill(['gateway' => $gateway, 'gateway_ref' => $transactionId])->save();
            });
        } catch (Throwable $e) {
            $deposit->forceFill(['message' => mb_substr($e->getMessage(), 0, 190)])->save();

            throw $e;
        }

        $deposit->refresh();

        $form = $manager->pay();

        // The local TEST gateway gets the project's own modern sandbox
        // page; real banks keep the vendor's default view (same rule as
        // PaymentService::purchase).
        if ($gateway === 'local') {
            RedirectionForm::setViewPath(resource_path('views/vendor/multipay/local-form.php'));
        } else {
            RedirectionForm::setViewPath(RedirectionForm::getDefaultViewPath());
        }

        return $form;
    }

    /**
     * Verify the gateway callback against the deposit (amount-mismatch
     * + cryptography identical to PaymentService::verify).
     *
     * @throws Throwable on failure (canceled / invalid / previously verified…)
     */
    public static function verify(WalletDeposit $deposit): Receipt
    {
        $gateway = $deposit->gateway ?: PaymentService::activeGateway();

        /*
         * Success-path hardening for the LOCAL TEST gateway — the local
         * driver POSTs to a URL whose QUERY string carries the
         * transactionId, and proxies/redirects can lose it; the stored
         * reference is restored here (local environments only — the same
         * rule as orders; production gateways verify via their own API).
         */
        if ($gateway === 'local'
            && PaymentService::localGatewayAllowed()
            && (string) $deposit->gateway_ref !== ''
            && ! request()->filled('transactionId')
            && ! request()->boolean('cancel')) {
            request()->merge(['transactionId' => (string) $deposit->gateway_ref]);
        }

        PaymentService::applyRuntimeConfig($gateway);

        try {
            $receipt = Payment::via($gateway)
                ->amount($deposit->amount)
                ->transactionId((string) $deposit->gateway_ref)
                ->verify();
        } catch (Throwable $e) {
            throw $e;
        }

        /*
         * SECURITY: the paid amount is compared locally for the drivers
         * that do NOT re-send the amount at verify time (identical to the
         * order path — Zibal echoes the bound amount/paidAmount in RIAL;
         * a mismatch below the deposit total fails closed).
         */
        if ($gateway === 'zibal') {
            $details = (array) $receipt->getDetails();
            $paidRial = (int) ($details['paidAmount'] ?? $details['amount'] ?? 0);

            if ($paidRial > 0 && (int) round($paidRial / 10) < (int) $deposit->amount) {
                throw new \RuntimeException('مبلغ پرداخت‌شده با مبلغ واریز مطابقت ندارد — تراکنش تأیید نشد.');
            }
        }

        return $receipt;
    }

    /**
     * THE MONEY MOMENT — credit the seller's wallet. Idempotent by
     * status: only a PENDING deposit reaches here; the row is locked so
     * a racing retried callback can never double-credit.
     */
    public static function finalizePaid(WalletDeposit $deposit, string $reference): WalletDeposit
    {
        return DB::transaction(function () use ($deposit, $reference) {
            $fresh = WalletDeposit::query()->whereKey($deposit->id)->lockForUpdate()->first();

            if ($fresh === null || $fresh->status !== WalletDeposit::STATUS_PENDING) {
                return $fresh; // already settled by a racing callback
            }

            $wallet = $fresh->wallet()->lockForUpdate()->first();
            $wallet?->increment('balance', (int) $fresh->amount);

            WalletTransaction::query()->create([
                'wallet_id' => $fresh->wallet_id,
                'type' => WalletTransaction::TYPE_DEPOSIT,
                'amount' => (int) $fresh->amount,
                'commission' => 0,
                'status' => WalletTransaction::STATUS_DONE,
                'description' => 'واریز به کیف پول از درگاه ('.$fresh->gateway.') — کد رهگیری '.mb_substr($reference, 0, 60),
            ]);

            $fresh->forceFill([
                'status' => WalletDeposit::STATUS_DONE,
                'message' => null,
                'meta' => ['reference' => mb_substr($reference, 0, 120)],
            ])->save();

            return $fresh;
        });
    }

    /** Mark a failed/canceled deposit (never credits anything). */
    public static function markFailed(WalletDeposit $deposit, ?string $message = null, bool $canceled = false): void
    {
        if ($deposit->status !== WalletDeposit::STATUS_PENDING) {
            return;
        }

        $deposit->forceFill([
            'status' => $canceled ? WalletDeposit::STATUS_CANCELED : WalletDeposit::STATUS_FAILED,
            'message' => $message !== null ? mb_substr($message, 0, 190) : null,
        ])->save();
    }
}
