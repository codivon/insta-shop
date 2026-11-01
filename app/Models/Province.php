<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'latitude', 'longitude', 'ordering', 'is_active'])]
class Province extends Model
{
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function cities(): HasMany
    {
        return $this->hasMany(City::class)->orderBy('ordering')->orderBy('id');
    }

    /** Scope: provinces visible to panels (admin can still see all). */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
