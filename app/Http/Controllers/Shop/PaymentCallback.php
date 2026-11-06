<?php

namespace App\Http\Controllers\Shop;

use App\Models\Order;
use App\Models\PaymentLog;
use App\Support\ActivityLogger;
use App\Support\AdminNotifier;
use App\Support\Fmt;
use App\Support\Payments\PaymentService;
use Illuminate\Http\Request;
use Shetabit\Multipay\Exceptions\PreviouslyVerifiedException;
use Throwable;

/**
 * Gateway callback (GET redirect from most gateways, POST from the local
 * test gateway). Public on purpose: the order is identified by the `o`
 * query parameter + its stored gateway reference, not by the session.
 *
 * Success path  → verify → finalizePaid (stock-, cart-) → result page
 * Failure path  → markFailed → result page (failure state)
 */
class PaymentCallback
{
    public function __invoke(Request $request)
    {
        $order = Order::query()->find((int) $request->input('o', 0));

        if (! $order) {
            return redirect()->route('landing');
        }

        /*
         * SECURITY: this endpoint is public (banks redirect browsers here),
         * so the per-order HMAC token (`t`) — minted into the callback URL
         * at purchase time — is the ONLY acceptable proof. A guessed
         * `?o={id}` (sequential ids!) without the token is rejected BEFORE
         * any verify()/finalizePaid() call; the attempt is audited.
         */
        if (! PaymentService::callbackTokenValid($order, (string) $request->input('t', ''))) {
            PaymentLog::record(
                $order,
                (string) ($order->gateway ?: 'unknown'),
                'callback_rejected',
                (string) $order->gateway_ref,
                (int) $order->total_amount,
                false,
                'forged/unsigned callback rejected (missing or invalid HMAC token)',
            );

            ActivityLogger::log(
                'security.payment_forgery',
                'درخواست جعلی callback پرداخت برای سفارش '.Fmt::fa($order->id).' رد شد (بدون امضای معتبر).',
                subject: $order,
            );

            // Never leak the order's state — behave like an unknown order.
            return redirect()->route('landing');
        }

        if ($order->status !== 'pending') {
            return redirect()->route('shop.payment.result', $order->id);
        }

        /*
         * The success path itself is hardened inside PaymentService::verify()
         * (signed URL + token-gated local driver). The local driver's
         * transactionId is restored from the order only in local environments.
         */
        try {
            $receipt = PaymentService::verify($order);
            $order->finalizePaid($receipt->getReferenceId());

            ActivityLogger::log(
                'order.paid',
                'پرداخت سفارش '.Fmt::fa($order->id).' موفق شد — مبلغ '.Fmt::money($order->total_amount),
                subject: $order,
                after: ['status' => 'paid'],
            );

            AdminNotifier::notify(
                'order.paid',
                'پرداخت موفق ✅ سفارش '.Fmt::fa($order->id),
                'مبلغ '.Fmt::money($order->total_amount).' پرداخت شد.',
                route('admin.orders.show', $order->id),
                'circle-check',
                $order,
            );
        } catch (PreviouslyVerifiedException $e) {
            /*
             * SECURITY (v0.21.0): Zibal answers 201 («previously verified»)
             * when the trackId was already confirmed — the money WAS taken.
             * A duplicate/retried callback must NOT take the failure path:
             * the old code called markFailed() here, which (with the stale
             * status check) could flip an order the racing callback had
             * just finalized into «failed» AFTER stock/wallet settlement.
             * finalizePaid is idempotent (pending-only, row-locked) — this
             * path just re-enters the success flow.
             */
            PaymentLog::record(
                $order,
                (string) ($order->gateway ?: 'zibal'),
                'callback_previously_verified',
                (string) $order->gateway_ref,
                (int) $order->total_amount,
                true,
                'gateway reports the trackId was verified before — treated as paid (idempotent)',
            );

            $order->finalizePaid((string) ($order->gateway_ref !== '' ? $order->gateway_ref : 'previously-verified'));

            ActivityLogger::log(
                'order.paid',
                'پرداخت سفارش '.Fmt::fa($order->id).' تأیید شد (callback تکراری — تراکنش قبلاً وریفای شده بود)',
                subject: $order,
                after: ['status' => 'paid'],
            );
        } catch (Throwable $e) {
            // the exact reason lands in payment_logs — the developer panel
            // (and the payment log table) can now show WHY it failed.
            PaymentLog::record(
                $order,
                (string) ($order->gateway ?: 'local'),
                'callback_failed',
                (string) $order->gateway_ref,
                (int) $order->total_amount,
                false,
                $e->getMessage(),
            );

            $order->markFailed();

            ActivityLogger::log(
                'order.failed',
                'پرداخت سفارش '.Fmt::fa($order->id).' ناموفق بود',
                subject: $order,
                after: ['status' => 'failed', 'reason' => mb_substr($e->getMessage(), 0, 190)],
            );

            AdminNotifier::notify(
                'order.failed',
                'پرداخت ناموفق ⚠️ سفارش '.Fmt::fa($order->id),
                mb_substr($e->getMessage(), 0, 150),
                route('admin.orders.show', $order->id),
                'alert',
                $order,
            );
        }

        return redirect()->route('shop.payment.result', $order->id);
    }
}
