<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['shop_id', 'name', 'type', 'is_default', 'sort'])]
class ShopAttribute extends Model
{
    /** Attribute field types (نوع ویژگی). */
    public const TYPES = [
        'color'  => 'رنگی',
        'select' => 'انتخابی',
        'text'   => 'متنی',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
        ];
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function values(): HasMany
    {
        return $this->hasMany(ShopAttributeValue::class)->orderBy('sort')->orderBy('id');
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    /**
     * Seed the three default attribute fields (with values) for a shop.
     * Called once when the shop is created — sellers can edit/delete them.
     */
    public static function seedDefaultsFor(Shop $shop): void
    {
        if ($shop->attributes()->exists()) {
            return; // already seeded
        }

        $defaults = [
            [
                'name' => 'رنگ', 'type' => 'color', 'sort' => 0,
                'values' => [
                    ['value' => 'مشکی', 'hex' => '#111827'],
                    ['value' => 'سفید', 'hex' => '#F8FAFC'],
                    ['value' => 'قرمز', 'hex' => '#DC2626'],
                    ['value' => 'آبی', 'hex' => '#2563EB'],
                    ['value' => 'سبز', 'hex' => '#16A34A'],
                    ['value' => 'صورتی', 'hex' => '#EC4899'],
                    ['value' => 'طلایی', 'hex' => '#D97706'],
                    ['value' => 'نقره‌ای', 'hex' => '#94A3B8'],
                ],
            ],
            [
                'name' => 'سایز', 'type' => 'select', 'sort' => 1,
                'values' => [
                    ['value' => 'XS'], ['value' => 'S'], ['value' => 'M'], ['value' => 'L'],
                    ['value' => 'XL'], ['value' => 'XXL'],
                ],
            ],
            [
                'name' => 'گارانتی', 'type' => 'select', 'sort' => 2,
                'values' => [
                    ['value' => 'بدون گارانتی'],
                    ['value' => '۶ ماه گارانتی'],
                    ['value' => '۱۲ ماه گارانتی'],
                    ['value' => '۱۸ ماه گارانتی'],
                    ['value' => '۲۴ ماه گارانتی'],
                ],
            ],
        ];

        foreach ($defaults as $default) {
            $attribute = $shop->attributes()->create([
                'name'       => $default['name'],
                'type'       => $default['type'],
                'is_default' => true,
                'sort'       => $default['sort'],
            ]);

            foreach ($default['values'] as $i => $value) {
                $attribute->values()->create([
                    'value'     => $value['value'],
                    'color_hex' => $value['hex'] ?? null,
                    'sort'      => $i,
                ]);
            }
        }
    }
}
