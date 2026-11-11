<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * One «نردبانِ فروشگاه» purchase (v0.25.0 — the SHOP twin of ProductBump):
 * the seller pays to keep their OWN shop at the very front of the home
 * page's shop lists for a fixed window.
 *
 * «Active» = ends_at > now — expiry happens BY DATE exactly like the
 * product bumps / subscriptions, so no cron can leave a paid shop bump
 * stuck on top. Renewal stacking: buying again while the bump is still
 * running EXTENDS it from its current ends_at (BumpService::grantShop),
 * so the row is both the entitlement and the auditable ledger.
 */
#[Fillable(['seller_id', 'shop_id', 'starts_at', 'ends_at', 'price_paid', 'bump_plan_id', 'source', 'note'])]
class ShopBump extends Model
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

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    /** The «طرح نردبان» this purchase rode on (null = legacy/manual rows). */
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

    /**
     * The still-running bump of ONE shop (newest end first). Only ONE
     * matter at a time — stacking renewals leave several open rows and
     * the newest end wins.
     */
    public static function runningForShop(int $shopId): ?self
    {
        return static::query()
            ->where('shop_id', $shopId)
            ->where('ends_at', '>', Carbon::now())
            ->orderByDesc('ends_at')
            ->orderByDesc('id')
            ->first();
    }

    /**
     * shop_id => ends_at for every shop with a RUNNING bump — the
     * customer-side ordering map (tiny; only bumped shops are returned).
     * Ascending walk so each shop's map value ends up being its LATEST
     * end (stacked renewals) — later writes always win with a bigger ts.
     */
    public static function activeByShopId(): array
    {
        return static::query()
            ->where('ends_at', '>', Carbon::now())
            ->whereHas('shop', fn ($q) => $q->publiclyVisible())
            ->orderBy('ends_at')
            ->get(['shop_id', 'ends_at'])
            ->mapWithKeys(fn (self $row) => [$row->shop_id => $row->ends_at->getTimestamp()])
            ->all();
    }

    /** The still-running bumps of one seller — newest end first. */
    public static function activeFor(int $sellerId): Collection
    {
        return static::query()
            ->where('seller_id', $sellerId)
            ->where('ends_at', '>', Carbon::now())
            ->with('shop:id,name,logo_path,instagram_username')
            ->orderByDesc('ends_at')
            ->get();
    }
}
