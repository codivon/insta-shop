<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;

#[Fillable(['phone', 'instagram_username', 'full_name', 'status', 'phone_verified_at', 'commission_type', 'commission_value'])]
#[Hidden(['remember_token'])]
class Seller extends Authenticatable
{
    protected function casts(): array
    {
        return [
            'phone_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'commission_value' => 'integer',
        ];
    }

    /* ───────────────── subscription (v0.22.0) ───────────────── */

    /** The seller's ACTIVE subscription (ends_at > now), if any. */
    public function activeSubscription(): ?SellerSubscription
    {
        return SellerSubscription::activeFor($this->id);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(SellerSubscription::class);
    }

    /** Does this seller currently hold ANY active entitlement (plan, trial or gift)? */
    public function hasActiveSubscription(): bool
    {
        return $this->subscriptions()
            ->where('ends_at', '>', now())
            ->exists();
    }

    /* ───────────────── bump — نردبان (v0.23.0) ───────────────── */

    public function bumps(): HasMany
    {
        return $this->hasMany(ProductBump::class);
    }

    /* ───────────────── commission (79 + 22.0) ───────────────── */

    /** Whether the seller has a PERSONAL commission override. */
    public function hasCommissionOverride(): bool
    {
        return in_array($this->commission_type, ['percent', 'fixed'], true)
            && $this->commission_value !== null;
    }

    /**
     * Resolved [type, value] — priority:
     *   ۱. the admin's PERSONAL override (a deliberate per-seller deal);
     *   ۲. the commission of the seller's ACTIVE plan (v0.22.0) — a plan
     *      with commission_type = null contributes nothing;
     *   ۳. the global default from settings («تنظیمات فروشگاه‌ها»).
     * The seller's subscription page always shows the RESOLVED rate so
     * nobody buys a plan expecting a rate the override still suppresses.
     */
    public function resolvedCommission(): array
    {
        if ($this->hasCommissionOverride()) {
            return [$this->commission_type, (int) $this->commission_value];
        }

        $subscription = $this->activeSubscription();

        if ($subscription !== null) {
            $override = $subscription->commissionOverride();

            if ($override !== null) {
                return $override;
            }
        }

        $type = (string) Setting::get('shops.commission_type', 'percent');
        $type = in_array($type, ['percent', 'fixed'], true) ? $type : 'percent';

        return [$type, (int) Setting::get('shops.commission_value', 0)];
    }

    /** Commission (تومان) due on a shop goods total — percent on the total, fixed as a flat cut. */
    public function commissionFor(int $itemsTotal): int
    {
        [$type, $value] = $this->resolvedCommission();

        if ($itemsTotal <= 0) {
            return 0;
        }

        return match ($type) {
            'fixed' => min(max(0, $value), $itemsTotal),
            default => min((int) floor($itemsTotal * max(0, min(100, $value)) / 100), $itemsTotal),
        };
    }

    public function shop(): HasOne
    {
        return $this->hasOne(Shop::class);
    }

    public function products(): HasManyThrough
    {
        return $this->hasManyThrough(Product::class, Shop::class);
    }

    /** Human-friendly status label for the admin panel. */
    public function statusLabel(): string
    {
        return match ($this->status) {
            'approved' => 'تأیید شده',
            'suspended' => 'غیرفعال',
            default => 'در انتظار تأیید',
        };
    }

    public function statusClasses(): string
    {
        return match ($this->status) {
            'approved' => 'bg-emerald-50 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-400',
            'suspended' => 'bg-rose-50 text-rose-600 dark:bg-rose-950/40 dark:text-rose-400',
            default => 'bg-amber-50 text-amber-600 dark:bg-amber-950/40 dark:text-amber-400',
        };
    }

    public function routeNotificationForSms(): string
    {
        return $this->phone;
    }
}
