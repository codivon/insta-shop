<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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
}
