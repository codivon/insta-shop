<?php

namespace App\Support\Payments;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentLog;
use App\Models\SellerBnplSetting;
use App\Models\Setting;
use App\Support\Fmt;
use App\Support\Payments\Drivers\BnplTorobPay;
use App\Support\Payments\Drivers\SplitAsanpardakht;
use App\Support\Payments\Drivers\SplitPayping;
use App\Support\Payments\Drivers\SplitSep;
use Shetabit\Multipay\Drivers\Asanpardakht\Asanpardakht;
use Shetabit\Multipay\Drivers\Digipay\Digipay;
use Shetabit\Multipay\Drivers\Local\Local;
use Shetabit\Multipay\Drivers\Payping\Payping;
use Shetabit\Multipay\Drivers\SEP\SEP;
use Shetabit\Multipay\Drivers\SnappPay\SnappPay;
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
     * v0.30.0 — درگاه‌های پرداخت اقساطی (BNPL) — یک بخش کاملاً جدا:
     *
     * دیجی‌پی / اسنپ‌پی / ترب‌پی are BUY-NOW-PAY-LATER gateways — the
     * customer pays the FIRST installment at checkout and the rest to the
     * gateway over the following months, while the gateway settles the
     * MERCHANT monthly. Orders paid here therefore NEVER credit seller
     * wallets instantly — they create pending BnplSettlement rows instead
     * (see App\Support\Payments\BnplSettlements).
     *
     * These slugs live OUTSIDE GATEWAYS on purpose: checkout shows them in
     * their own «پرداخت اقساطی» section, the admin panel gets its own
     * settings section, and wallet DEPOSITS never offer them.
     *
     * NOTE the snapppay spelling (double p) — it must match the vendor
     * shetabit/multipay driver map exactly.
     */
    public const BNPL_GATEWAYS = [
        'digipay' => 'دیجی‌پی',
        'snapppay' => 'اسنپ‌پی',
        'torobpay' => 'ترب‌پی',
    ];

    /**
     * v0.30.0 — credential fields of the BNPL trio (OAuth password grant:
     * client_id/client_secret Basic pair + username/password). Stored as
     * settings "payment.{gateway}.{field}" exactly like the direct
     * gateways. hasCredentials()/applyRuntimeConfig() read BOTH maps.
     */
    public const BNPL_CREDENTIAL_FIELDS = [
        'digipay' => [
            'client_id' => 'Client ID دیجی‌پی',
            'client_secret' => 'Client Secret دیجی‌پی',
            'username' => 'نام کاربری دیجی‌پی',
            'password' => 'رمز عبور دیجی‌پی',
        ],
        'snapppay' => [
            'client_id' => 'Client ID اسنپ‌پی',
            'client_secret' => 'Client Secret اسنپ‌پی',
            'username' => 'نام کاربری اسنپ‌پی',
            'password' => 'رمز عبور اسنپ‌پی',
        ],
        'torobpay' => [
            'client_id' => 'Client ID ترب‌پی',
            'client_secret' => 'Client Secret ترب‌پی',
            'username' => 'نام کاربری ترب‌پی',
            'password' => 'رمز عبور ترب‌پی',
        ],
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

    /**
     * v0.30.0 — the REAL driver classes of the BNPL trio. torobpay rides
     * our subclass that fixes the vendor's missing RIAL conversion inside
     * the cartList payload (vendor sends `amount` in RIAL but a TOMAN
     * cartList — TorobPay cross-checks the two and would reject).
     */
    private const BNPL_VENDOR_DRIVER_MAP = [
        'digipay' => Digipay::class,
        'snapppay' => SnappPay::class,
        'torobpay' => BnplTorobPay::class,
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
        foreach (self::credentialFieldsFor($gateway) as $field => $_) {
            if (trim((string) Setting::get("payment.{$gateway}.{$field}", '')) !== '') {
                return true;
            }

            if (trim((string) data_get(config('payment.drivers.'.$gateway, []), $field, '')) !== '') {
                return true;
            }
        }

        return false;
    }

    /** The editable credential fields of one gateway (direct + BNPL maps). */
    public static function credentialFieldsFor(string $gateway): array
    {
        return self::CREDENTIAL_FIELDS[$gateway]
            ?? self::BNPL_CREDENTIAL_FIELDS[$gateway]
            ?? [];
    }

    /**
     * v0.30.0 — does the slug belong to the installment (BNPL) trio?
     * These live outside GATEWAYS and follow a completely different money
     * flow (monthly merchant settlement, no instant wallet credit).
     */
    public static function isBnplGateway(string $gateway): bool
    {
        return array_key_exists($gateway, self::BNPL_GATEWAYS);
    }

    /**
     * BNPL gateways the ADMIN turned on (enabled + credentialed) — the
     * first layer of the checkout filter; the SELLER opt-ins narrow it
     * further (see bnplGatewaysForSellers).
     *
     * @return array<string, string> key → Persian label
     */
    public static function availableBnplGateways(): array
    {
        $available = [];

        foreach (self::BNPL_GATEWAYS as $key => $label) {
            if (self::isEnabled($key) && self::hasCredentials($key)) {
                $available[$key] = $label;
            }
        }

        return $available;
    }

    /**
     * The BNPL surcharge ONE shop adds for a gateway (0 when not opted in).
     * v0.32.0 — checkout lists a gateway when at least ONE seller opted in
     * (see CheckoutPage::bnplIssues for the eligibility analysis); this fee
     * is always computed on the shop's ELIGIBLE goods, so excluded lines
     * never carry a surcharge.
     */
    public static function bnplFeeForShop(string $gateway, ?int $sellerId, int $itemsTotal): int
    {
        $setting = SellerBnplSetting::forSeller($gateway, $sellerId);

        return ($setting && $setting->enabled) ? $setting->feeFor($itemsTotal) : 0;
    }

    /** How many installments the product-page banner/checkout hint shows (۲–۱۲). */
    public static function bnplInstallments(string $gateway): int
    {
        $value = (int) Setting::get("payment.{$gateway}.installments", '4');

        return ($value >= 2 && $value <= 12) ? $value : 4;
    }

    /**
     * The day of month the shop deposits BNPL settlements to seller wallets
     * (۱–۲۹ — تیرماه هیچ‌وقت روز ۳۰ ندارد). The cron deposits every pending
     * settlement whose due date (the NEXT occurrence of this day after the
     * purchase) has arrived; «خرید ۲۵ ماه → تسویه سرِ ماه».
     */
    public static function bnplSettlementDay(string $gateway): int
    {
        $value = (int) Setting::get("payment.{$gateway}.settlement_day", '1');

        return ($value >= 1 && $value <= 29) ? $value : 1;
    }

    /**
     * v0.31.0 — the settlement FREQUENCY of a BNPL gateway: every 1, 2 or 3
     * months (هر ماه / هر ۲ ماه / هر ۳ ماه). The due date of a settlement is
     * the next occurrence of the settlement day AFTER the purchase, pushed
     * (frequency − 1) months further — a 2-month policy on day 10 settles a
     * purchase of day 12 on the 10th of the month AFTER next.
     */
    public static function bnplSettlementFrequency(string $gateway): int
    {
        $value = (int) Setting::get("payment.{$gateway}.settlement_frequency_months", '1');

        return in_array($value, [1, 2, 3], true) ? $value : 1;
    }

    /**
     * v0.31.0 — the settlement MODE of a BNPL gateway: «auto» rides the
     * daily cron (bnpl:settle deposits every due row of the gateway) while
     * «manual» leaves EVERY row for the admin's «واریز دستی» in the
     * تسویه اقساطی section. Manual-mode rows show a «دستی» hint so the
     * admin knows the cron will never touch them.
     */
    public static function bnplSettlementMode(string $gateway): string
    {
        $value = (string) Setting::get("payment.{$gateway}.settlement_mode", 'auto');

        return $value === 'manual' ? 'manual' : 'auto';
    }

    /** Persian label of the settlement mode (چیپ‌های تنظیمات و جدول تسویه). */
    public static function bnplSettlementModeLabel(string $gateway): string
    {
        return self::bnplSettlementMode($gateway) === 'manual' ? 'دستی' : 'خودکار';
    }

    /** «هر ماه / هر ۲ ماه / هر ۳ ماه» — the frequency chip of the policy line. */
    public static function bnplSettlementFrequencyLabel(string $gateway): string
    {
        return match (self::bnplSettlementFrequency($gateway)) {
            2 => 'هر ۲ ماه یک‌بار',
            3 => 'هر ۳ ماه یک‌بار',
            default => 'هر ماه',
        };
    }

    /** The full policy line shown to sellers/admins: «هر ماه، روزِ ۱ — خودکار». */
    public static function bnplSettlementPolicyLabel(string $gateway): string
    {
        return self::bnplSettlementFrequencyLabel($gateway).', روزِ '
            .Fmt::fa(self::bnplSettlementDay($gateway)).' — '
            .self::bnplSettlementModeLabel($gateway);
    }

    /** v0.31.0 — minimum GOODS amount a cart needs for a BNPL gateway (۰ = بدون محدودیت). */
    public static function bnplMinAmount(string $gateway): int
    {
        $value = (int) Setting::get("payment.{$gateway}.min_amount", '0');

        return max(0, $value);
    }

    /** v0.31.0 — maximum GOODS amount a BNPL gateway accepts (۰ = بی‌نهایت). */
    public static function bnplMaxAmount(string $gateway): int
    {
        $value = (int) Setting::get("payment.{$gateway}.max_amount", '0');

        return max(0, $value);
    }

    /**
     * v0.31.0 — is this GOODS total inside the gateway's min/max window?
     * The BNPL gateways carry real credit limits (e.g. دیجی‌پی تا ۵۰ میلیون
     * تومان) — a cart outside the window never shows the gateway at
     * checkout and placeOrder re-checks it server-side.
     */
    public static function bnplAmountEligible(string $gateway, int $itemsTotal): bool
    {
        $min = self::bnplMinAmount($gateway);
        $max = self::bnplMaxAmount($gateway);

        if ($min > 0 && $itemsTotal < $min) {
            return false;
        }

        return ! ($max > 0 && $itemsTotal > $max);
    }

    /**
     * Mock mode of a BNPL gateway — rides the vendor LOCAL test driver so
     * the whole installment flow is testable end-to-end without a merchant
     * contract. SECURITY: the flag only ever bites inside local
     * environments (fail-closed in production, exactly like the local
     * gateway itself).
     */
    public static function bnplMockEnabled(string $gateway): bool
    {
        return self::isBnplGateway($gateway)
            && self::localGatewayAllowed()
            && Setting::get("payment.{$gateway}.mock", '0') === '1';
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

        foreach (self::credentialFieldsFor($gateway) as $field => $label) {
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

        /*
         * v0.30.0 — BNPL trio: an EXPLICIT map entry for every call (mock
         * ON → the vendor LOCAL test driver with its friendly sandbox page
         * labels; OFF → the real driver). Mirrors the split swap pattern:
         * the config repository lives for the whole request, so the swap
         * must always be re-stated.
         */
        if (self::isBnplGateway($gateway)) {
            $mock = self::bnplMockEnabled($gateway);

            config()->set(
                'payment.map.'.$gateway,
                $mock ? Local::class : self::BNPL_VENDOR_DRIVER_MAP[$gateway],
            );

            if ($mock) {
                // the sandbox page renders through the LOCAL form — borrow
                // its labels/description (callbackUrl is overridden per-order
                // by ->callbackUrl() AFTER via(), so the local relative URL
                // here is harmless), then re-brand the page with the BNPL
                // gateway's own name so the sandbox reads «اسنپ‌پی (شبیه‌ساز)»
                $label = self::BNPL_GATEWAYS[$gateway];

                $drivers[$gateway] = array_merge(
                    $drivers[$gateway] ?? [],
                    (array) config('payment.drivers.local'),
                    ['callbackUrl' => config('payment.drivers.'.$gateway.'.callbackUrl', '/shop/payment/callback')],
                    [
                        'title' => $label.' (شبیه‌ساز)',
                        'description' => 'شبیه‌ساز درگاه اقساطی '.$label.' — قسط اول هنگام خرید پرداخت می‌شود؛ هیچ پول واقعی جابه‌جا نمی‌شود.',
                    ],
                );

                config()->set('payment.drivers', $drivers);
            }
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
        // carries an unknown key. v0.30.0 — the BNPL trio is a known choice
        // too (the order row stores bnpl_gateway separately).
        $gateway = $order->gateway && array_key_exists($order->gateway, self::GATEWAYS + self::BNPL_GATEWAYS)
            ? $order->gateway
            : self::activeGateway();

        // Fail CLOSED: outside local, the test gateway never touches real orders.
        if ($gateway === 'local' && ! self::localGatewayAllowed()) {
            PaymentLog::record($order, 'local', 'purchase_blocked', null, (int) $order->total_amount, false,
                'local test gateway is disabled outside local environments');

            throw new \RuntimeException('درگاه آزمایشی روی سرور عملیاتی غیرفعال است — از پنل مدیریت یک درگاه واقعی تنظیم کنید.');
        }

        /*
         * v0.30.0 — a BNPL gateway left in MOCK mode never reaches a
         * production checkout (bnplMockEnabled already fails closed in
         * production, so a mock-flagged gateway silently falls back to its
         * REAL driver there — this guard is the loud local-only belt).
         */
        if (self::isBnplGateway($gateway)
            && Setting::get("payment.{$gateway}.mock", '0') === '1'
            && ! self::localGatewayAllowed()) {
            PaymentLog::record($order, $gateway, 'purchase_blocked', null, (int) $order->total_amount, false,
                'mock mode is local-only — refusing to sandbox a real order');

            throw new \RuntimeException('حالت آزمایشی درگاه اقساطی روی سرور عملیاتی غیرفعال است.');
        }

        // تسهیم is a DIRECT-gateway concept — installment orders always take
        // the monthly-settlement path (CAPABLE never contains BNPL slugs, but
        // keep the invariant explicit).
        $splitPlan = self::isBnplGateway($gateway) ? null : SplitPlan::forOrder($order, $gateway);

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

        if (self::isBnplGateway($gateway)) {
            self::attachBnplDetails($invoice, $gateway, $order);
        }

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
         * v0.30.0 — a BNPL gateway in MOCK mode rides the local driver and
         * gets the same sandbox page.
         */
        if ($gateway === 'local' || (self::isBnplGateway($gateway) && self::bnplMockEnabled($gateway))) {
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
     * v0.30.0 — build the payload the BNPL trio needs on the invoice:
     * customer mobile + per-shop cart lines (goods + the shop's installment
     * surcharge − its discount share + its shipping share = what the
     * customer actually finances at the gateway).
     *
     *   • snapppay — REQUIRES a cartList (vendor throws without it) and a
     *     NUMERIC transactionId (field-observed error 1005 for uuids) → we
     *     pass detail order_id = time()+order id (unique per attempt,
     *     numeric). The vendor normalizer converts TOMAN→RIAL.
     *   • digipay — providerId = invoice uuid (unique per attempt) + an
     *     optional basketDetailsDto (product codes/names → helps the
     *     gateway's BNPL approval).
     *   • torobpay — transactionId = invoice uuid + detail cartItems (our
     *     BnplTorobPay subclass converts the cartList to RIAL).
     */
    private static function attachBnplDetails(Invoice $invoice, string $gateway, Order $order): void
    {
        $attempt = (string) time(); // unique per purchase attempt (retries)

        // the customer mobile every BNPL gateway wants (09xx — SnappPay's
        // driver converts to +98 itself)
        $phone = (string) $order->delivery_phone;

        if ($phone !== '') {
            $invoice->detail('phone', $phone);
        }

        // per-shop cart lines: items + the shop's BNPL fee + its shipping −
        // its proportional discount share (sums to orders.total_amount)
        $carts = self::bnplCartLines($order);

        if ($gateway === 'snapppay') {
            $invoice->detail('order_id', $attempt.$order->id); // NUMERIC, unique per attempt

            $invoice->detail('cartList', array_map(static fn (array $cart): array => [
                'cartId' => $cart['shop_id'],
                'totalAmount' => $cart['total'],
                'shippingAmount' => $cart['shipping'],
                'taxAmount' => 0,
                'isShipmentIncluded' => $cart['shipping'] > 0,
                'isTaxIncluded' => true,
                'cartItems' => $cart['items'],
            ], $carts));

            return;
        }

        if ($gateway === 'digipay') {
            $invoice->uuid('order-'.$order->id.'-'.$attempt);

            $items = [];

            foreach ($carts as $cart) {
                foreach ($cart['items'] as $item) {
                    $items[] = [
                        'productCode' => (string) $item['id'],
                        'brand' => (string) ($cart['shop_name'] ?? 'InstaShop'),
                        'productType' => 1, // durable (1 durable, 2 consumable, 3 service)
                        'count' => max(1, (int) $item['count']),
                    ];
                }
            }

            $invoice->detail('basketDetailsDto', [
                'basketId' => 'basket-'.$order->id.'-'.$attempt,
                'items' => $items,
            ]);

            return;
        }

        // torobpay — flat cartItems detail; the driver builds cartList and
        // converts everything to RIAL (see BnplTorobPay)
        $invoice->uuid('order-'.$order->id.'-'.$attempt);

        $invoice->detail('cartItems', array_merge(...array_map(
            static fn (array $cart): array => $cart['items'],
            $carts,
        )));
        $invoice->detail('shipping_amount', (int) $order->shipping_cost);
        $invoice->detail('is_shipment_included', (int) $order->shipping_cost > 0);
    }

    /**
     * Per-shop lines of a BNPL order for the gateway payloads — each cart
     * carries its goods total, the shop's installment surcharge (orders
     * .bnpl_fees map), its shipping share and its proportional discount
     * share, so Σ cart totals == orders.total_amount exactly.
     *
     * @return list<array{shop_id: int, shop_name: string, items_total: int, fee: int, shipping: int, discount: int, total: int, items: list<array{id: int, name: string, count: int, amount: int, category: string}>}>
     */
    private static function bnplCartLines(Order $order): array
    {
        $lines = OrderItem::query()
            ->where('order_id', $order->id)
            ->with(['product:id,shop_id,name,category_id'])
            ->get();

        $fees = (array) ($order->bnpl_fees ?? []);
        $grand = max(1, (int) $lines->sum(fn ($line) => $line->unit_price * $line->quantity));

        return $lines
            ->groupBy('shop_id')
            ->map(function ($shopLines, $shopId) use ($fees, $grand, $order): array {
                $itemsTotal = (int) $shopLines->sum(fn ($line) => $line->unit_price * $line->quantity);
                $fee = (int) ($fees[(string) $shopId] ?? $fees[(int) $shopId] ?? 0);

                $shipping = 0;

                foreach ((array) ($order->shipments ?? []) as $entry) {
                    if ((int) ($entry['shop_id'] ?? 0) === (int) $shopId) {
                        $shipping = max(0, (int) ($entry['cost'] ?? 0));

                        break;
                    }
                }

                $discount = (int) floor(((int) $order->discount_amount) * $itemsTotal / $grand);
                $shop = $shopLines->first()?->product?->shop;

                $items = $shopLines->map(static fn ($line): array => [
                    'id' => (int) $line->product_id,
                    'name' => mb_substr((string) $line->product_name, 0, 60),
                    'count' => max(1, (int) $line->quantity),
                    'amount' => (int) $line->unit_price,
                    'category' => (string) ($line->product?->category?->name ?? 'سایر'),
                ])->values()->all();

                return [
                    'shop_id' => (int) $shopId,
                    'shop_name' => (string) ($shop->name ?? ''),
                    'items_total' => $itemsTotal,
                    'fee' => $fee,
                    'shipping' => $shipping,
                    'discount' => $discount,
                    'total' => max(0, $itemsTotal + $fee + $shipping - $discount),
                    'items' => $items,
                ];
            })
            ->values()
            ->all();
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
         *
         * v0.30.0 — a BNPL gateway in MOCK mode rides the very same local
         * driver, so it gets the same re-injection (and stays local-only).
         */
        $mockBnpl = self::isBnplGateway($gateway) && self::bnplMockEnabled($gateway);

        if (($gateway === 'local' || $mockBnpl)
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

        /*
         * v0.30.0 — TorobPay echoes the financed amount (RIAL) in its verify
         * response; same fail-closed check as Zibal (the app convention is
         * TOMAN → ÷10). SnappPay's verify response carries no reliable
         * amount — its gateway-side check already bound the token to the
         * purchase amount.
         */
        if ($gateway === 'torobpay') {
            $details = (array) $receipt->getDetails();
            $paidRial = (int) ($details['response']['amount'] ?? $details['amount'] ?? 0);

            if ($paidRial > 0 && (int) round($paidRial / 10) < (int) $order->total_amount) {
                PaymentLog::record(
                    $order,
                    $gateway,
                    'verify_amount_mismatch',
                    (string) $order->gateway_ref,
                    (int) $order->total_amount,
                    false,
                    'torobpay paid amount '.$paidRial.' rial is BELOW the order total '.(int) $order->total_amount.' toman',
                );

                throw new \RuntimeException('مبلغ پرداخت‌شده با مبلغ سفارش مطابقت ندارد — تراکنش تأیید نشد.');
            }
        }

        // BNPL post-verify handshake (SnappPay settle / Digipay deliver) —
        // operational, NEVER fails the already-verified payment.
        self::afterBnplVerify($order, $gateway);

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
     * v0.30.0 — the BNPL post-verify handshake. Best-effort and
     * operational: a failure here must NEVER fail an already-verified
     * payment (the money is taken; the order finalizes regardless).
     *
     *   • snapppay — settle() is MANDATORY by end of day («after this step
     *     you cannot revert»); an unverified/unsettled payment is auto-
     *     released by the gateway. Called right after verify.
     *   • digipay — deliver() is MANDATORY for credit purchases
     *     (callback type 5/13); skipped for wallet/IPG flows.
     *   • torobpay — the vendor driver has no settle; SETTLE is reached
     *     through verify per the official plugin (verify → settle is the
     *     plugin's flow, verify-only leaves the order pending — nothing
     *     extra to call here until TorobPay ships a settle endpoint in the
     *     vendor package).
     */
    private static function afterBnplVerify(Order $order, string $gateway): void
    {
        if (! self::isBnplGateway($gateway) || self::bnplMockEnabled($gateway)) {
            return; // mock flow needs no gateway handshake
        }

        try {
            if ($gateway === 'snapppay') {
                $invoice = new Invoice;
                $invoice->amount($order->total_amount);
                $invoice->transactionId((string) $order->gateway_ref);

                (new SnappPay($invoice, (array) config('payment.drivers.snapppay')))->settle();

                PaymentLog::record($order, $gateway, 'bnpl_settle_ok', (string) $order->gateway_ref,
                    (int) $order->total_amount, true, 'SnappPay settle انجام شد (نهایی‌سازی تراکنش نزد درگاه)');
            }

            if ($gateway === 'digipay') {
                $type = (int) request()->input('type', 0);

                if (in_array($type, [5, 13], true)) { // 5 credit / 13 BNPL — deliver is mandatory
                    $trackingCode = (string) request()->input('trackingCode', $order->gateway_ref);

                    $invoice = new Invoice;
                    $invoice->amount($order->total_amount);
                    $invoice->detail('type', $type);
                    $invoice->detail('trackingCode', $trackingCode);
                    $invoice->detail('invoiceNumber', str_pad((string) $order->id, 5, '0', STR_PAD_LEFT).'-'.now()->format('ymd'));
                    $invoice->detail('deliveryDate', now()->toDateString());
                    $invoice->detail('products', $order->items->map(
                        fn ($item): string => mb_substr((string) $item->product_name, 0, 80),
                    )->values()->all());

                    (new Digipay($invoice, (array) config('payment.drivers.digipay')))->deliver();

                    PaymentLog::record($order, $gateway, 'bnpl_deliver_ok', $trackingCode,
                        (int) $order->total_amount, true, 'دیجی‌پی deliver ثبت شد (سفارش تحویل‌شده اعلام شد)');
                }
            }
        } catch (Throwable $e) {
            PaymentLog::record(
                $order,
                $gateway,
                $gateway === 'snapppay' ? 'bnpl_settle_failed' : 'bnpl_deliver_failed',
                (string) $order->gateway_ref,
                (int) $order->total_amount,
                false,
                'پرداخت وریفای شد اما handshake نهایی درگاه ناموفق بود: '.$e->getMessage(),
            );
        }
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
