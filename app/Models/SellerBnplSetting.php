<?php

namespace App\Models;

use App\Support\Fmt;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * v0.30.0 — a seller's opt-in of ONE BNPL (installment) gateway.
 *
 * The seller turns «خرید اقساطی» on per gateway from their panel:
 *   • enabled         → their products become payable through the gateway
 *   • show_on_product → the single-product page shows the small
 *                       «امکان خرید اقساطی» banner (SnappPay-style)
 *   • fee_type/value  → the surcharge added to the PRODUCTS total when a
 *                       customer pays via this gateway (percent | fixed),
 *                       on top of the goods and NEVER on the shipping.
 *
 * A BNPL gateway is offered at checkout only when EVERY seller with items
 * in the cart enabled it (one opt-out removes it for the whole cart).
 */
#[Fillable([
    'seller_id', 'gateway', 'enabled', 'show_on_product', 'fee_type', 'fee_value',
])]
class SellerBnplSetting extends Model
{
    /** Fee shapes a seller may pick from. */
    public const FEE_TYPES = ['none', 'percent', 'fixed'];

    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
            'show_on_product' => 'boolean',
            'fee_value' => 'integer',
        ];
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }

    /** Persian label of the fee shape («بدون کارمزد» / «۵٪» / «۲۰٬۰۰۰ تومان»). */
    public function feeLabel(): string
    {
        return match ($this->fee_type) {
            'percent' => $this->fee_value > 0 ? Fmt::toFa($this->fee_value).'٪ کارمزد' : 'بدون کارمزد',
            'fixed' => $this->fee_value > 0 ? Fmt::money($this->fee_value).' کارمزد' : 'بدون کارمزد',
            default => 'بدون کارمزد',
        };
    }

    /**
     * The surcharge for ONE shop's goods total under this setting.
     * percent → floor(total × value / 100) — fixed → value once per shop.
     */
    public function feeFor(int $itemsTotal): int
    {
        return match ($this->fee_type) {
            'percent' => $this->fee_value > 0 ? (int) floor(((int) $itemsTotal) * $this->fee_value / 100) : 0,
            'fixed' => min(max(0, $this->fee_value), PHP_INT_MAX),
            default => 0,
        };
    }

    /** Convenience finder (null-safe — never throws). */
    public static function forSeller(string $gateway, ?int $sellerId): ?self
    {
        if (! $sellerId) {
            return null;
        }

        return static::query()
            ->where('seller_id', $sellerId)
            ->where('gateway', $gateway)
            ->first();
    }
}
