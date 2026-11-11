<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable(['seller_id', 'name', 'slug', 'instagram_username', 'about', 'logo_path', 'banner_path', 'phone', 'address', 'status', 'show_stock'])]
class Shop extends Model
{
    protected static function booted(): void
    {
        // Generate a unique slug once, right after creation.
        static::created(function (self $shop) {
            if (! $shop->slug) {
                $base = Str::slug($shop->name) ?: 'shop';
                $slug = $base.'-'.$shop->id;

                while (self::query()->where('slug', $slug)->whereKeyNot($shop->id)->exists()) {
                    $slug = $base.'-'.$shop->id.'-'.Str::lower(Str::random(4));
                }

                $shop->forceFill(['slug' => $slug])->saveQuietly();
            }

            // Fresh shops start with the three default variation fields.
            ShopAttribute::seedDefaultsFor($shop);
        });
    }

    /** Get-or-create the single shop owned by the given seller (safe to call on every login/page). */
    public static function ensureForSeller(Seller $seller): self
    {
        $shop = static::query()->firstOrCreate(
            ['seller_id' => $seller->id],
            [
                'name' => 'فروشگاه '.($seller->instagram_username ? '@'.$seller->instagram_username : ($seller->full_name ?: 'من')),
                'instagram_username' => $seller->instagram_username,
            ],
        );

        // Legacy shops (created before the attributes migration) get their
        // default variation fields on first touch; seeded shops no-op.
        ShopAttribute::seedDefaultsFor($shop);

        return $shop;
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }

    /**
     * Global categories actually used by this shop's published products,
     * each with `shop_products_count` (products of THIS shop inside it).
     * Replaces the old per-shop category management — categories are
     * platform-wide now.
     */
    public function usedCategories(): Builder
    {
        return Category::query()
            ->join('products', 'products.category_id', '=', 'categories.id')
            ->where('products.shop_id', $this->id)
            ->where('products.status', Product::STATUS_PUBLISHED)
            ->groupBy('categories.id')
            ->selectRaw('categories.*, count(products.id) as shop_products_count')
            ->orderBy('categories.sort')
            ->orderBy('categories.id');
    }

    /** Variation fields defined by this shop (رنگ، گارانتی، سایز، ...). */
    public function attributes(): HasMany
    {
        return $this->hasMany(ShopAttribute::class)->orderBy('sort')->orderBy('id');
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

    /**
     * v0.23.0 — «فروشگاه بسته به‌خاطر اتمام اشتراک»؟ The shop answers the
     * usual visibility rules (active + approved seller) but its seller has
     * NO active subscription any more: the storefront still resolves so the
     * customer sees the «اشتراک فروشنده به پایان رسیده» page instead of a
     * bare 404, while every LISTING (home, search, sliders) hides it via
     * scopePubliclyVisible.
     */
    public function isClosedForSubscription(): bool
    {
        return $this->isPublic() && ! $this->seller->hasActiveSubscription();
    }

    /** Query scope: shops the customer app is allowed to show. */
    public function scopePubliclyVisible(Builder $query): Builder
    {
        // v0.23.0 — the seller must ALSO hold an active subscription
        // (plan / trial / admin gift): «هر کسی که اشتراک نداشت، فروشگاهش
        // باز نمی‌شود». Expiry is by date, so this never drifts.
        return $query->where('status', 'active')
            ->whereHas('seller', fn (Builder $q) => $q
                ->where('status', 'approved')
                ->whereHas('subscriptions', fn (Builder $s) => $s->where('ends_at', '>', now())));
    }

    /**
     * The instagram handle this storefront answers to — the seller's
     * (immutable) handle first, then the shop mirror.
     */
    public function storefrontHandle(): ?string
    {
        return $this->seller?->instagram_username ?: $this->instagram_username;
    }

    /** Public address of this storefront: دامنه/shop/{آیدی اینستا}. */
    public function storefrontUrl(): string
    {
        $handle = $this->storefrontHandle();

        return $handle ? route('shop.storefront', ['instagram' => $handle]) : url('/shop');
    }
}
