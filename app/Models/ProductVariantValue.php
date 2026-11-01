<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['product_variant_id', 'shop_attribute_id', 'shop_attribute_value_id', 'value_text'])]
class ProductVariantValue extends Model
{
    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function attribute(): BelongsTo
    {
        return $this->belongsTo(ShopAttribute::class, 'shop_attribute_id');
    }

    public function value(): BelongsTo
    {
        return $this->belongsTo(ShopAttributeValue::class, 'shop_attribute_value_id');
    }
}
