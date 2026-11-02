<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['shop_id', 'title', 'published', 'sort'])]
class ShopFilter extends Model
{
    protected function casts(): array
    {
        return [
            'published' => 'boolean',
            'sort'      => 'integer',
        ];
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    /** Selected spec groups (رنگ، گارانتی، …) of this filter. */
    public function items(): HasMany
    {
        return $this->hasMany(ShopFilterItem::class)->orderBy('id');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('published', true);
    }

    public function scopeForShop(Builder $query, int $shopId): Builder
    {
        return $query->where('shop_id', $shopId)->orderBy('sort')->orderBy('id');
    }
}
