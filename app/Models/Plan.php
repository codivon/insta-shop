<?php

namespace App\Models;

use App\Support\Fmt;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Subscription plan (پلن اشتراک) — the product the SELLER buys to unlock
 * benefits for a fixed duration (ماهانه / ۶ ماهه / سالانه …).
 *
 * A plan may carry its OWN commission rate (commission_type percent|fixed
 * + value). Resolution order (Seller::resolvedCommission()):
 *   ۱. the admin's PERSONAL override for this seller (wins — deliberate),
 *   ۲. the seller's ACTIVE plan commission,
 *   ۳. the global default from «تنظیمات فروشگاه‌ها».
 * commission_type = null means «طبق نرخ سراسری» — the plan sells the
 * duration itself and leaves the rate to the global setting.
 */
#[Fillable(['title', 'tagline', 'features', 'duration_days', 'price', 'commission_type', 'commission_value', 'is_active', 'sort'])]
class Plan extends Model
{
    protected function casts(): array
    {
        return [
            'features' => 'array',
            'duration_days' => 'integer',
            'price' => 'integer',
            'commission_value' => 'integer',
            'is_active' => 'boolean',
            'sort' => 'integer',
        ];
    }

    /** Whether this plan defines a SPECIAL commission rate. */
    public function hasCommissionOverride(): bool
    {
        return in_array($this->commission_type, ['percent', 'fixed'], true)
            && $this->commission_value !== null;
    }

    /** Human commission line for the plan card: «کمیسیون صفر» / «طبق نرخ سراسری». */
    public function commissionLabel(): string
    {
        if (! $this->hasCommissionOverride()) {
            return 'کمیسیون طبق نرخ سراسری';
        }

        return $this->commission_type === 'fixed'
            ? 'کمیسیون ثابت '.Fmt::money($this->commission_value).' به‌ازای هر سفارش'
            : 'کمیسیون '.Fmt::toFa($this->commission_value).'٪';
    }

    /** Human duration line: «۳۰ روزه». */
    public function durationLabel(): string
    {
        return Fmt::toFa($this->duration_days).' روزه';
    }

    /** Sellable plans for the seller panel — active, cheapest-durations first by sort. */
    public static function sellable(): Collection
    {
        return static::query()
            ->where('is_active', true)
            ->orderBy('sort')
            ->orderBy('duration_days')
            ->orderBy('id')
            ->get();
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(SellerSubscription::class);
    }
}
