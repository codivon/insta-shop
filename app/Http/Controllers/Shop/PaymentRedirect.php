<?php

namespace App\Http\Controllers\Shop;

use App\Models\Order;
use App\Models\PaymentLog;
use App\Support\Payments\PaymentService;
use Illuminate\Http\Request;
use Throwable;

/**
 * Hands the customer over to the payment gateway.
 *
 * Lives outside Livewire because the gateway hand-off must render the
 * raw redirection form HTML (a full document) returned by shetabit.
 */
class PaymentRedirect
{
    public function __invoke(Request $request, Order $order)
    {
        $customer = $request->user('customer');

        abort_unless($customer && $order->customer_id === $customer->id, 403);

        // Already processed → show its result page instead.
        if ($order->status !== 'pending') {
            return redirect()->route('shop.payment.result', $order->id);
        }

        /*
         * Last-moment re-check (defence in depth): only published products
         * with enough stock can be paid — the cart contents may have gone
         * stale between checkout and this redirect.
         */
        foreach ($order->items as $item) {
            $product = $item->product;

            if (! $product || $product->status !== 'published' || $product->stock < $item->quantity) {
                PaymentLog::record(
                    $order,
                    $order->gateway,
                    'precheck_failed',
                    null,
                    null,
                    false,
                    "product [{$item->product_name}] is no longer purchasable",
                );
                $order->markFailed();

                return redirect()
                    ->route('shop.cart')
                    ->with('checkout-error', 'برخی اقلام سفارش دیگر قابل خرید نیستند (حذف‌شده یا ناموجود). سبد را بررسی کنید.');
            }
        }

        try {
            $form = PaymentService::purchase($order);
        } catch (Throwable $e) {
            report($e);
            $order->markFailed();

            return redirect()
                ->route('shop.cart')
                ->with('checkout-error', 'اتصال به درگاه پرداخت برقرار نشد. لطفاً دوباره تلاش کنید.');
        }

        return response($form->render());
    }
}
