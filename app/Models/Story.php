<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

/**
 * Instagram-style shop story: an image (video reserved for later) shown in
 * a full-screen viewer for a limited window (default 24h, customizable via
 * starts_at/ends_at). Visibility for customers = approved by the admin
 * (or auto-approved per the stories settings) AND inside the window.
 */
class Story extends Model
{
    public const TYPE_IMAGE = 'image';

    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'shop_id', 'product_id', 'type', 'media_path', 'caption', 'link_url',
        'starts_at', 'ends_at', 'status',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function likes(): HasMany
    {
        return $this->hasMany(StoryLike::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(StoryComment::class)->latest();
    }

    public function views(): HasMany
    {
        return $this->hasMany(StoryView::class);
    }

    /* ───────────────── scopes ───────────────── */

    /** Approved (or auto-approved) + inside the display window. */
    public function scopeVisible(Builder $query): Builder
    {
        $now = now();

        return $query
            ->where('status', self::STATUS_APPROVED)
            ->where('starts_at', '<=', $now)
            ->where('ends_at', '>', $now);
    }

    /** Stories of shops the customer may see (public shops). */
    public function scopeOfPublicShops(Builder $query): Builder
    {
        return $query->whereHas('shop', fn ($q) => $q->publiclyVisible());
    }

    /** Currently running (for the max-active-per-shop limit). */
    public function scopeRunning(Builder $query): Builder
    {
        $now = now();

        return $query->where('starts_at', '<=', $now)->where('ends_at', '>', $now);
    }

    /* ───────────────── state helpers ───────────────── */

    public function isRunning(): bool
    {
        return $this->starts_at !== null
            && $this->ends_at !== null
            && now()->between($this->starts_at, $this->ends_at);
    }

    /** pending → «در انتظار تأیید» · running approved → «فعال» · … */
    public function stateLabel(): string
    {
        if ($this->status === self::STATUS_REJECTED) {
            return 'رد شده';
        }

        if ($this->status === self::STATUS_PENDING) {
            return 'در انتظار تأیید';
        }

        if (now()->lt($this->starts_at)) {
            return 'زمان‌بندی‌شده';
        }

        if ($this->isRunning()) {
            return 'فعال';
        }

        return 'منقضی';
    }

    /** Accent color for the state badge. */
    public function stateClass(): string
    {
        return match ($this->stateLabel()) {
            'فعال' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300',
            'در انتظار تأیید' => 'bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-300',
            'زمان‌بندی‌شده' => 'bg-sky-100 text-sky-700 dark:bg-sky-950 dark:text-sky-300',
            'رد شده' => 'bg-rose-100 text-rose-700 dark:bg-rose-950 dark:text-rose-300',
            default => 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300',
        };
    }

    public function mediaUrl(): string
    {
        return Storage::disk('public')->url($this->media_path);
    }

    /** Whether the given customer already saw this story. */
    public function seenBy(?Customer $customer): bool
    {
        if ($customer === null) {
            return false;
        }

        return $this->views()
            ->where('customer_id', $customer->id)
            ->exists();
    }

    /** Like state + count in one shot for cards. */
    public function likedBy(?Customer $customer): bool
    {
        if ($customer === null) {
            return false;
        }

        return $this->likes()
            ->where('customer_id', $customer->id)
            ->exists();
    }
}
