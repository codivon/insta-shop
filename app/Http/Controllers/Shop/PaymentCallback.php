<?php

namespace App\Http\Controllers\Shop;

use App\Models\Order;
use App\Models\PaymentLog;
use App\Support\ActivityLogger;
use App\Support\AdminNotifier;
use App\Support\Fmt;
use App\Support\Payments\PaymentService;
use Illuminate\Http\Request;
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
