<?php

namespace App\Models;

use App\Support\Fmt;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'shop_id',
    'title',
    'code',
    'type',
    'price',
    'percent',
    'discount_ceiling',
    'starts_at',
    'ends_at',
    'least_price',
    'least_products_count',
    'quantity',
    'quantity_per_user',
    'used_count',
    'customer_ids',
    'include_type',
    'include_ids',
    'exclude_type',
    'exclude_ids',
    'only_first_purchase',
    'not_discount_products',
    'description',
    'published',
])]
class Discount extends Model
{
    public const TYPE_AMOUNT = 'amount';

    public const TYPE_PERCENT = 'percent';

    public const INCLUDE_ALL = 'all';

    public const INCLUDE_CATEGORY = 'category';

    public const INCLUDE_PRODUCT = 'product';

    public const EXCLUDE_NONE = 'none';

    protected function casts(): array
    {
        return [
            'price'               => 'integer',
            'percent'             => 'integer',
            'discount_ceiling'    => 'integer',
            'starts_at'           => 'datetime',
            'ends_at'             => 'datetime',
            'least_price'         => 'integer',
            'least_products_count' => 'integer',
            'quantity'            => 'integer',
            'quantity_per_user'   => 'integer',
            'used_count'          => 'integer',
            'customer_ids'        => 'array',
            'include_ids'         => 'array',
            'exclude_ids'         => 'array',
            'only_first_purchase' => 'boolean',
            'not_discount_products' => 'boolean',
            'published'           => 'boolean',
        ];
    }

    /* ───────────────────── relations ───────────────────── */

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    /* ───────────────────── scopes ───────────────────── */

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('published', true);
    }

    public function scopeForShop(Builder $query, int $shopId): Builder
    {
        return $query->where('shop_id', $shopId)->orderByDesc('id');
    }

    /* ───────────────────── helpers ───────────────────── */

    /** Human label of the value (e.g. «۲۰٪» / «۵۰٬۰۰۰ تومان»). */
    public function valueLabel(): string
    {
        return $this->type === self::TYPE_PERCENT
            ? Fmt::toFa($this->percent).'٪'
            : Fmt::moneyShort($this->price).' تومان';
    }

    /** The JDate window text shown in the list (e.g. «از ۱۴۰۴/۰۱/۰۱»). */
    public function windowLabel(): string
    {
        $from = $this->starts_at ? \App\Support\JDate::date($this->starts_at) : null;
        $to   = $this->ends_at ? \App\Support\JDate::date($this->ends_at) : null;

        return match (true) {
            $from && $to => "از {$from} تا {$to}",
            $from        => "از {$from}",
            $to          => "تا {$to}",
            default      => 'بدون محدودیت زمانی',
        };
    }

    /** Whether the discount is currently inside its time window. */
    public function inWindow(): bool
    {
        if ($this->starts_at && $this->starts_at->isFuture()) {
            return false;
        }

        if ($this->ends_at && $this->ends_at->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * Find a redeemable discount for a code — window/quantity limits only
     * (the cart-side checks like least_price live in DiscountEvaluator).
     */
    public static function findActive(string $code): ?self
    {
        $code = trim($code);

        if ($code === '') {
            return null;
        }

        /** @var ?self $discount */
        $discount = static::query()
            ->published()
            ->whereRaw('lower(code) = lower(?)', [$code])
            ->first();

        if (! $discount || ! $discount->inWindow()) {
            return null;
        }

        if ($discount->quantity > 0 && $discount->used_count >= $discount->quantity) {
            return null;
        }

        return $discount;
    }
}
