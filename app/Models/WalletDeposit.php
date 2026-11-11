<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One «واریز به کیف پول» attempt (v0.24.0) — the seller types an amount,
 * picks a gateway, and the browser is handed to the gateway. The wallet
 * is credited ONLY after the callback verifies (WalletDepositService) —
 * the pending row is just the auditable in-flight state.
 *
 * Status flow: pending → done (credited) | failed (never credited) —
 * callbacks are idempotent: a retried one re-enters the success path
 * without double-crediting (status re-checked before the credit).
 */
#[Fillable(['seller_id', 'wallet_id', 'amount', 'gateway', 'gateway_ref', 'status', 'message', 'meta'])]
class WalletDeposit extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_DONE = 'done';

    public const STATUS_FAILED = 'failed';

    public const STATUS_CANCELED = 'canceled';

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'meta' => 'array',
        ];
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    /** Persian status label + chip classes for the wallet page. */
    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_DONE => 'واریز شد',
            self::STATUS_FAILED => 'ناموفق',
            self::STATUS_CANCELED => 'لغو شد',
            default => 'در انتظار پرداخت',
        };
    }

    public function statusClasses(): string
    {
        return match ($this->status) {
            self::STATUS_DONE => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300',
            self::STATUS_FAILED, self::STATUS_CANCELED => 'bg-rose-100 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300',
            default => 'bg-amber-100 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300',
        };
    }
}
