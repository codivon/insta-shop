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
     * SECURITY: the local TEST gateway must never run on a production
     * install — its verify() succeeds purely from the order's stored
     * reference, so a public callback would mark orders paid for free.
     */
    public static function localGatewayAllowed(): bool
    {
        return app()->isLocal();
    }

    /**
     * Gateways the CUSTOMER may choose from at checkout: the local test
     * gateway is always there; a real gateway shows up once ANY credential
     * for it is saved (admin settings panel or .env config).
     *
     * @return array<string, string> key → Persian label
     */
    public static function availableGateways(): array
    {
        $available = [];

        foreach (self::GATEWAYS as $key => $label) {
            if ($key === 'local') {
                if (self::localGatewayAllowed()) {
                    $available[$key] = $label;
                }

                continue;
            }

            foreach (self::CREDENTIAL_FIELDS[$key] ?? [] as $field => $_) {
                $fromSettings = trim((string) Setting::get("payment.{$key}.{$field}", ''));
                $fromConfig = trim((string) data_get(config('payment.drivers.'.$key, []), $field, ''));

                if ($fromSettings !== '' || $fromConfig !== '') {
                    $available[$key] = $label;

                    break;
                }
            }
        }

        return $available;
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
        // The gateway the CUSTOMER chose at checkout wins; fall back to the
        // admin-configured default when the order predates that feature or
        // carries an unknown key.
        $gateway = $order->gateway && array_key_exists($order->gateway, self::GATEWAYS)
            ? $order->gateway
            : self::activeGateway();

        // Fail CLOSED: outside local, the test gateway never touches real orders.
        if ($gateway === 'local' && ! self::localGatewayAllowed()) {
            PaymentLog::record($order, 'local', 'purchase_blocked', null, (int) $order->total_amount, false,
                'local test gateway is disabled outside local environments');

            throw new \RuntimeException('درگاه آزمایشی روی سرور عملیاتی غیرفعال است — از پنل مدیریت یک درگاه واقعی تنظیم کنید.');
        }

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

        $form = $manager->pay();

        /*
         * The local TEST gateway gets our own modern sandbox page (a card
         * styled like a real bank gateway) instead of the vendor's plain
         * form. setViewPath is STATIC — set it only for the local driver
         * and reset it for real banks so they keep the default view.
         */
        if ($gateway === 'local') {
            RedirectionForm::setViewPath(resource_path('views/vendor/multipay/local-form.php'));
        } else {
            RedirectionForm::setViewPath(RedirectionForm::getDefaultViewPath());
        }

        return $form;
    }

    /**
     * Verify the gateway callback against the order and return the receipt.
     *
     * @throws Throwable on failure (canceled / invalid / previously verified…)
     */
    public static function verify(Order $order): Receipt
    {
        $gateway = $order->gateway ?: self::activeGateway();

        /*
         * Success-path hardening for the LOCAL TEST gateway (the
         * «پرداخت موفق → ناموفق» bug): the local driver POSTs to a URL whose
         * QUERY string carries the transactionId, and proxies/redirects can
         * lose it — the stored reference is restored here. This auto-inject
         * is what makes the local driver self-verifying, therefore it is
         * allowed ONLY where the local gateway itself is allowed (local
         * environments). Production gateways verify cryptographically via
         * their own API and never need it.
         */
        if ($gateway === 'local'
            && self::localGatewayAllowed()
            && (string) $order->gateway_ref !== ''
            && ! request()->filled('transactionId')
            && ! request()->boolean('cancel')) {
            request()->merge(['transactionId' => (string) $order->gateway_ref]);
        }

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

    /**
     * SECURITY: the callback is PUBLIC (banks redirect real browsers to it),
     * so the order id travels with a per-order HMAC token. Anyone POST/GETing
     * the callback without the token (i.e. every guessed URL) is dropped —
     * a forged callback can never reach verify()/finalizePaid().
     */
    public static function callbackToken(Order $order): string
    {
        return hash_hmac('sha256', 'payment-callback:'.$order->id, (string) config('app.key'));
    }

    public static function callbackTokenValid(Order $order, string $token): bool
    {
        return $token !== ''
            && hash_equals(self::callbackToken($order), $token);
    }

    /** Per-order SIGNED callback URL (the gateway appends transactionId/cancel). */
    protected static function callbackUrl(Order $order): string
    {
        return route('shop.payment.callback', ['o' => $order->id, 't' => self::callbackToken($order)]);
    }
}
