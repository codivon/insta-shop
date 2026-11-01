<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['order_id', 'shop_id', 'product_id', 'product_name', 'variant_id', 'variant_label', 'unit_price', 'quantity'])]
class OrderItem extends Model
{
    protected function casts(): array
    {
        return [
            'variant_id' => 'integer',
            'unit_price' => 'integer',
            'quantity'   => 'integer',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    /** Snapshot line total (Toman). */
    public function lineTotal(): int
    {
        return $this->unit_price * $this->quantity;
    }
}
