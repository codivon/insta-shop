<?php

namespace App\Support\Payments;

use App\Models\Order;
use App\Models\PaymentLog;
use App\Models\Setting;
use App\Support\Fmt;
use App\Support\Payments\Drivers\SplitAsanpardakht;
use App\Support\Payments\Drivers\SplitPayping;
use App\Support\Payments\Drivers\SplitSep;
use Shetabit\Multipay\Drivers\Asanpardakht\Asanpardakht;
use Shetabit\Multipay\Drivers\Payping\Payping;
use Shetabit\Multipay\Drivers\SEP\SEP;
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
        'novinopay' => 'نوین‌پی',
        'payping' => 'پی‌پینگ',
        'sadad' => 'سداد (بانک ملی)',
        'behpardakht' => 'به‌پرداخت (بانک ملت)',
        'parsian' => 'پارسیان',
        'irankish' => 'ایران‌کیش',
        'pasargad' => 'پاسارگاد',
        'asanpardakht' => 'آسان‌پرداخت',
        'sepehr' => 'سپهر (بانک صادرات)',
        'sep' => 'سپ الکترونیک (سامان)',
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
        'novinopay' => ['merchantId' => 'مرچنت‌کد نوین‌پی'],
        'payping' => ['merchantId' => 'کد اختصاصی پی‌پینگ (Bearer Token)'],
        'sadad' => ['key' => 'کلید سداد (Key)', 'merchantId' => 'مرچنت‌کد سداد', 'terminalId' => 'شماره ترمینال سداد'],
        'behpardakht' => ['terminalId' => 'شماره ترمینال به‌پرداخت', 'username' => 'نام کاربری به‌پرداخت', 'password' => 'رمز عبور به‌پرداخت'],
        'parsian' => ['merchantId' => 'مرچنت‌کد پارسیان (Login Account)'],
        'irankish' => [
            'terminalId' => 'شماره ترمینال ایران‌کیش',
            'password' => 'رمز عبور ایران‌کیش',
            'acceptorId' => 'شناسه پذیرنده ایران‌کیش (Acceptor ID)',
            'pubKey' => 'کلید عمومی RSA ایران‌کیش (Public Key)',
        ],
        'pasargad' => ['userName' => 'نام کاربری پاسارگاد', 'password' => 'رمز عبور پاسارگاد', 'terminalCode' => 'کد ترمینال پاسارگاد'],
        'asanpardakht' => [
            'username' => 'نام کاربری آسان‌پرداخت',
            'password' => 'رمز عبور آسان‌پرداخت',
            'merchantConfigID' => 'شناسه پیکربندی پذیرنده (Merchant Config ID)',
        ],
        'sepehr' => ['terminalId' => 'شماره ترمینال سپهر (صادرات)'],
        'sep' => ['terminalId' => 'شماره ترمینال سپ الکترونیک'],
    ];

    /**
     * v0.27.0 — پرداخت تسهیمی: the driver classes swapped in when the
     * admin toggle turns split payment ON for a capable gateway (the
     * vendor package stays untouched — the swap rides config('payment.map')).
     * v0.28.0 — sep joins the club (مستند «تراکنش تسویه به چند حساب»).
     */
    public const SPLIT_DRIVER_MAP = [
        'payping' => SplitPayping::class,
        'asanpardakht' => SplitAsanpardakht::class,
        'sep' => SplitSep::class,
    ];

    /**
     * The ORIGINAL vendor classes — applyRuntimeConfig always writes an
     * EXPLICIT map entry so a split-off call restores the vendor driver
     * even when an earlier split-on call already swapped it (the config
     * repository lives for the whole request).
     */
    private const VENDOR_DRIVER_MAP = [
        'payping' => Payping::class,
        'asanpardakht' => Asanpardakht::class,
        'sep' => SEP::class,
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
     * Gateways the CUSTOMER may choose from at checkout (v0.28.0): the
     * admin panel now toggles each gateway ON/OFF (چند درگاه هم‌زمان) —
     * a gateway is offered when it is ENABLED and actually configured
     * (credentials saved in the panel or .env); the local test gateway
     * only exists in local environments.
     *
     * FAIL-OPEN legacy fallback: when NO gateway is explicitly enabled
     * (a pre-toggle install before the first save), the old behavior
     * applies — any gateway holding credentials stays available so a
     * running store never loses its checkout.
     *
     * @return array<string, string> key → Persian label
     */
    public static function availableGateways(): array
    {
        $enabled = [];

        foreach (self::GATEWAYS as $key => $label) {
            if (! self::isEnabled($key)) {
                continue;
            }

            if ($key !== 'local' && ! self::hasCredentials($key)) {
                continue; // enabled but unconfigured — would die at purchase
            }

            $enabled[$key] = $label;
        }

        if ($enabled !== []) {
            return $enabled;
        }

        // Legacy behavior (pre-multi-gateway installs): credentials decide.
        return self::credentialedGateways();
    }

    /** The admin toggle for a gateway — settings payment.{g}.enabled. */
    public static function isEnabled(string $gateway): bool
    {
        if ($gateway === 'local') {
            return self::localGatewayAllowed()
                && Setting::get('payment.local.enabled', '1') === '1';
        }

        $stored = Setting::get("payment.{$gateway}.enabled");

        if ($stored !== null) {
            return $stored === '1';
        }

        /*
         * Legacy default (before the toggles existed): a gateway counted
         * as usable when it held credentials or was the active one — keep
         * that state until the admin saves the panel once.
         */
        return $gateway === self::activeGateway() || self::hasCredentials($gateway);
    }

    /** Does the gateway have ANY credential (settings DB or .env config)? */
    public static function hasCredentials(string $gateway): bool
    {
        foreach (self::CREDENTIAL_FIELDS[$gateway] ?? [] as $field => $_) {
            if (trim((string) Setting::get("payment.{$gateway}.{$field}", '')) !== '') {
                return true;
            }

            if (trim((string) data_get(config('payment.drivers.'.$gateway, []), $field, '')) !== '') {
                return true;
            }
        }

        return false;
    }

    /** The PRE-multi-gateway rule: a gateway exists when it holds credentials. */
    private static function credentialedGateways(): array
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
     * CRITICAL FIX (v0.27.0): the gateway's credentials reach the driver
     * ONLY through the Laravel config repository.
     *
     * Proven at runtime: shetabit's Payment::config() merges into its
     * INTERNAL $settings, and via() then REPLACES $settings with
     * driverConfig() = merge(package defaults, config('payment').drivers)
     * — so the previous approach (Payment::config(['drivers' => …])) was
     * silently DROPPING every admin-panel credential before the driver
     * was ever built (only «local», which needs none, kept working).
     *
     * This method merges the DB settings over the env-backed config and
     * writes them back to config('payment.drivers') — the container binds
     * a FRESH Payment instance per resolve, whose constructor snapshots
     * config('payment'), so via() now hands the real credentials to the
     * driver. The same channel swaps the DRIVER CLASS for split payment
     * (config('payment.map')) — the vendor package stays untouched.
     */
    public static function applyRuntimeConfig(string $gateway, bool $split = false): void
    {
        $drivers = (array) config('payment.drivers', []);

        foreach (self::CREDENTIAL_FIELDS[$gateway] ?? [] as $field => $label) {
            $value = trim((string) Setting::get("payment.{$gateway}.{$field}", ''));

            if ($value !== '') {
                $drivers[$gateway] = array_merge($drivers[$gateway] ?? [], [$field => $value]);
            }
        }

        config()->set('payment.drivers', $drivers);

        if (isset(self::SPLIT_DRIVER_MAP[$gateway])) {
            config()->set(
                'payment.map.'.$gateway,
                $split ? self::SPLIT_DRIVER_MAP[$gateway] : self::VENDOR_DRIVER_MAP[$gateway],
            );
        }
    }

    /**
     * Purchase the order through the active gateway and return the
     * redirection form that sends the customer to the gateway page.
     *
     * v0.27.0 — پرداخت تسهیمی: when the gateway is split-capable, the
     * admin toggle is ON and every shop of the order has a split
     * destination, the seller shares ride the gateway's split API and the
     * wallet credit is skipped at finalization. A failed split purchase
     * (before any gateway transaction exists) falls back to the standard
     * flow instead of killing the checkout.
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

        $splitPlan = SplitPlan::forOrder($order, $gateway);

        try {
            return self::startPurchase($order, $gateway, $splitPlan);
        } catch (Throwable $splitError) {
            /*
             * FAIL-SAFE تسهیم: the split attempt died before ANY gateway
             * transaction existed (gateway_ref still null) → retry the
             * standard flow so the customer is never stranded at checkout;
             * sellers simply take the wallet path for this order.
             */
            if ($splitPlan === null || $order->refresh()->gateway_ref !== null) {
                throw $splitError;
            }

            PaymentLog::record($order, $gateway, 'split_fallback', null, (int) $order->total_amount, true,
                'پرداخت تسهیمی ناموفق بود — ادامه با روش معمولی (سهم فروشندگان به کیف پول واریز می‌شود): '.$splitError->getMessage());
        }

        return self::startPurchase($order, $gateway, null);
    }

    /**
     * The single purchase attempt — one gateway handshake with (or
     * without) the split payload.
     *
     * @param  array{gateway: string, shares: list<array{shop_id: int, shop_name: string, seller_id: int, destination: string, amount: int, items_total: int}>}|null  $splitPlan
     */
    private static function startPurchase(Order $order, string $gateway, ?array $splitPlan): RedirectionForm
    {
        // One shared manager instance: purchase() and pay() must see the
        // same invoice — the container binds a fresh instance per resolve,
        // so never split this chain across facade calls. Credentials ride
        // config('payment') (applyRuntimeConfig) — see its docblock.
        self::applyRuntimeConfig($gateway, $splitPlan !== null);

        $invoice = new Invoice;
        $invoice->amount($order->total_amount);
        $invoice->detail('orderId', (string) $order->id);

        if ($splitPlan !== null) {
            self::attachSplitDetails($invoice, $gateway, $order, $splitPlan);
        }

        $manager = Payment::via($gateway)
            ->callbackUrl(self::callbackUrl($order));

        try {
            $manager->purchase($invoice, function ($driver, string $transactionId) use ($order, $gateway, $splitPlan): void {
                $order->forceFill([
                    'gateway' => $gateway,
                    'gateway_ref' => $transactionId,
                    'split_payment' => $splitPlan !== null,
                ])->save();
            });
        } catch (Throwable $e) {
            PaymentLog::record(
                $order,
                $gateway,
                $splitPlan !== null ? 'split_purchase' : 'purchase',
                null,
                (int) $order->total_amount,
                false,
                $e->getMessage(),
            );

            throw $e;
        }

        $order->refresh();

        PaymentLog::record(
            $order,
            $gateway,
            $splitPlan !== null ? 'split_redirect' : 'redirect',
            (string) $order->gateway_ref,
            (int) $order->total_amount,
            true,
            $splitPlan !== null
                ? 'تسهیم فعال — سهم '.Fmt::fa(count($splitPlan['shares'])).' فروشگاه مستقیماً به مقصد تسهیم واریز می‌شود'
                : 'redirecting customer to gateway',
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
     * Translate the split plan into the payload each gateway's driver
     * expects on the invoice:
     *   • payping      → splitItems  (amounts in تومان + userIdentity);
     *   • sep          → splitSettlements (amounts in تومان, converted to
     *     ریال inside the driver + PurchaseID = 30 zeros);
     *   • asanpardakht → splitServices (amounts in تومان, converted to
     *     ریال inside the driver + iban).
     */
    private static function attachSplitDetails(Invoice $invoice, string $gateway, Order $order, array $splitPlan): void
    {
        $code = str_pad((string) $order->id, 5, '0', STR_PAD_LEFT);

        if ($gateway === 'payping') {
            $invoice->detail('splitItems', array_map(static fn (array $share): array => [
                'amount' => (int) $share['amount'],
                'userIdentity' => (string) $share['destination'],
                'description' => 'سهم فروشگاه '.$share['shop_name'].' از سفارش #'.$code,
            ], $splitPlan['shares']));

            return;
        }

        if ($gateway === 'sep') {
            // مستند سپ «تراکنش تسویه به چند حساب»: IBAN + Amount (ریال داخل
            // درایور) + PurchaseID (تسویهٔ عادی = ۳۰ کاراکتر صفر داخل درایور).
            $invoice->detail('splitSettlements', array_map(static fn (array $share): array => [
                'iban' => (string) $share['destination'],
                'amount' => (int) $share['amount'],
            ], $splitPlan['shares']));

            return;
        }

        $invoice->detail('splitServices', array_map(static fn (array $share): array => [
            'serviceName' => (string) $share['shop_name'],
            'amount' => (int) $share['amount'],
            'iban' => (string) $share['destination'],
        ], $splitPlan['shares']));
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

        // Same credentials channel as purchase (see applyRuntimeConfig's
        // docblock — verify() must see the DB credentials too).
        self::applyRuntimeConfig($gateway);

        try {
            $receipt = Payment::via($gateway)
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

        /*
         * SECURITY (v0.21.0): the paid amount is compared locally for the
         * drivers that do NOT re-send the amount at verify time (Zibal's
         * API takes only merchant + trackId). Zibal echoes the bound
         * amount/paidAmount in RIAL (vendor currency TOMAN → ratio 10);
         * a mismatch below the order total fails closed. Zarinpal already
         * sends the amount inside the verify request (gateway-side check).
         */
        if ($gateway === 'zibal') {
            $details = (array) $receipt->getDetails();
            $paidRial = (int) ($details['paidAmount'] ?? $details['amount'] ?? 0);

            if ($paidRial > 0 && (int) round($paidRial / 10) < (int) $order->total_amount) {
                PaymentLog::record(
                    $order,
                    $gateway,
                    'verify_amount_mismatch',
                    (string) $order->gateway_ref,
                    (int) $order->total_amount,
                    false,
                    'gateway paid amount '.$paidRial.' rial is BELOW the order total '.(int) $order->total_amount.' toman',
                );

                throw new \RuntimeException('مبلغ پرداخت‌شده با مبلغ سفارش مطابقت ندارد — تراکنش تأیید نشد.');
            }
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
