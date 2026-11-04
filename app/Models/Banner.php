<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/**
 * Home hero slider slide, managed by the admin (بنرها و اسلایدها).
 */
class Banner extends Model
{
    protected $fillable = ['title', 'image_path', 'link_url', 'position', 'sort', 'is_active'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort' => 'integer',
        ];
    }

    /** Active slides of the home hero, sort order preserved. */
    public function scopeHomeHero(Builder $query): Builder
    {
        return $query->where('position', 'home_hero')
            ->where('is_active', true)
            ->orderBy('sort')
            ->orderBy('id');
    }

    public function imageUrl(): string
    {
        return Storage::disk('public')->url($this->image_path);
    }
}
