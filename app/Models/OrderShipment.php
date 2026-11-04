<?php

namespace App\Models;

use App\Support\IranCalendar;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'order_id', 'shop_id',
    'shipment_status', 'tracking_code', 'shipment_status_at',
    'canceled_at', 'cancel_reason', 'canceled_by', 'refund_amount',
    'items_total', 'shipping_cost', 'discount_amount', 'total',
    'delivery_date',
])]
class OrderShipment extends Model
{
    public const STATUSES = ['none', 'processing', 'shipped', 'delivered'];

    protected function casts(): array
    {
        return [
            'items_total' => 'integer',
            'shipping_cost' => 'integer',
            'discount_amount' => 'integer',
            'total' => 'integer',
            'refund_amount' => 'integer',
            'shipment_status_at' => 'datetime',
            'canceled_at' => 'datetime',
            'delivery_date' => 'date',
        ];
    }

    /* ─────────────────────────── Relations ─────────────────────────── */

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    /* ──────────────────────────── State ────────────────────────────── */

    public function isCanceled(): bool
    {
        return $this->canceled_at !== null;
    }

    /** Persian label of the shipment status. */
    public function statusLabel(): string
    {
        return match ($this->shipment_status) {
            'processing' => 'در حال پردازش',
            'shipped' => 'در حال ارسال',
            'delivered' => 'تحویل شد',
            default => 'پردازش نشده',
        };
    }

    /** Tailwind chip classes per shipment status. */
    public function statusClasses(): string
    {
        return match ($this->shipment_status) {
            'processing' => 'bg-sky-50 text-sky-600 dark:bg-sky-950/40 dark:text-sky-400',
            'shipped' => 'bg-violet-50 text-violet-600 dark:bg-violet-950/40 dark:text-violet-400',
            'delivered' => 'bg-emerald-50 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-400',
            default => 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400',
        };
    }

    /** Step index for progress steppers (0..3). */
    public function stepIndex(): int
    {
        return match ($this->shipment_status) {
            'processing' => 1,
            'shipped' => 2,
            'delivered' => 3,
            default => 0,
        };
    }

    /* ────────────────── customer-picked delivery day ────────────────── */

    /** The Y-m-d the customer picked at checkout (null = not picked). */
    public function deliveryDateRaw(): ?string
    {
        return $this->delivery_date ? $this->delivery_date->toDateString() : null;
    }

    /** «پنجشنبه ۹ آبان ۱۴۰۵» — the chosen delivery day, human label. */
    public function deliveryDateLabel(): string
    {
        return $this->delivery_date
            ? IranCalendar::longLabel($this->delivery_date)
            : '';
    }
}
