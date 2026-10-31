<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['customer_id'])]
class Cart extends Model
{
    /** Get-or-create the single cart of the given customer (safe on every request). */
    public static function ensureForCustomer(Customer $customer): self
    {
        return static::query()->firstOrCreate(['customer_id' => $customer->id]);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class)->latest('id');
    }

    /** Distinct product count (used for the tab-bar badge). */
    public function countDistinct(): int
    {
        return $this->items()->count();
    }

    /** Total quantity across items. */
    public function countUnits(): int
    {
        return (int) $this->items()->sum('quantity');
    }

    /** Cart total in Toman: Σ (unit price × qty) for purchasable items only. */
    public function totalToman(): int
    {
        return (int) $this->items()
            ->selectRaw('COALESCE(SUM(cart_items.quantity * products.price), 0) as total')
            ->join('products', 'products.id', '=', 'cart_items.product_id')
            ->where('products.status', 'published')
            ->value('total');
    }
}
