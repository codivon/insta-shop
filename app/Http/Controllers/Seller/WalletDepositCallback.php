<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\WalletDeposit;
use App\Support\ActivityLogger;
use App\Support\AdminNotifier;
use App\Support\Fmt;
use App\Support\Payments\WalletDepositService;
use Illuminate\Http\Request;
use Shetabit\Multipay\Exceptions\PreviouslyVerifiedException;
use Throwable;

/**
 * Gateway callback for WALLET DEPOSITS (v0.24.0) — public on purpose
 * (banks redirect browsers here), mirroring Shop\PaymentCallback:
 *
 *  • the deposit is identified by the `d` query param + its stored
 *    gateway reference and is protected by a per-deposit HMAC token
 *    (`t`) — a guessed URL is dropped BEFORE any verify()/credit;
 *  • only a PENDING deposit can be finalized (idempotent credit — a
 *    retried callback re-enters the success path without double-crediting);
 *  • PreviouslyVerifiedException = the money WAS taken → success path.
 */
class WalletDepositCallback extends Controller
{
    public function __invoke(Request $request)
    {
        $deposit = WalletDeposit::query()->find((int) $request->input('d', 0));

        if (! $deposit) {
            return redirect()->route('landing');
        }

        /*
         * SECURITY: this endpoint is public — the per-deposit HMAC token
         * is the ONLY acceptable proof. Every tokenless/guessed callback
         * is rejected before any money can move.
         */
        if (! WalletDepositService::callbackTokenValid($deposit, (string) $request->input('t', ''))) {
            ActivityLogger::log(
                'security.payment_forgery',
                'درخواست جعلی callback واریز کیف پول برای ردیف '.Fmt::fa($deposit->id).' رد شد (بدون امضای معتبر).',
                subject: $deposit,
            );

            // Never leak the deposit's state — behave like an unknown id.
            return redirect()->route('landing');
        }

        if ($deposit->status !== WalletDeposit::STATUS_PENDING) {
            return redirect()->route('seller.wallet')
                ->with('wallet-flash', 'وضعیت واریز: '.$deposit->statusLabel());
        }

        try {
            $receipt = WalletDepositService::verify($deposit);
            $fresh = WalletDepositService::finalizePaid($deposit, $receipt->getReferenceId());

            ActivityLogger::log(
                'wallet.deposit.done',
                'واریز '.Fmt::money($fresh->amount).' به کیف پول فروشنده از درگاه '.$fresh->gateway.' موفق شد — کد رهگیری '.mb_substr($receipt->getReferenceId(), 0, 40),
                subject: $fresh,
                after: ['status' => 'done', 'amount' => (int) $fresh->amount],
            );

            AdminNotifier::notify(
                'wallet.deposit.done',
                'واریز کیف پول ✅ '.Fmt::money($fresh->amount),
                'فروشنده @'.($fresh->seller?->instagram_username ?: $fresh->seller?->phone).' مبلغ '.Fmt::money($fresh->amount).' از درگاه '.$fresh->gateway.' به کیف پول واریز کرد.',
                route('admin.finance'),
                'wallet',
                $fresh,
            );

            return redirect()->route('seller.wallet')
                ->with('wallet-flash', 'واریز '.Fmt::money($fresh->amount).' تومان با موفقیت انجام شد و به کیف پول شما اضافه شد. ✅');
        } catch (PreviouslyVerifiedException $e) {
            // The trackId was already confirmed — the money WAS taken;
            // a duplicate/retried callback must NOT take the failure path.
            $fresh = WalletDepositService::finalizePaid($deposit, (string) ($deposit->gateway_ref !== '' ? $deposit->gateway_ref : 'previously-verified'));

            ActivityLogger::log(
                'wallet.deposit.done',
                'واریز کیف پول تأیید شد (callback تکراری — تراکنش قبلاً وریفای شده بود)',
                subject: $fresh,
                after: ['status' => 'done'],
            );

            return redirect()->route('seller.wallet')
                ->with('wallet-flash', 'واریز '.Fmt::money($fresh->amount).' تومان قبلاً تأیید شده و به کیف پول شما اضافه شده است. ✅');
        } catch (Throwable $e) {
            WalletDepositService::markFailed($deposit, $e->getMessage());

            ActivityLogger::log(
                'wallet.deposit.failed',
                'واریز '.Fmt::money($deposit->amount).' به کیف پول ناموفق بود',
                subject: $deposit,
                after: ['status' => 'failed', 'reason' => mb_substr($e->getMessage(), 0, 190)],
            );

            return redirect()->route('seller.wallet')
                ->with('wallet-deposit-error', 'پرداخت انجام نشد: '.mb_substr($e->getMessage(), 0, 120));
        }
    }
}
