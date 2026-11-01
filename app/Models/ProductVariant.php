<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['product_id', 'price', 'stock', 'discount_percent', 'discount_expires_at', 'max_purchase', 'min_purchase', 'status', 'sort'])]
class ProductVariant extends Model
{
    protected function casts(): array
    {
        return [
            'price'               => 'integer',
            'stock'               => 'integer',
            'discount_percent'    => 'integer',
            'discount_expires_at' => 'datetime',
            'max_purchase'        => 'integer',
            'min_purchase'        => 'integer',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function values(): HasMany
    {
        return $this->hasMany(ProductVariantValue::class);
    }

    /** Final payable price after the variant discount. */
    public function finalPrice(): int
    {
        if ($this->discount_percent <= 0) {
            return (int) $this->price;
        }

        return (int) floor($this->price * (100 - min(100, $this->discount_percent)) / 100);
    }

    public function isDiscountActive(): bool
    {
        return $this->discount_percent > 0
            && ($this->discount_expires_at === null || $this->discount_expires_at->isFuture());
    }

    /** Human label from its attribute picks: «رنگ: مشکی / سایز: L». */
    public function label(): string
    {
        $parts = [];

        foreach ($this->values as $value) {
            $attributeName = $value->attribute?->name ?? '';

            $picked = $value->value_text
                ?? $value->value?->value
                ?? '';

            if ($picked !== '') {
                $parts[] = $attributeName !== '' ? $attributeName.': '.$picked : $picked;
            }
        }

        return $parts !== [] ? implode(' / ', $parts) : 'تنوع '.($this->sort + 1);
    }

    public function valueForAttribute(int $attributeId): ?self
    {
        return $this->values->first(fn (ProductVariantValue $v) => (int) $v->shop_attribute_id === $attributeId);
    }
}
