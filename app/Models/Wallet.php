<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['seller_id', 'balance'])]
class Wallet extends Model
{
    protected function casts(): array
    {
        return [
            'balance' => 'integer',
        ];
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class)->latest('id');
    }

    /** Get-or-create the wallet of a seller id. */
    public static function ensureForSeller(int $sellerId): self
    {
        return static::query()->firstOrCreate(['seller_id' => $sellerId]);
    }
}
