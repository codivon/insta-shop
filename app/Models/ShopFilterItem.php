<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['shop_filter_id', 'spec_name'])]
class ShopFilterItem extends Model
{
    public function filter(): BelongsTo
    {
        return $this->belongsTo(ShopFilter::class, 'shop_filter_id');
    }
}
