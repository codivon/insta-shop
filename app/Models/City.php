<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['province_id', 'name', 'latitude', 'longitude', 'ordering', 'is_active'])]
class City extends Model
{
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class);
    }

    /** Scope: cities visible to panels (admin can still see all). */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
