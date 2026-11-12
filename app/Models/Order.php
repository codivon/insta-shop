<?php

namespace App\Models;

use App\Support\AdminNotifier;
use App\Support\Fmt;
use App\Support\IranCalendar;
use App\Support\JDate;
use App\Support\Payments\PaymentService;
use App\Support\SellerNotifier;
use App\Support\Wallet\WalletService;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

#[Fillable([
    'customer_id', 'status', 'total_amount', 'items_count',
    'shipping_cost', 'discount_amount', 'discount_code', 'shipments', 'commissions',
    'receiver_name', 'delivery_phone', 'delivery_address', 'postal_code',
    'gateway', 'gateway_ref', 'receipt_ref', 'paid_at',
    'shipment_status', 'tracking_code', 'shipment_status_at',
    'bnpl_gateway', 'bnpl_fee', 'bnpl_fees',
])]
class Order extends Model
{
    public const STATUSES = ['pending', 'paid', 'failed', 'canceled'];

    /** Shipment lifecycle for paid orders. */
    public const SHIPMENT_STATUSES = ['none', 'processing', 'shipped', 'delivered'];

    protected function casts(): array
    {
        return [
            'total_amount' => 'integer',
            'shipping_cost' => 'integer',
            'discount_amount' => 'integer',
            'items_count' => 'integer',
            'split_payment' => 'boolean',
            'shipments' => 'array',
            'commissions' => 'array',
            'paid_at' => 'datetime',
            'shipment_status_at' => 'datetime',
            'bnpl_fee' => 'integer',
            'bnpl_fees' => 'array',
        ];
    }

    /* ─────────────────────────── Relations ─────────────────────────── */

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class)->orderBy('id');
    }

    /** Per-shop shipment slices of this order (one row per shop). */
    public function shopShipments(): HasMany
    {
        return $this->hasMany(OrderShipment::class)->orderBy('id');
    }

    /* ─────────────────────── Creation from cart ────────────────────── */

    /**
     * Build a pending order from the customer's cart.
     * Only published & in-stock lines are taken (per-variant stock when a
     * variant was picked); quantities are capped to the current stock and
     * the picked combination is snapshotted (id + human label).
     * Returns null when nothing is purchasable.
     *
     * $checkout carries the checkout decisions:
     *   gateway        → chosen payment gateway key
     *   shipments      → [{shop_id, carrier_id, carrier_title, cost}]
     *   shipping_cost  → sum of the chosen carriers' online costs
     *   discount       → ?Discount applied (its used_count is bumped here)
     *   discount_amount→ computed discount in Toman
     */
    /**
     * v0.30.0 — BNPL payload of $checkout (درگاه‌های اقساطی):
     *   bnpl_gateway → the installment gateway the customer picked
     *   bnpl_fees    → {shop_id: fee} — each seller's surcharge frozen at
     *                  checkout so the monthly settlements always match
     *                  what the customer paid
     *   bnpl_fee     → Σ bnpl_fees — rides the PRODUCTS total (never the
     *                  shipping, client request)
     */
    public static function createFromCart(Customer $customer, array $delivery, string $gateway, array $checkout = []): ?self
    {
        $cart = $customer->cart;

        if (! $cart) {
            return null;
        }

        $lines = $cart->items()->with(['product', 'variant'])->get()
            ->filter(fn ($item) => $item->product
                && $item->product->status === 'published'
                && self::lineStock($item) > 0)
            ->map(fn ($item) => [
                'shop_id' => $item->product->shop_id,
                'product_id' => $item->product->id,
                'product_name' => $item->product->name,
                'variant_id' => (int) $item->variant_id,
                'variant_label' => $item->variant_id > 0 ? $item->variant?->label() : null,
                'unit_price' => $item->unitPrice(),
                'quantity' => min((int) $item->quantity, self::lineStock($item)),
            ])
            ->values();

        if ($lines->isEmpty()) {
            return null;
        }

        $shipping = max(0, (int) ($checkout['shipping_cost'] ?? 0));
        $discount = $checkout['discount'] ?? null;
        $discountAmount = $discount instanceof Discount
            ? max(0, (int) ($checkout['discount_amount'] ?? 0))
            : 0;

        /* v0.30.0 — the installment surcharge (validated server-side by the
         * caller): only a KNOWN BNPL gateway carries it, the per-shop map
         * is re-clamped to non-negative ints and the grand total recomputed
         * from it (never trusted from the wire). */
        $bnplGateway = array_key_exists((string) ($checkout['bnpl_gateway'] ?? ''), PaymentService::BNPL_GATEWAYS)
            ? (string) $checkout['bnpl_gateway']
            : null;

        $bnplFees = [];

        foreach ((array) ($checkout['bnpl_fees'] ?? []) as $shopId => $fee) {
            $bnplFees[(int) $shopId] = max(0, (int) $fee);
        }

        $bnplFeeTotal = array_sum($bnplFees);

        $order = DB::transaction(function () use ($customer, $delivery, $gateway, $lines, $shipping, $discount, $discountAmount, $bnplGateway, $bnplFees, $bnplFeeTotal, $checkout) {
            /*
             * SECURITY (v0.21.0): the discount is CLAIMED atomically while
             * holding the discount row lock. The old code blindly did
             * increment('used_count') after the checkout-time evaluation —
             * N concurrent placeOrder calls all evaluated BEFORE any of
             * them committed, so a code with quantity=1 / quantity_per_user=1
             * could yield N discounted orders. Every creator now serializes
             * on the row lock and the caps are re-checked under it; a lost
             * claim simply drops the discount (order proceeds undiscounted).
             */
            $claimedDiscount = null;

            if ($discount instanceof Discount) {
                $locked = Discount::query()->whereKey($discount->id)->lockForUpdate()->first();

                $stillValid = $locked !== null
                    && $locked->published
                    && ($locked->starts_at === null || ! now()->lt($locked->starts_at))
                    && ($locked->ends_at === null || ! now()->gt($locked->ends_at))
                    && ($locked->quantity <= 0 || $locked->used_count < $locked->quantity);

                // per-user quota — re-checked UNDER the row lock (all creators
                // for this code serialize here, so the count below sees the
                // previous creator's committed order row → no per-user race)
                if ($stillValid && $locked->quantity_per_user > 0) {
                    $used = static::query()
                        ->where('customer_id', $customer->id)
                        ->whereIn('status', ['pending', 'paid'])
                        ->where('discount_code', $locked->code)
                        ->count();

                    $stillValid = $used < $locked->quantity_per_user;
                }

                if ($stillValid) {
                    $locked->increment('used_count');
                    $claimedDiscount = $locked;
                }
            }

            $appliedAmount = $claimedDiscount !== null ? $discountAmount : 0;

            $order = static::query()->create([
                'customer_id' => $customer->id,
                'status' => 'pending',
                'total_amount' => max(0, (int) $lines->sum(fn ($line) => $line['unit_price'] * $line['quantity']) + $bnplFeeTotal + $shipping - $appliedAmount),
                'shipping_cost' => $shipping,
                'discount_amount' => $appliedAmount,
                'discount_code' => $claimedDiscount?->code,
                'shipments' => array_values((array) ($checkout['shipments'] ?? [])),
                'items_count' => (int) $lines->sum('quantity'),
                'receiver_name' => (string) $delivery['receiver_name'],
                'delivery_phone' => (string) $delivery['delivery_phone'],
                'delivery_address' => (string) $delivery['delivery_address'],
                'postal_code' => $delivery['postal_code'] ?? null,
                'gateway' => $gateway,
                'bnpl_gateway' => $bnplGateway,
                'bnpl_fees' => $bnplFees !== [] ? $bnplFees : null,
                'bnpl_fee' => $bnplFeeTotal,
            ]);

            $order->items()->createMany($lines->all());
            $order->ensureShopShipments();

            return $order;
        });

        // seller notifications AFTER the transaction (Web Push = HTTP calls
        // must never hold a DB write lock): «سفارش جدید شامل محصولات شما»
        SellerNotifier::toShops(
            $lines->pluck('shop_id')->all(),
            'order.new',
            'سفارش جدید '.Fmt::fa($order->id).' ثبت شد 🛍️',
            Fmt::fa($lines->sum('quantity')).' قلم کالا از فروشگاه شما در این سفارش وجود دارد.',
            route('seller.orders'),
            'bag',
            $order,
        );

        return $order;
    }

    /* ──────────────────────── State transitions ────────────────────── */

    /**
     * Mark the order paid, decrement product stock and empty the cart.
     * Idempotent: only a pending order can be finalized (double callbacks
     * from the gateway are safely ignored).
     */
    public function finalizePaid(string $receiptRef): bool
    {
        return (bool) DB::transaction(function () use ($receiptRef) {
            $order = static::query()->whereKey($this->id)->lockForUpdate()->first();

            if (! $order || $order->status !== 'pending') {
                return false;
            }

            foreach ($order->items as $item) {
                if ($item->variant_id > 0) {
                    ProductVariant::query()
                        ->whereKey($item->variant_id)
                        ->where('stock', '>=', $item->quantity)
                        ->decrement('stock', $item->quantity);
                } else {
                    Product::query()
                        ->whereKey($item->product_id)
                        ->where('stock', '>=', $item->quantity)
                        ->decrement('stock', $item->quantity);
                }
            }

            // Variant items drain per-variant stock → refresh the denormalized
            // product price/stock aggregates once per affected product.
            $order->items
                ->filter(fn ($item) => $item->variant_id > 0)
                ->map(fn ($item) => $item->product_id)
                ->unique()
                ->each(function ($productId) {
                    $product = Product::query()->find($productId);

                    $product?->syncAggregates();
                });

            $order->forceFill([
                'status' => 'paid',
                'receipt_ref' => $receiptRef,
                'paid_at' => now(),
            ])->save();

            ($order->customer->cart)?->items()->delete();

            // seller wallets: credit each shop's share of this paid order
            WalletService::creditOrder($order);

            // notify every shop's seller: «پرداخت شد — آماده پردازش/ارسال»
            SellerNotifier::toShops(
                $order->items->pluck('shop_id')->all(),
                'order.paid',
                'پرداخت سفارش '.Fmt::fa($order->id).' موفق شد ✅',
                'مبلغ '.Fmt::money($order->total_amount).' پرداخت شد — سفارش آماده پردازش و ارسال است.',
                route('seller.orders'),
                'circle-check',
                $order,
            );

            return true;
        });
    }

    /**
     * Mark a pending order failed (canceled / verification error).
     *
     * SECURITY (v0.21.0): the status is re-read from the DB under a row
     * lock — the old check trusted the stale in-memory instance, so a
     * duplicate gateway callback racing finalizePaid could flip a PAID
     * order to failed (stock/wallet already settled). Also: a failed
     * order RELEASES the discount quota it reserved at creation, so
     * abandoned checkouts no longer burn a seller's global cap.
     */
    public function markFailed(): bool
    {
        return (bool) DB::transaction(function () {
            $fresh = static::query()->whereKey($this->id)->lockForUpdate()->first();

            if (! $fresh || $fresh->status !== 'pending') {
                return false;
            }

            $fresh->forceFill(['status' => 'failed'])->save();

            if ((string) $fresh->discount_code !== '') {
                Discount::query()
                    ->where('code', $fresh->discount_code)
                    ->where('used_count', '>', 0)
                    ->decrement('used_count');
            }

            return true;
        });
    }

    /* ──────────────────────────── Helpers ──────────────────────────── */

    public function statusLabel(): string
    {
        return match ($this->status) {
            'paid' => 'پرداخت‌شده',
            'failed' => 'پرداخت ناموفق',
            'canceled' => 'لغو شده',
            default => 'در انتظار پرداخت',
        };
    }

    /** Tailwind chip classes per status. */
    public function statusClasses(): string
    {
        return match ($this->status) {
            'paid' => 'bg-emerald-50 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-400',
            'failed' => 'bg-rose-50 text-rose-600 dark:bg-rose-950/40 dark:text-rose-400',
            'canceled' => 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400',
            default => 'bg-amber-50 text-amber-600 dark:bg-amber-950/40 dark:text-amber-400',
        };
    }

    /** Human-friendly gateway name for receipts. */
    public function gatewayLabel(): string
    {
        // PaymentService::GATEWAYS + BNPL_GATEWAYS are the single source of
        // truth (v0.30.0 — the BNPL trio renders everywhere automatically).
        return PaymentService::GATEWAYS[$this->gateway]
            ?? PaymentService::BNPL_GATEWAYS[$this->gateway]
            ?? match ($this->gateway) {
                'idpay' => 'آیدی‌پی', // legacy rows from before multipay v3
                null, '' => 'درگاه آزمایشی (محلی)',
                default => (string) $this->gateway,
            };
    }

    /** v0.30.0 — was this order paid through an installment (BNPL) gateway? */
    public function isBnpl(): bool
    {
        return (string) $this->bnpl_gateway !== ''
            && array_key_exists((string) $this->bnpl_gateway, PaymentService::BNPL_GATEWAYS);
    }

    /* ───────────────────────── Shipment lifecycle ───────────────────── */

    /**
     * Make sure every shop that has items in this order owns an
     * order_shipments slice (legacy orders get backfilled lazily).
     */
    public function ensureShopShipments(): void
    {
        $existing = OrderShipment::query()->where('order_id', $this->id)->pluck('shop_id')->all();

        $shares = OrderItem::query()
            ->where('order_id', $this->id)
            ->selectRaw('shop_id, SUM(unit_price * quantity) as items_total')
            ->groupBy('shop_id')
            ->get();

        $grand = max(1, (int) $shares->sum('items_total'));

        foreach ($shares as $share) {
            $shopId = (int) $share->shop_id;

            if (in_array($shopId, array_map('intval', $existing), true)) {
                continue;
            }

            // per-shop carrier cost + customer-picked delivery day from the
            // shipments JSON snapshot (pay-on-delivery carriers are NOT
            // charged online → 0)
            $shipping = 0;
            $deliveryDate = null;

            foreach ((array) ($this->shipments ?? []) as $entry) {
                if ((int) ($entry['shop_id'] ?? 0) === $shopId) {
                    $carrier = Carrier::query()->find((int) ($entry['carrier_id'] ?? 0));
                    $shipping = ($carrier && $carrier->isPayOnDelivery())
                        ? 0
                        : max(0, (int) ($entry['cost'] ?? 0));

                    $deliveryDate = IranCalendar::isAllowedPick(
                        $carrier?->selectableDeliveryDays() ?? [],
                        isset($entry['delivery_date']) ? (string) $entry['delivery_date'] : null,
                    ) ? (string) $entry['delivery_date'] : null;

                    break;
                }
            }

            // proportional share of the order-level discount code
            $discount = (int) floor(((int) $this->discount_amount) * ((int) $share->items_total) / $grand);
            $itemsTotal = max(0, (int) $share->items_total);

            OrderShipment::query()->create([
                'order_id' => $this->id,
                'shop_id' => $shopId,
                'items_total' => $itemsTotal,
                'shipping_cost' => $shipping,
                'discount_amount' => $discount,
                'total' => max(0, $itemsTotal + $shipping - $discount),
                'delivery_date' => $deliveryDate,
            ]);
        }
    }

    /** The shipment slice of one shop (row is created on demand). */
    public function shipmentForShop(int $shopId): OrderShipment
    {
        $this->ensureShopShipments();

        return OrderShipment::query()
            ->where('order_id', $this->id)
            ->where('shop_id', $shopId)
            ->first()
            ?? new OrderShipment(['order_id' => $this->id, 'shop_id' => $shopId]);
    }

    /**
     * Transition the shipment state of ONE shop's slice on a PAID order.
     * «در حال ارسال» requires a post tracking code.
     */
    public function setShopShipment(int $shopId, string $status, ?string $trackingCode = null): bool
    {
        // re-read status from DB (the in-memory instance can be stale —
        // e.g. finalizePaid mutates a fresh clone inside its transaction)
        if (static::query()->whereKey($this->id)->value('status') !== 'paid'
            || ! in_array($status, OrderShipment::STATUSES, true)) {
            return false;
        }

        if (! $this->hasShopItems($shopId)) {
            return false;
        }

        $trackingCode = trim((string) $trackingCode);

        if ($status === 'shipped' && $trackingCode === '') {
            return false;
        }

        $shipment = $this->shipmentForShop($shopId);

        if ($shipment->isCanceled()) {
            return false;
        }

        $shipment->forceFill([
            'shipment_status' => $status,
            'tracking_code' => $trackingCode !== '' ? $trackingCode : ($status === 'none' ? null : $shipment->tracking_code),
            'shipment_status_at' => now(),
        ])->save();

        $this->syncOverallShipment();

        return true;
    }

    /**
     * Derive the overall orders.shipment_status from the shop slices so
     * existing lists/labels keep working: delivered only when EVERY active
     * slice is delivered, else the furthest state in progress.
     */
    public function syncOverallShipment(): void
    {
        $rows = OrderShipment::query()->where('order_id', $this->id)->get();

        if ($rows->isEmpty()) {
            return;
        }

        $active = $rows->reject(fn (OrderShipment $row) => $row->isCanceled());

        $status = 'none';

        if ($active->isNotEmpty()) {
            $status = $active->every(fn (OrderShipment $row) => $row->shipment_status === 'delivered')
                ? 'delivered'
                : ($active->contains(fn (OrderShipment $row) => $row->shipment_status === 'shipped')
                    ? 'shipped'
                    : ($active->contains(fn (OrderShipment $row) => $row->shipment_status === 'processing') ? 'processing' : 'none'));
        }

        $this->forceFill(['shipment_status' => $status, 'shipment_status_at' => now()])->save();

        // keep a representative tracking code for legacy displays
        $tracking = $rows->first(fn (OrderShipment $row) => (string) ($row->tracking_code ?? '') !== '')?->tracking_code;

        if ($tracking) {
            $this->forceFill(['tracking_code' => $tracking])->save();
        }
    }

    /**
     * Cancel ONE shop's slice of a paid order (seller or admin action):
     * goods return to stock, the seller wallet pays back its credited
     * share and refund_amount records what must go back to the customer.
     */
    public function cancelShopSlice(int $shopId, string $reason, string $by): bool
    {
        if (static::query()->whereKey($this->id)->value('status') !== 'paid' || ! $this->hasShopItems($shopId)) {
            return false;
        }

        $shipment = $this->shipmentForShop($shopId);

        if ($shipment->isCanceled() || $shipment->shipment_status === 'delivered') {
            return false;
        }

        $reason = trim($reason) !== '' ? trim($reason) : 'بدون دلیل ثبت‌شده';

        DB::transaction(function () use ($shopId, $reason, $by, $shipment) {
            // 1) goods go back to stock (they left at finalizePaid)
            $items = $this->items()->where('shop_id', $shopId)->get();

            foreach ($items as $item) {
                if ($item->variant_id > 0) {
                    ProductVariant::query()->whereKey($item->variant_id)->increment('stock', $item->quantity);
                } elseif ($item->product_id) {
                    Product::query()->whereKey($item->product_id)->increment('stock', $item->quantity);
                }
            }

            $items->filter(fn ($item) => $item->variant_id > 0)
                ->map(fn ($item) => $item->product_id)
                ->unique()
                ->each(fn ($productId) => Product::query()->find($productId)?->syncAggregates());

            // 2) slice → canceled, refund snapshot frozen
            $shipment->forceFill([
                'canceled_at' => now(),
                'cancel_reason' => mb_substr($reason, 0, 300),
                'canceled_by' => $by === 'admin' ? 'admin' : 'seller',
                'refund_amount' => (int) $shipment->total,
                'shipment_status' => 'none',
                'shipment_status_at' => now(),
            ])->save();

            // 3) seller wallet pays back the credited payout for this slice
            $shop = Shop::query()->find($shopId);

            if ($shop && $shop->seller_id) {
                $seller = Seller::query()->find($shop->seller_id);
                $payout = max(0, (int) $shipment->items_total - ($seller?->commissionFor((int) $shipment->items_total) ?? 0));

                if ($payout > 0) {
                    $wallet = Wallet::ensureForSeller((int) $shop->seller_id);
                    $wallet->decrement('balance', $payout);

                    WalletTransaction::query()->create([
                        'wallet_id' => $wallet->id,
                        'type' => WalletTransaction::TYPE_WITHDRAWAL,
                        'amount' => $payout,
                        'commission' => 0,
                        'status' => WalletTransaction::STATUS_DONE,
                        'order_id' => $this->id,
                        'description' => 'لغو سهم فروشگاه از سفارش #'.WalletService::orderCode($this).' — بازگشت وجه به مشتری',
                    ]);
                }
            }
        });

        $this->syncOverallShipment();

        // notify the seller of this shop
        $shop = Shop::query()->find($shopId);

        if ($shop?->seller_id) {
            SellerNotifier::notify(
                (int) $shop->seller_id,
                'order.canceled',
                'سهم فروشگاه شما از سفارش '.Fmt::fa($this->id).' لغو شد ⛔',
                'دلیل: '.$reason.' — مبلغ '.Fmt::money((int) $shipment->total).' باید به مشتری بازگردانده شود.',
                route('seller.orders'),
                'circle-x',
                $this,
            );
        }

        // and the admin panel
        AdminNotifier::notify(
            'order.canceled',
            'لغو سهم فروشگاه از سفارش '.Fmt::fa($this->id),
            'توسط '.($by === 'admin' ? 'مدیر' : 'فروشنده').' — دلیل: '.$reason,
            route('admin.orders.show', $this->id),
            'circle-x',
            $this,
        );

        return true;
    }

    /** Seller-side guard: does any order item belong to this shop? */
    public function hasShopItems(int $shopId): bool
    {
        return $this->items()->where('shop_id', $shopId)->exists();
    }

    /** Persian label of the derived overall shipment status. */
    public function shipmentStatusLabel(): string
    {
        return match ($this->shipment_status) {
            'processing' => 'در حال پردازش',
            'shipped' => 'در حال ارسال',
            'delivered' => 'تحویل شد',
            default => 'پردازش نشده',
        };
    }

    /** Tailwind chip classes for the derived overall shipment status. */
    public function shipmentStatusClasses(): string
    {
        return match ($this->shipment_status) {
            'processing' => 'bg-sky-50 text-sky-600 dark:bg-sky-950/40 dark:text-sky-400',
            'shipped' => 'bg-violet-50 text-violet-600 dark:bg-violet-950/40 dark:text-violet-400',
            'delivered' => 'bg-emerald-50 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-400',
            default => 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400',
        };
    }

    /** Persian (Jalali) creation date, e.g. «۱۴۰۵/۰۸/۰۹ — ۲۳:۲۱». */
    public function dateFa(): string
    {
        return $this->created_at ? JDate::dateTime($this->created_at) : '';
    }

    public function paidAtFa(): string
    {
        return $this->paid_at ? JDate::dateTime($this->paid_at) : '';
    }

    /** Current purchasable stock of a cart line (variant-aware; 0 when the combination is gone). */
    public static function lineStock(CartItem $item): int
    {
        if ((int) $item->variant_id > 0) {
            $variant = $item->variant;

            return ($variant && $variant->status === 'active') ? (int) $variant->stock : 0;
        }

        // Downloadable goods are never stock-limited.
        if ($item->product && $item->product->type === Product::TYPE_DOWNLOADABLE) {
            return 99;
        }

        return (int) ($item->product->stock ?? 0);
    }
}
