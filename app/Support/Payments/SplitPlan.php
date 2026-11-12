<?php

namespace App\Support\Payments;

use App\Models\BankCard;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Seller;
use App\Models\Setting;
use App\Models\Shop;
use App\Support\Phone;

/**
 * پرداخت تسهیمی (split payment) — سهم فروشنده مستقیماً از درگاه به
 * مقصد بانکی او می‌رود، نه از طریق کیف پول.
 *
 * RESEARCH (v0.27.0 / v0.28.0) — among the gateways wired into this
 * project, exactly three expose a real, documented split/settlement API
 * at PURCHASE time:
 *
 *   • payping       → POST /v3/pay/shared with items[] = {amount (toman),
 *                     userIdentity, description} — the recipient is the
 *                     seller's registered PayPing identity (mobile).
 *   • asanpardakht  → the sharing/settlement service (تسهیم) of the REST
 *                     IPG — the Token request rides the seller slices;
 *                     the vendor's own error table (482 «settlement slices
 *                     does not match total amount», 483 «unregistered iban»,
 *                     491 «too many settlement destinations», 578 «no
 *                     default sharing») proves the server-side API.
 *   • sep           → the SEP (سامان) token request documented in the
 *                     official guide «راهنمای تراکنش تسویه به چند حساب»
 *                     (v1.0, خرداد ۱۴۰۱): SettlementIbanInfo[] = {IBAN,
 *                     Amount (ریال), PurchaseID} — max 9 destinations,
 *                     PurchaseID = 30 zeros for normal (non-government)
 *                     settlements.
 *
 * Every other gateway (novinopay, sadad, behpardakht, parsian, irankish,
 * pasargad, sepehr) has NO documented purchase-time split parameter —
 * for them the toggle is never offered and the money always takes the
 * wallet path.
 *
 * The toggle lives in the admin panel (تنظیمات درگاه) as
 * settings «payment.{gateway}.split_enabled».
 *
 * BANK-CARD GATE (v0.28.0 — client request): a split purchase only
 * happens when EVERY seller of the order has completed their bank-cards
 * section (حداقل یک کارت ثبت‌شده) — a seller without any card always
 * takes the wallet path, which per the ONE-path rule below means the
 * WHOLE order goes through the wallet.
 *
 * FAIL-SAFE: PaymentService falls back to the standard flow (wallet
 * settlement) whenever a split purchase dies before creating a gateway
 * transaction, or when ANY shop of the order has no split destination —
 * a partial split would double-track the money, so the whole order
 * always takes ONE path.
 */
final class SplitPlan
{
    /** Gateway → human explanation shown in the admin panel. */
    public const CAPABLE = [
        'payping' => 'از طریق «پرداخت اشتراکی» پی‌پینگ (v3/pay/shared) — سهم هر فروشگاه به هویت ثبت‌شدهٔ او در پی‌پینگ (شماره موبایل فروشنده) واریز می‌شود.',
        'asanpardakht' => 'از طریق سرویس تسهیم آسان‌پرداخت در درخواست توکن — شبا هر فروشنده باید پیشاپیش در پنل آسان‌پرداخت ثبت و تأیید شده باشد.',
        'sep' => 'از طریق پارامتر SettlementIbanInfo در درخواست توکن سپ (سامان) — مستند رسمی «تراکنش تسویه به چند حساب»؛ شبای فروشندگان باید در پنل سامان برای تسویهٔ چندحسابی مجاز شده باشد و هر تراکنش حداکثر ۹ مقصد می‌پذیرد.',
    ];

    /** Driver classes swapped in at purchase time (vendor stays untouched). */
    public const SPLIT_DRIVER_MAP = [
        'payping' => Drivers\SplitPayping::class,
        'asanpardakht' => Drivers\SplitAsanpardakht::class,
        'sep' => Drivers\SplitSep::class,
    ];

    /**
     * Per-gateway ceiling on split destinations (SEP guide: «حداکثر طول
     * آرایه ۹ می‌باشد») — an order with more shops than the ceiling takes
     * the wallet path as a whole.
     */
    private const MAX_SHARES = [
        'sep' => 9,
    ];

    public static function capable(string $gateway): bool
    {
        return array_key_exists($gateway, self::CAPABLE);
    }

    /** The admin toggle: settings payment.{gateway}.split_enabled = '1'. */
    public static function enabled(string $gateway): bool
    {
        return self::capable($gateway)
            && Setting::get('payment.'.$gateway.'.split_enabled', '0') === '1';
    }

    /**
     * The split destination of a seller for the given gateway — used both
     * while building the plan and later when the paid-order report is
     * written (so both sides describe the SAME destination).
     */
    public static function sellerDestination(string $gateway, Seller $seller): ?string
    {
        return $gateway === 'payping'
            ? self::paypingIdentity($seller)
            : self::defaultSheba($seller);
    }

    /**
     * Build the split plan for an order — NULL means «the standard flow»:
     * the whole amount lands in the platform merchant account and every
     * seller's share is credited to the in-app wallet after verification
     * (WalletService::creditOrder).
     *
     * Share rules mirror WalletService exactly: base = goods total
     * (unit_price × quantity), commission = seller override (else global
     * default), payout = base − commission. Shipping cost and discount
     * codes never enter the base and stay with the platform.
     *
     * @return array{gateway: string, shares: list<array{shop_id: int, shop_name: string, seller_id: int, destination: string, amount: int, items_total: int}>}|null
     */
    public static function forOrder(Order $order, string $gateway): ?array
    {
        if (! self::enabled($gateway)) {
            return null;
        }

        /*
         * Direct query — the Order::items() relation appends orderBy('id')
         * which would poison the GROUP BY aggregate under MySQL 8's
         * ONLY_FULL_GROUP_BY (same trap WalletService documents).
         */
        $shares = OrderItem::query()
            ->where('order_id', $order->id)
            ->selectRaw('shop_id, SUM(unit_price * quantity) as total')
            ->groupBy('shop_id')
            ->get();

        $plan = [];

        foreach ($shares as $share) {
            $shop = Shop::query()->find((int) $share->shop_id);

            if (! $shop || ! $shop->seller_id) {
                return null; // orphan shop — whole order takes the wallet path
            }

            /** @var Seller $seller */
            $seller = Seller::query()->find((int) $shop->seller_id);

            if (! $seller) {
                return null;
            }

            // v0.28.0 — الزام کارت بانکی: without at least ONE registered
            // card the seller's money has no reliable bank destination →
            // the WHOLE order takes the wallet path (never a partial split).
            if (! self::sellerHasBankCard($seller)) {
                return null;
            }

            $itemsTotal = max(0, (int) $share->total);
            $commission = $itemsTotal > 0 && $seller ? $seller->commissionFor($itemsTotal) : 0;
            $payout = max(0, $itemsTotal - $commission);

            if ($payout <= 0) {
                continue; // commission swallows the whole share — nothing to split
            }

            $destination = self::sellerDestination($gateway, $seller);

            if ($destination === null || $destination === '') {
                /*
                 * ONE shop without a destination → the WHOLE order goes to
                 * the wallet path. A partial split would send one shop's
                 * money to a sheba and another's into the wallet, leaving
                 * the books irreconcilable.
                 */
                return null;
            }

            $plan[] = [
                'shop_id' => (int) $shop->id,
                'shop_name' => (string) $shop->name,
                'seller_id' => (int) $seller->id,
                'destination' => $destination,
                'amount' => $payout,
                'items_total' => $itemsTotal,
            ];
        }

        if ($plan === []) {
            return null; // nothing to split (all-commission order) — standard flow
        }

        $max = self::MAX_SHARES[$gateway] ?? null;

        if ($max !== null && count($plan) > $max) {
            return null; // gateway's split-array limit exceeded — wallet path
        }

        return [
            'gateway' => $gateway,
            'shares' => $plan,
        ];
    }

    /**
     * BANK-CARD GATE (v0.28.0): split payment is only allowed for sellers
     * who completed the bank-cards section of their panel (حداقل یک کارت).
     */
    public static function sellerHasBankCard(Seller $seller): bool
    {
        return BankCard::query()->where('seller_id', $seller->id)->exists();
    }

    /**
     * PayPing shared payment identifies the recipient by the identity the
     * seller registered at PayPing — for sellers that is their login
     * mobile in the LOCAL 09xxxxxxxxx form. The DB stores the E.164-ish
     * 989xxxxxxxxx (Phone::normalize) — convert before matching
     * (v0.28.0 BUGFIX: the raw stored value never matched ^09\d{9}$, so
     * the payping split silently ALWAYS fell back to the wallet path).
     */
    private static function paypingIdentity(Seller $seller): ?string
    {
        $local = Phone::toLocal(trim((string) $seller->phone));

        return preg_match('/^09\d{9}$/', $local) === 1 ? $local : null;
    }

    /** The seller's default bank card's sheba (falls back to any card with one). */
    private static function defaultSheba(Seller $seller): ?string
    {
        $card = BankCard::query()
            ->where('seller_id', $seller->id)
            ->where('sheba', '!=', '')
            ->whereNotNull('sheba')
            ->orderByDesc('is_default')
            ->orderBy('id')
            ->first();

        $sheba = trim((string) ($card?->sheba ?? ''));

        return $sheba !== '' ? $sheba : null;
    }
}
