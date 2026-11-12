<?php

namespace App\Models;

use App\Support\JDate;
use App\Support\Payments\PaymentService;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

/**
 * v0.30.0 — one shop's monthly settlement for ONE order paid through a
 * BNPL (installment) gateway (دیجی‌پی / اسنپ‌پی / ترب‌پی).
 *
 * WHY IT EXISTS: these gateways settle the MERCHANT monthly («قسط اول در
 * هنگام خرید پرداخت می‌شود، باقی اقساط را مشتری می‌پردازد و تسویهٔ پذیرنده
 * سر ماه انجام می‌شود») — so a paid BNPL order must NOT credit seller
 * wallets instantly. Instead every shop in the order gets a pending row
 * here, rendered FADED in the seller's wallet («پرداخت با اسنپ‌پی — بعد
 * از تسویه به کیف پول شما واریز می‌شود») and paid by the daily cron
 * (bnpl:settle) on/after the gateway's configured settlement day, or by
 * the admin's manual «واریز دستی».
 */
#[Fillable([
    'order_id', 'seller_id', 'wallet_id', 'gateway',
    'amount', 'commission', 'fee', 'items_total',
    'status', 'due_date', 'paid_at', 'wallet_transaction_id',
    'manual', 'note',
])]
class BnplSettlement extends Model
{
    public const STATUSES = ['pending', 'paid', 'canceled'];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'commission' => 'integer',
            'fee' => 'integer',
            'items_total' => 'integer',
            'due_date' => 'date',
            'paid_at' => 'datetime',
            'manual' => 'boolean',
        ];
    }

    /* ─────────────────────────── Relations ─────────────────────────── */

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    /* ──────────────────────────── Labels ───────────────────────────── */

    public function statusLabel(): string
    {
        return match ($this->status) {
            'paid' => 'واریز شد',
            'canceled' => 'لغو شد',
            default => 'در انتظار تسویه',
        };
    }

    public function statusClasses(): string
    {
        return match ($this->status) {
            'paid' => 'bg-emerald-50 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-400',
            'canceled' => 'bg-rose-50 text-rose-600 dark:bg-rose-950/40 dark:text-rose-400',
            default => 'bg-amber-50 text-amber-600 dark:bg-amber-950/40 dark:text-amber-400',
        };
    }

    /** Gateway display name (falls back to the raw key like Order::gatewayLabel). */
    public function gatewayLabel(): string
    {
        return PaymentService::BNPL_GATEWAYS[$this->gateway]
            ?? (string) $this->gateway;
    }

    /** Persian due date, e.g. «۱۴۰۵/۰۹/۰۱». */
    public function dueDateFa(): string
    {
        return $this->due_date ? JDate::date($this->due_date) : '';
    }

    /* ─────────────────────── State transitions ─────────────────────── */

    /**
     * Atomically claim a PENDING settlement for deposit — returns the
     * fresh row when this caller won the pending→paid race, null when a
     * concurrent cron/admin action already handled it (idempotent).
     *
     * @return self|null the claimed row (status already flipped to paid)
     */
    public function claimForDeposit(): ?self
    {
        return DB::transaction(function (): ?self {
            $fresh = static::query()
                ->whereKey($this->id)
                ->where('status', 'pending')
                ->lockForUpdate()
                ->first();

            if (! $fresh) {
                return null;
            }

            $fresh->forceFill([
                'status' => 'paid',
                'paid_at' => now(),
                'wallet_transaction_id' => $fresh->wallet_transaction_id,
            ])->save();

            return $fresh;
        });
    }
}
