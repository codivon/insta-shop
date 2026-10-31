<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['shop_id', 'shop_category_id', 'name', 'description', 'price', 'compare_at_price', 'stock', 'status'])]
class Product extends Model
{
    protected function casts(): array
    {
        return [
            'price'            => 'integer',
            'compare_at_price' => 'integer',
            'stock'            => 'integer',
            'views'            => 'integer',
        ];
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ShopCategory::class, 'shop_category_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort')->orderBy('id');
    }

    /** First image (lowest sort) — used as the card thumbnail. */
    public function mainImage(): HasOne
    {
        return $this->hasOne(ProductImage::class)->orderBy('sort')->orderBy('id');
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }
}
