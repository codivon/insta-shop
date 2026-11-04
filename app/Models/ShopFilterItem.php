<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['shop_filter_id', 'spec_name', 'kind', 'shop_attribute_id'])]
class ShopFilterItem extends Model
{
    /** Item kinds — what the filter block reads its values from. */
    public const KIND_SPEC = 'spec';

    public const KIND_ATTRIBUTE = 'attribute';

    /** The table has no created_at/updated_at columns (migration 2026_11_15_000001). */
    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'kind' => 'string',
            'shop_attribute_id' => 'integer',
        ];
    }

    public function filter(): BelongsTo
    {
        return $this->belongsTo(ShopFilter::class, 'shop_filter_id');
    }

    /** The shop attribute (ویژگی) behind this item — only when kind = attribute. */
    public function attribute(): BelongsTo
    {
        return $this->belongsTo(ShopAttribute::class, 'shop_attribute_id');
    }

    public function isAttribute(): bool
    {
        return $this->kind === self::KIND_ATTRIBUTE;
    }
}
