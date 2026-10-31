<?php

namespace App\Support\Payments;

use App\Models\Order;
use App\Models\PaymentLog;
use App\Models\Setting;
use Shetabit\Multipay\Invoice;
use Shetabit\Multipay\Receipt;
use Shetabit\Multipay\RedirectionForm;
use Shetabit\Payment\Facade\Payment;
use Throwable;

/**
 * Thin wrapper around shetabit/payment.
 *
 * The active gateway is read from the DB settings (group "payment") so the
 * admin panel can switch it later (Phase 6) — defaults to the local test
 * gateway. Every attempt is audited into payment_logs.
 */
class PaymentService
{
    /** Gateways selectable from the admin panel (Phase 6 UI). */
    public const GATEWAYS = [
        'local' => 'درگاه آزمایشی (محلی)',
        'zarinpal' => 'زرین‌پال',
        'zibal' => 'زیبال',
        'behpardakht' => 'بانک ملت',
        'sep' => 'درگاه سپ',
    ];

    /**
     * Credential fields per gateway, editable from the admin panel
     * (stored in settings as "payment.{gateway}.{field}").
     * Anything left empty falls back to config/payment.php (i.e. .env).
     */
    public const CREDENTIAL_FIELDS = [
        'local' => [],
        'zarinpal' => ['merchantId' => 'مرچنت‌کد زرین‌پال'],
        'zibal' => ['merchantId' => 'مرچنت‌کد زیبال'],
        'behpardakht' => ['terminalId' => 'شماره ترمینال', 'username' => 'نام کاربری', 'password' => 'رمز عبور'],
        'sep' => ['terminalId' => 'شماره ترمینال'],
    ];

    public static function activeGateway(): string
    {
        $gateway = (string) Setting::get('payment.gateway', 'local');

        return array_key_exists($gateway, self::GATEWAYS) ? $gateway : 'local';
    }

    /**
     * Build the "drivers" config block with credentials from the settings
     * merged over config/payment.php (env-backed defaults). We must pass
     * the WHOLE drivers map because shetabit's config() replaces keys.
     */
    public static function runtimeDriversConfig(string $gateway): array
    {
        $drivers = (array) config('payment.drivers', []);

        foreach (self::CREDENTIAL_FIELDS[$gateway] ?? [] as $field => $label) {
            $value = trim((string) Setting::get("payment.{$gateway}.{$field}", ''));

            if ($value !== '') {
                $drivers[$gateway] = array_merge($drivers[$gateway] ?? [], [$field => $value]);
            }
        }

        return ['drivers' => $drivers];
    }

    /**
     * Purchase the order through the active gateway and return the
     * redirection form that sends the customer to the gateway page.
     *
     * @throws Throwable when the gateway refuses the purchase
     */
    public static function purchase(Order $order): RedirectionForm
    {
        $gateway = self::activeGateway();

        $invoice = new Invoice;
        $invoice->amount($order->total_amount);
        $invoice->detail('orderId', (string) $order->id);

        // One shared manager instance: purchase() and pay() must see the
        // same invoice — the container binds a fresh instance per resolve,
        // so never split this chain across facade calls. The runtime
        // drivers config carries credentials saved from the admin panel.
        $manager = Payment::config(self::runtimeDriversConfig($gateway))
            ->via($gateway)
            ->callbackUrl(self::callbackUrl($order));

        try {
            $manager->purchase($invoice, function ($driver, string $transactionId) use ($order, $gateway): void {
                $order->forceFill(['gateway' => $gateway, 'gateway_ref' => $transactionId])->save();
            });
        } catch (Throwable $e) {
            PaymentLog::record($order, $gateway, 'purchase', null, (int) $order->total_amount, false, $e->getMessage());

            throw $e;
        }

        $order->refresh();

        PaymentLog::record(
            $order,
            $gateway,
            'redirect',
            (string) $order->gateway_ref,
            (int) $order->total_amount,
            true,
            'redirecting customer to gateway',
        );

        return $manager->pay();
    }

    /**
     * Verify the gateway callback against the order and return the receipt.
     *
     * @throws Throwable on failure (canceled / invalid / previously verified…)
     */
    public static function verify(Order $order): Receipt
    {
        $gateway = $order->gateway ?: self::activeGateway();

        try {
            $receipt = Payment::config(self::runtimeDriversConfig($gateway))
                ->via($gateway)
                ->amount($order->total_amount)
                ->transactionId((string) $order->gateway_ref)
                ->verify();
        } catch (Throwable $e) {
            PaymentLog::record(
                $order,
                $gateway,
                'verify_failed',
                (string) request('transactionId', $order->gateway_ref),
                (int) $order->total_amount,
                false,
                $e->getMessage(),
            );

            throw $e;
        }

        PaymentLog::record(
            $order,
            $gateway,
            'verify_ok',
            (string) $order->gateway_ref,
            (int) $order->total_amount,
            true,
            'ref: '.$receipt->getReferenceId(),
            ['receipt' => $receipt->getDetails()],
        );

        return $receipt;
    }

    /** Per-order callback URL (the gateway appends transactionId/cancel). */
    protected static function callbackUrl(Order $order): string
    {
        return route('shop.payment.callback', ['o' => $order->id]);
    }
}
