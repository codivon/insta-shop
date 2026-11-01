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

    /** Cart total in Toman: Σ (effective unit price × qty) for published products. */
    public function totalToman(): int
    {
        return (int) $this->items()
            ->with(['product:id,price,status', 'variant'])
            ->get()
            ->filter(fn ($item) => $item->product && $item->product->status === 'published')
            ->sum(fn ($item) => $item->unitPrice() * $item->quantity);
    }
}
