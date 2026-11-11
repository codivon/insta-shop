<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\WalletDeposit;
use App\Support\ActivityLogger;
use App\Support\Payments\WalletDepositService;
use Illuminate\Http\Request;
use Throwable;

/**
 * Hands the SELLER over to the payment gateway for a wallet deposit
 * (v0.24.0) — mirrors Shop\PaymentRedirect: lives outside Livewire
 * because the gateway hand-off must render the raw redirection form HTML.
 */
class WalletDepositGo extends Controller
{
    public function __invoke(Request $request, WalletDeposit $deposit)
    {
        $seller = $request->user('seller');

        /*
         * SECURITY: non-owned deposits answer 404 (not 403) — no oracle
         * for enumerating other sellers' pending deposit ids.
         */
        abort_unless($seller && $deposit->seller_id === $seller->id, 404);

        // Already processed → back to the wallet with its state.
        if ($deposit->status !== WalletDeposit::STATUS_PENDING) {
            return redirect()->route('seller.wallet')
                ->with('wallet-flash', 'این درخواست واریز قبلاً پردازش شده است ('.$deposit->statusLabel().').');
        }

        try {
            $form = WalletDepositService::purchase($deposit);
        } catch (Throwable $e) {
            report($e);
            WalletDepositService::markFailed($deposit, $e->getMessage());

            return redirect()
                ->route('seller.wallet')
                ->with('wallet-deposit-error', 'اتصال به درگاه پرداخت برقرار نشد. لطفاً دوباره تلاش کنید.');
        }

        ActivityLogger::log(
            'wallet.deposit.started',
            'فروشنده برای واریز '.number_format($deposit->amount).' تومان به کیف پول، به درگاه «'.$deposit->gateway.'» هدایت شد',
            subject: $deposit,
            after: ['amount' => (int) $deposit->amount, 'gateway' => $deposit->gateway],
        );

        return response($form->render());
    }
}
