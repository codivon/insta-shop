<?php

namespace App\Models;

use App\Support\Fmt;
use App\Support\JDate;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

#[Fillable([
    'customer_id', 'status', 'total_amount', 'items_count',
    'receiver_name', 'delivery_phone', 'delivery_address', 'postal_code',
    'gateway', 'gateway_ref', 'receipt_ref', 'paid_at',
])]
class Order extends Model
{
    public const STATUSES = ['pending', 'paid', 'failed', 'canceled'];

    protected function casts(): array
    {
        return [
            'total_amount' => 'integer',
            'items_count'  => 'integer',
            'paid_at'      => 'datetime',
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

    /* ─────────────────────── Creation from cart ────────────────────── */

    /**
     * Build a pending order from the customer's cart.
     * Only published & in-stock lines are taken (per-variant stock when a
     * variant was picked); quantities are capped to the current stock and
     * the picked combination is snapshotted (id + human label).
     * Returns null when nothing is purchasable.
     */
    public static function createFromCart(Customer $customer, array $delivery, string $gateway): ?self
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
                'shop_id'       => $item->product->shop_id,
                'product_id'    => $item->product->id,
                'product_name'  => $item->product->name,
                'variant_id'    => (int) $item->variant_id,
                'variant_label' => $item->variant_id > 0 ? $item->variant?->label() : null,
                'unit_price'    => $item->unitPrice(),
                'quantity'      => min((int) $item->quantity, self::lineStock($item)),
            ])
            ->values();

        if ($lines->isEmpty()) {
            return null;
        }

        return DB::transaction(function () use ($customer, $delivery, $gateway, $lines) {
            $order = static::query()->create([
                'customer_id'      => $customer->id,
                'status'           => 'pending',
                'total_amount'     => (int) $lines->sum(fn ($line) => $line['unit_price'] * $line['quantity']),
                'items_count'      => (int) $lines->sum('quantity'),
                'receiver_name'    => (string) $delivery['receiver_name'],
                'delivery_phone'   => (string) $delivery['delivery_phone'],
                'delivery_address' => (string) $delivery['delivery_address'],
                'postal_code'      => $delivery['postal_code'] ?? null,
                'gateway'          => $gateway,
            ]);

            $order->items()->createMany($lines->all());

            return $order;
        });
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
                'status'      => 'paid',
                'receipt_ref' => $receiptRef,
                'paid_at'     => now(),
            ])->save();

            ($order->customer->cart)?->items()->delete();

            // seller wallets: credit each shop's share of this paid order
            \App\Support\Wallet\WalletService::creditOrder($order);

            return true;
        });
    }

    /** Mark a pending order failed (canceled / verification error). */
    public function markFailed(): bool
    {
        if ($this->status !== 'pending') {
            return false;
        }

        return (bool) $this->forceFill(['status' => 'failed'])->save();
    }

    /* ──────────────────────────── Helpers ──────────────────────────── */

    public function statusLabel(): string
    {
        return match ($this->status) {
            'paid'    => 'پرداخت‌شده',
            'failed'  => 'پرداخت ناموفق',
            'canceled'=> 'لغو شده',
            default   => 'در انتظار پرداخت',
        };
    }

    /** Tailwind chip classes per status. */
    public function statusClasses(): string
    {
        return match ($this->status) {
            'paid'    => 'bg-emerald-50 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-400',
            'failed'  => 'bg-rose-50 text-rose-600 dark:bg-rose-950/40 dark:text-rose-400',
            'canceled'=> 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400',
            default   => 'bg-amber-50 text-amber-600 dark:bg-amber-950/40 dark:text-amber-400',
        };
    }

    /** Human-friendly gateway name for receipts. */
    public function gatewayLabel(): string
    {
        return match ($this->gateway) {
            'zarinpal'    => 'زرین‌پال',
            'idpay'       => 'آیدی‌پی',
            'zibal'       => 'زیبال',
            'behpardakht' => 'بانک ملت',
            'sep'         => 'درگاه سپ',
            default       => 'درگاه آزمایشی (محلی)',
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
