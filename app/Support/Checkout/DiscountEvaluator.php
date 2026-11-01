<?php

namespace App\Support\Checkout;

use App\Models\Customer;
use App\Models\Discount;
use Illuminate\Support\Collection;

/**
 * Evaluates a discount code against one shop's cart lines.
 *
 * Kept side-effect free: checkout calls evaluate() for the live summary
 * and again inside Order::createFromCart (which is the place that
 * increments used_count).
 */
class DiscountEvaluator
{
    /**
     * Result shape: [ok(bool), amount(int), reason(string), discount(?Discount)]
     * — amount is in Toman, already capped by the ceiling / subtotal.
     *
     * @param  Collection<int, \App\Models\CartItem>  $lines  valid lines of ONE shop
     */
    public static function evaluate(?Discount $discount, ?Customer $customer, Collection $lines): array
    {
        if (! $discount) {
            return [false, 0, 'کد تخفیف معتبر نیست.', null];
        }

        if ($lines->isEmpty()) {
            return [false, 0, 'محصولی برای اعمال این کد در سبد شما نیست.', $discount];
        }

        /* ── product scope (include / exclude) ── */
        $lines = self::scopeLines($discount, $lines);

        if ($lines->isEmpty()) {
            return [false, 0, 'این کد شامل محصولات سبد شما نمی‌شود.', $discount];
        }

        /* ── only-first-purchase ── */
        if ($discount->only_first_purchase && $customer
            && \App\Models\Order::query()->where('customer_id', $customer->id)->where('status', 'paid')->exists()) {
            return [false, 0, 'این کد فقط برای اولین خرید قابل استفاده است.', $discount];
        }

        /* ── allowed customers ── */
        $allowed = $discount->customer_ids;

        if (is_array($allowed) && $allowed !== [] && $customer && ! in_array((int) $customer->id, array_map('intval', $allowed), true)) {
            return [false, 0, 'این کد تخفیف برای حساب شما فعال نیست.', $discount];
        }

        /* ── minimum order requirements ── */
        $subtotal = (int) $lines->sum(fn ($item) => $item->lineTotal());
        $count    = (int) $lines->sum('quantity');

        if ($discount->least_price > 0 && $subtotal < $discount->least_price) {
            return [false, 0, 'مبلغ سفارش برای استفاده از این کد کافی نیست (حداقل '.\App\Support\Fmt::money($discount->least_price).' تومان).', $discount];
        }

        if ($discount->least_products_count > 0 && $count < $discount->least_products_count) {
            return [false, 0, 'تعداد محصولات سبد برای این کد کافی نیست (حداقل '.\App\Support\Fmt::toFa($discount->least_products_count).' عدد).', $discount];
        }

        /* ── per-user usage ── */
        if ($discount->quantity_per_user > 0 && $customer) {
            $used = \App\Models\Order::query()
                ->where('customer_id', $customer->id)
                ->whereIn('status', ['pending', 'paid'])
                ->where('discount_code', $discount->code)
                ->count();

            if ($used >= $discount->quantity_per_user) {
                return [false, 0, 'سهمیهٔ استفادهٔ شما از این کد تمام شده است.', $discount];
            }
        }

        /* ── amount ── */
        $amount = $discount->type === Discount::TYPE_PERCENT
            ? (int) floor($subtotal * min(100, $discount->percent) / 100)
            : min((int) $discount->price, $subtotal);

        if ($discount->type === Discount::TYPE_PERCENT && $discount->discount_ceiling > 0) {
            $amount = min($amount, (int) $discount->discount_ceiling);
        }

        $amount = max(0, min($amount, $subtotal));

        if ($amount < 1) {
            return [false, 0, 'مبلغ تخفیف این کد صفر است.', $discount];
        }

        return [true, $amount, 'کد تخفیف اعمال شد.', $discount];
    }

    /** Apply include/exclude product scope to the shop's lines. */
    protected static function scopeLines(Discount $discount, Collection $lines): Collection
    {
        $include = self::includedLine($discount, $lines);
        $lines   = $include ?? $lines;

        $exclude = $discount->exclude_type !== Discount::EXCLUDE_NONE
            ? array_map('intval', (array) ($discount->exclude_ids ?? []))
            : [];

        if ($exclude === []) {
            return $lines;
        }

        return $lines->reject(function ($item) use ($discount, $exclude) {
            $product = $item->product;

            if (! $product) {
                return false;
            }

            if ($discount->exclude_type === Discount::INCLUDE_PRODUCT) {
                return in_array((int) $product->id, $exclude, true);
            }

            return in_array((int) $product->shop_category_id, $exclude, true);
        })->values();
    }

    /** Lines that the include-rule selects (null = rule is «all»). */
    protected static function includedLine(Discount $discount, Collection $lines): ?Collection
    {
        $ids = match ($discount->include_type) {
            Discount::INCLUDE_CATEGORY, Discount::INCLUDE_PRODUCT
                => array_map('intval', (array) ($discount->include_ids ?? [])),
            default => [],
        };

        if ($discount->include_type === Discount::INCLUDE_ALL || $ids === []) {
            return null;
        }

        return $lines->filter(function ($item) use ($discount, $ids) {
            $product = $item->product;

            if (! $product) {
                return false;
            }

            return $discount->include_type === Discount::INCLUDE_PRODUCT
                ? in_array((int) $product->id, $ids, true)
                : in_array((int) $product->shop_category_id, $ids, true);
        })->values();
    }
}
