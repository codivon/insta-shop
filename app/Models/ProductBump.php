<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * One «نردبان» purchase (v0.23.0 — Divar-style bump): the seller pays to
 * keep ONE product at the very top of the home page for a fixed window.
 *
 * «Active» = ends_at > now — expiry happens BY DATE exactly like the
 * subscriptions, so no cron can leave a paid bump stuck on top. Renewal
 * stacking: buying again while the bump is still running EXTENDS it from
 * its current ends_at (BumpService), so the row is both the entitlement
 * and the auditable ledger.
 */
#[Fillable(['seller_id', 'product_id', 'starts_at', 'ends_at', 'price_paid', 'bump_plan_id', 'source', 'note'])]
class ProductBump extends Model
{
    public const SOURCE_WALLET = 'wallet'; // خرید از کیف پول

    public const SOURCE_ADMIN = 'admin'; // اعطای دستی مدیر

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'price_paid' => 'integer',
        ];
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /** The «طرح نردبان» this purchase rode on (null = the retired flat-rate era). */
    public function bumpPlan(): BelongsTo
    {
        return $this->belongsTo(BumpPlan::class);
    }

    /** Is this bump still running? */
    public function isActive(): bool
    {
        return $this->ends_at->isFuture();
    }

    /** Whole days left (0 on the last day / expired). */
    public function daysLeft(): int
    {
        return max(0, (int) now()->startOfDay()->diffInDays($this->ends_at->endOfDay()));
    }

    /** Persian source label for the tables. */
    public function sourceLabel(): string
    {
        return match ($this->source) {
            self::SOURCE_ADMIN => 'اعطای مدیر',
            default => 'خرید از کیف پول',
        };
    }

    /** The still-running bumps of one seller — newest end first. */
    public static function activeFor(int $sellerId): Collection
    {
        return static::query()
            ->where('seller_id', $sellerId)
            ->where('ends_at', '>', Carbon::now())
            ->with('product:id,shop_id,name')
            ->orderByDesc('ends_at')
            ->get();
    }

    /**
     * The DISTINCT product ids of one seller's RUNNING bumps (v0.25.0) —
     * the «نردبان فعال» badge on the «فروشگاه من» product cards.
     */
    public static function activeProductIds(int $sellerId): array
    {
        return static::query()
            ->where('seller_id', $sellerId)
            ->where('ends_at', '>', Carbon::now())
            ->pluck('product_id')
            ->unique()
            ->values()
            ->all();
    }

    /** Home-page strip payload: bumped products, longest remaining first. */
    public static function homeStrip(int $limit = 10): Collection
    {
        return static::query()
            ->where('ends_at', '>', Carbon::now())
            ->whereHas('product', fn ($q) => $q
                ->where('status', 'published')
                ->whereHas('shop', fn ($shop) => $shop->publiclyVisible()))
            ->with(['product.mainImage', 'product.shop:id,name,instagram_username'])
            ->orderByDesc('ends_at')
            ->orderByDesc('id')
            ->limit($limit * 2) // stacked renewals duplicate products — dedupe below
            ->get()
            ->map(fn (self $bump) => $bump->product)
            ->filter()
            ->unique('id')
            ->take($limit)
            ->values();
    }
}
