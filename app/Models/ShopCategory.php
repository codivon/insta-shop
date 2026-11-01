<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['shop_id', 'name', 'sort', 'show_on_storefront'])]
class ShopCategory extends Model
{
    protected function casts(): array
    {
        return [
            'sort'                => 'integer',
            'show_on_storefront'  => 'boolean',
        ];
    }

    /** Scope: categories the seller ticked «نمایش در صفحه فروشگاه» for. */
    public function scopeShowOnStorefront(Builder $query): Builder
    {
        return $query->where('show_on_storefront', true);
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
