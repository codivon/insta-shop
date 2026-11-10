<?php

namespace App\Models;

use App\Support\Fmt;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['wallet_id', 'type', 'amount', 'commission', 'status', 'order_id', 'description', 'sheba', 'card_number', 'admin_note'])]
class WalletTransaction extends Model
{
    public const TYPE_DEPOSIT = 'deposit';

    public const TYPE_WITHDRAWAL = 'withdrawal';

    public const TYPE_PLAN = 'plan'; // v0.22.0 — خرید اشتراک از کیف پول

    public const STATUS_DONE = 'done';

    public const STATUS_PENDING = 'pending';

    public const STATUS_REJECTED = 'rejected';

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'order_id' => 'integer',
        ];
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function isDeposit(): bool
    {
        return $this->type === self::TYPE_DEPOSIT;
    }

    public function isWithdrawal(): bool
    {
        return $this->type === self::TYPE_WITHDRAWAL;
    }

    /** Whether this row SPENDS wallet money (withdrawals + plan purchases). */
    public function isSpend(): bool
    {
        return $this->type === self::TYPE_WITHDRAWAL || $this->type === self::TYPE_PLAN;
    }

    /** Persian label of the transaction type. */
    public function typeLabel(): string
    {
        return match ($this->type) {
            self::TYPE_DEPOSIT => 'واریز فروش',
            self::TYPE_PLAN => 'خرید اشتراک',
            default => 'درخواست برداشت',
        };
    }

    /** Persian label + classes of the status chip. */
    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'در انتظار تأیید',
            self::STATUS_REJECTED => 'رد شد',
            default => 'تسویه شد',
        };
    }

    public function statusClasses(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'bg-amber-100 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300',
            self::STATUS_REJECTED => 'bg-rose-100 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300',
            default => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300',
        };
    }

    /** Formatted amount with a leading sign for deposits. */
    public function amountLabel(): string
    {
        return ($this->isDeposit() ? '+' : '−').Fmt::money($this->amount);
    }

    /** Chip classes of the transaction type. */
    public function typeClasses(): string
    {
        return match ($this->type) {
            self::TYPE_DEPOSIT => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300',
            self::TYPE_PLAN => 'bg-violet-100 text-violet-700 dark:bg-violet-950/60 dark:text-violet-300',
            default => 'bg-slate-100 text-slate-700 dark:bg-slate-950/60 dark:text-slate-300',
        };
    }
}
