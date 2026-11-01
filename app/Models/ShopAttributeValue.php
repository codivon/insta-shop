<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['shop_attribute_id', 'value', 'color_hex', 'sort'])]
class ShopAttributeValue extends Model
{
    public function attribute(): BelongsTo
    {
        return $this->belongsTo(ShopAttribute::class, 'shop_attribute_id');
    }
}
