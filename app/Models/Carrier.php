<?php

namespace App\Models;

use App\Support\Fmt;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'shop_id',
    'title',
    'image_path',
    'is_active',
    'waiting_time',
    'max_package_weight',
    'min_package_weight',
    'description',
    'origin_province_id',
    'origin_city_id',
    'carriage_forward',
    'base_price',
    'free_shipping_weight',
    'free_shipping_price',
    'extra_cost',
    'covered_cities',
    'delivery_time_type',
    'start_days_after_order',
    'default_delivery_range',
    'sort',
])]
class Carrier extends Model
{
    public const COVERED_ALL = 'all';

    public const COVERED_SELECT_CITY = 'select_city';

    public const TIME_DEFAULT = 'default';

    public const TIME_USER_SELECT = 'user_select';

    protected function casts(): array
    {
        return [
            'is_active'             => 'boolean',
            'carriage_forward'      => 'boolean',
            'max_package_weight'    => 'integer',
            'min_package_weight'    => 'integer',
            'base_price'            => 'integer',
            'free_shipping_weight'  => 'integer',
            'free_shipping_price'   => 'integer',
            'extra_cost'            => 'integer',
            'start_days_after_order' => 'integer',
            'sort'                  => 'integer',
        ];
    }

    /* ───────────────────── relations ───────────────────── */

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function originProvince(): BelongsTo
    {
        return $this->belongsTo(Province::class, 'origin_province_id');
    }

    public function originCity(): BelongsTo
    {
        return $this->belongsTo(City::class, 'origin_city_id');
    }

    /** Cities covered when covered_cities = select_city. */
    public function cities(): BelongsToMany
    {
        return $this->belongsToMany(City::class, 'carrier_city');
    }

    /** Weight tiers (تعرفه‌های وزنی) — درون/بیرون استانی. */
    public function tariffs(): HasMany
    {
        return $this->hasMany(CarrierTariff::class)->orderBy('max_weight')->orderBy('sort')->orderBy('id');
    }

    /* ───────────────────── scopes ───────────────────── */

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeForShop(Builder $query, int $shopId): Builder
    {
        return $query->where('shop_id', $shopId)->orderBy('sort')->orderBy('id');
    }

    /* ───────────────────── helpers ───────────────────── */

    /** Whether the carrier covers a given city id. */
    public function coversCity(int $cityId): bool
    {
        if ($this->covered_cities === self::COVERED_SELECT_CITY) {
            return $this->cities()->whereKey($cityId)->exists();
        }

        return true;
    }

    /** Human label of the covered area (e.g. «همه شهرها» / «۵ شهر»). */
    public function coverageLabel(): string
    {
        if ($this->covered_cities === self::COVERED_SELECT_CITY) {
            $count = $this->cities()->count();

            return $count > 0 ? $count.' شهر منتخب' : 'شهر منتخبی انتخاب نشده';
        }

        return 'همه شهرها';
    }

    /** The delivery window text shown to customers. */
    public function deliveryRangeLabel(): string
    {
        if ($this->delivery_time_type === self::TIME_USER_SELECT) {
            return 'انتخاب توسط خریدار';
        }

        return $this->default_delivery_range ?: 'زمان‌بندی ارسال ثبت نشده';
    }

    /**
     * Online shipping cost of this carrier for one shop's cart group.
     *
     *  پس‌کرایه          → 0 online (paid on delivery, shown as a note)
     *  تعرفه وزنی        → the tariff tier matching (zone, weight) — batch 74
     *  سقف مبلغ رایگان    → free when the group subtotal reached the threshold
     *  سقف وزن رایگان     → free when the group weight reached the threshold
     *  otherwise          → legacy base price + extra cost per extra kg
     */
    public function costFor(int $subtotal, int $weightGrams = 0, ?bool $inProvince = null): int
    {
        if ($this->carriage_forward) {
            return 0; // paid on delivery
        }

        // Weight tiers win over the legacy flat price when defined.
        if ($this->hasTariffs()) {
            $tierCost = $this->tariffCost($weightGrams, $inProvince);

            if ($tierCost === null) {
                return 0; // destination zone has no tier → treat as free rather than blocking
            }

            // Free-shipping thresholds still apply on top of tariffs.
            if ($this->free_shipping_price > 0 && $subtotal >= $this->free_shipping_price) {
                return 0;
            }

            if ($this->free_shipping_weight > 0 && $weightGrams >= $this->free_shipping_weight) {
                return 0;
            }

            return max(0, $tierCost);
        }

        if ($this->free_shipping_price > 0 && $subtotal >= $this->free_shipping_price) {
            return 0;
        }

        if ($this->free_shipping_weight > 0 && $weightGrams >= $this->free_shipping_weight) {
            return 0;
        }

        $kg = max(1, (int) ceil($weightGrams / 1000));

        return max(0, (int) $this->base_price + (max(0, $kg - 1) * (int) $this->extra_cost));
    }

    /** Whether the carrier has ANY weight tariff defined. */
    public function hasTariffs(): bool
    {
        return $this->tariffs()->exists();
    }

    /**
     * Cost of the tariff tier matching the weight for a zone
     * (smallest tier whose max_weight covers the package; beyond the
     * last tier the heaviest tier's cost applies). null = no tier.
     */
    public function tariffCost(int $weightGrams, ?bool $inProvince = null): ?int
    {
        $zone = ($inProvince === true) ? CarrierTariff::ZONE_IN : CarrierTariff::ZONE_OUT;

        $tiers = $this->tariffs->where('zone_type', $zone)->sortBy('max_weight')->values();

        if ($tiers->isEmpty()) {
            return null;
        }

        $tier = $tiers->first(fn (CarrierTariff $t) => $weightGrams <= $t->max_weight)
            ?? $tiers->last();

        return (int) $tier->cost;
    }

    /** Short tariff summary for the seller list chips: «۲ پله وزنی». */
    public function tariffSummaryLabel(): string
    {
        $count = $this->tariffs()->count();

        if ($count === 0) {
            return 'بدون تعرفه';
        }

        $in  = $this->tariffs->where('zone_type', CarrierTariff::ZONE_IN)->count();
        $out = $this->tariffs->where('zone_type', CarrierTariff::ZONE_OUT)->count();

        $parts = [];

        if ($in > 0) {
            $parts[] = 'درون استانی '.Fmt::toFa($in).' پله';
        }

        if ($out > 0) {
            $parts[] = 'بیرون استانی '.Fmt::toFa($out).' پله';
        }

        return implode(' · ', $parts);
    }

    /** Whether this carrier is paid on delivery (پس‌کرایه) instead of online. */
    public function isPayOnDelivery(): bool
    {
        return $this->carriage_forward;
    }
}
