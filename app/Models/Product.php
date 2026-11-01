<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['shop_id', 'shop_category_id', 'name', 'description', 'specifications', 'spec_type', 'type', 'price', 'compare_at_price', 'stock', 'weight_grams', 'unit', 'status', 'is_special', 'special_ends_at'])]
class Product extends Model
{
    public const TYPE_PHYSICAL = 'physical';

    public const TYPE_DOWNLOADABLE = 'downloadable';

    public const STATUS_DRAFT = 'draft';

    public const STATUS_PUBLISHED = 'published';

    protected function casts(): array
    {
        return [
            'price'            => 'integer',
            'compare_at_price' => 'integer',
            'stock'            => 'integer',
            'views'            => 'integer',
            'weight_grams'     => 'integer',
            'specifications'   => 'array',
            'is_special'       => 'boolean',
            'special_ends_at'  => 'datetime',
        ];
    }

    /** The «فروش ویژه» window is currently open (flag on + deadline in the future). */
    public function isSpecialActive(): bool
    {
        if (! $this->is_special) {
            return false;
        }

        return $this->special_ends_at === null || $this->special_ends_at->isFuture();
    }

    /** Scope: published products whose special-sale window is open. */
    public function scopeSpecialActive(Builder $query): Builder
    {
        return $query->where('is_special', true)
            ->where(fn (Builder $q) => $q
                ->whereNull('special_ends_at')
                ->orWhere('special_ends_at', '>', now()));
    }

    public function isDownloadable(): bool
    {
        return $this->type === self::TYPE_DOWNLOADABLE;
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class)->orderBy('sort')->orderBy('id');
    }

    public function activeVariants(): HasMany
    {
        return $this->variants()->where('status', 'active');
    }

    public function files(): HasMany
    {
        return $this->hasMany(ProductFile::class)->orderBy('sort')->orderBy('id');
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
        return $this->status === self::STATUS_PUBLISHED;
    }

    /** The product sells through active variants (price/stock live per combination). */
    public function hasVariants(): bool
    {
        return $this->activeVariants()->exists();
    }

    /**
     * Sync the denormalized price/stock used by the customer app:
     * price = cheapest active variant/file, stock = summed variant stock.
     */
    public function syncAggregates(): void
    {
        if ($this->isDownloadable()) {
            $minFilePrice = $this->files()->where('status', 'active')->min('price');

            $this->forceFill(['price' => (int) ($minFilePrice ?: $this->price)])->saveQuietly();

            return;
        }

        $variants = $this->variants()->where('status', 'active')->get(['price', 'stock']);

        if ($variants->isNotEmpty()) {
            $this->forceFill([
                'price' => (int) $variants->min('price'),
                'stock' => (int) $variants->sum('stock'),
            ])->saveQuietly();
        }
    }
}
