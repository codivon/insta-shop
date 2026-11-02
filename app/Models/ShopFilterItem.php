<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['shop_filter_id', 'spec_name'])]
class ShopFilterItem extends Model
{
    /** The table has no created_at/updated_at columns (migration 2026_11_15_000001). */
    public $timestamps = false;

    public function filter(): BelongsTo
    {
        return $this->belongsTo(ShopFilter::class, 'shop_filter_id');
    }
}
