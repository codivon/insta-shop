<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['order_id', 'gateway', 'event', 'transaction_id', 'amount', 'ok', 'message', 'meta'])]
class PaymentLog extends Model
{
    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'ok'     => 'boolean',
            'meta'   => 'array',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /** Thin audit helper — never throws, logging must not break payments. */
    public static function record(
        Order $order,
        string $gateway,
        string $event,
        ?string $transactionId = null,
        ?int $amount = null,
        bool $ok = true,
        ?string $message = null,
        ?array $meta = null,
    ): ?self {
        try {
            return static::query()->create([
                'order_id'       => $order->id,
                'gateway'        => $gateway,
                'event'          => $event,
                'transaction_id' => $transactionId,
                'amount'         => $amount,
                'ok'             => $ok,
                'message'        => $message,
                'meta'           => $meta,
            ]);
        } catch (\Throwable) {
            return null;
        }
    }
}
