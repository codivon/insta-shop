<?php

namespace App\Http\Controllers\Shop;

use App\Models\Order;
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

        if ($order->status !== 'pending') {
            return redirect()->route('shop.payment.result', $order->id);
        }

        try {
            $receipt = PaymentService::verify($order);
            $order->finalizePaid($receipt->getReferenceId());
        } catch (Throwable $e) {
            $order->markFailed();
        }

        return redirect()->route('shop.payment.result', $order->id);
    }
}
