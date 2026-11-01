<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['cart_id', 'product_id', 'variant_id', 'quantity'])]
class CartItem extends Model
{
    protected function casts(): array
    {
        return [
            'variant_id' => 'integer',
            'quantity'   => 'integer',
        ];
    }

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /** The picked combination (only meaningful when variant_id > 0). */
    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    /** Line identity key: the variant id, or 0 for a plain product. */
    public function variantKey(): int
    {
        return (int) $this->variant_id;
    }

    /** Effective unit price: variant price (discount-aware) or the product price. */
    public function unitPrice(): int
    {
        if ($this->variant_id > 0 && $this->variant) {
            return $this->variant->finalPrice();
        }

        return (int) ($this->product->price ?? 0);
    }

    /** Line total in Toman. */
    public function lineTotal(): int
    {
        return $this->unitPrice() * $this->quantity;
    }
}
