<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * One seller subscription row — BOTH the active entitlement AND the
 * history (expired rows stay as an auditable ledger).
 *
 * «Active» = ends_at > now. Nothing else: expiry happens BY DATE, so no
 * cron can leave a paid plan stuck active, and no status column can
 * drift. Revoking = clamp ends_at to now (history intact).
 */
#[Fillable(['seller_id', 'plan_id', 'starts_at', 'ends_at', 'price_paid', 'source', 'is_trial', 'note'])]
class SellerSubscription extends Model
{
    public const SOURCE_ADMIN = 'admin'; // اعطای دستی توسط مدیر

    public const SOURCE_WALLET = 'wallet'; // خرید از کیف پول

    public const SOURCE_TRIAL = 'trial'; // دورهٔ آزمایشی خودکار

    public const SOURCE_AUTO = 'auto'; // v0.25.0 — اشتراک رایگان خودکار ثبت‌نام

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'price_paid' => 'integer',
            'is_trial' => 'boolean',
        ];
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    /** Is this subscription still running? */
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
            self::SOURCE_WALLET => 'خرید از کیف پول',
            self::SOURCE_TRIAL => 'دورهٔ آزمایشی',
            self::SOURCE_AUTO => 'اشتراک رایگان خودکار',
            default => 'اعطای مدیر',
        };
    }

    /** Row title: plan title, «دورهٔ آزمایشی» or «بدون پلن». */
    public function title(): string
    {
        return $this->plan?->title ?? ($this->is_trial ? 'دورهٔ آزمایشی' : 'بدون پلن');
    }

    /** The commission this row grants (null = the row grants no special rate). */
    public function commissionOverride(): ?array
    {
        $plan = $this->plan;

        if (! $plan || ! $plan->hasCommissionOverride()) {
            return null;
        }

        return [$plan->commission_type, (int) $plan->commission_value];
    }

    /** Active subscriptions (any kind) of one seller — newest end first. */
    public static function activeFor(int $sellerId): ?self
    {
        return static::query()
            ->where('seller_id', $sellerId)
            ->where('ends_at', '>', Carbon::now())
            ->orderByDesc('ends_at')
            ->orderByDesc('id')
            ->first();
    }
}
