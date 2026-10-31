<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable(['seller_id', 'name', 'slug', 'instagram_username', 'about', 'logo_path', 'banner_path', 'phone', 'address', 'status'])]
class Shop extends Model
{
    protected static function booted(): void
    {
        // Generate a unique slug once, right after creation.
        static::created(function (self $shop) {
            if ($shop->slug) {
                return;
            }

            $base = Str::slug($shop->name) ?: 'shop';
            $slug = $base.'-'.$shop->id;

            while (self::query()->where('slug', $slug)->whereKeyNot($shop->id)->exists()) {
                $slug = $base.'-'.$shop->id.'-'.Str::lower(Str::random(4));
            }

            $shop->forceFill(['slug' => $slug])->saveQuietly();
        });
    }

    /** Get-or-create the single shop owned by the given seller (safe to call on every login/page). */
    public static function ensureForSeller(Seller $seller): self
    {
        return static::query()->firstOrCreate(
            ['seller_id' => $seller->id],
            [
                'name'               => 'فروشگاه '.($seller->instagram_username ? '@'.$seller->instagram_username : ($seller->full_name ?: 'من')),
                'instagram_username' => $seller->instagram_username,
            ],
        );
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }

    public function categories(): HasMany
    {
        return $this->hasMany(ShopCategory::class)->orderBy('sort')->orderBy('id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class)->latest();
    }

    public function publishedProducts(): HasMany
    {
        return $this->products()->where('status', 'published');
    }

    /** Public storefront visibility (used by the customer app from Phase 4). */
    public function isPublic(): bool
    {
        return $this->status === 'active' && $this->seller?->status === 'approved';
    }
}
