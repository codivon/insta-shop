<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/**
 * Admin-managed home visuals: the HERO SLIDER slides (position=home_hero)
 * and the BANNER CARDS row below it (position=home_banner) — two separate
 * admin pages since v0.17.1 (اسلایدر اصلی / بنرها).
 */
class Banner extends Model
{
    public const POSITION_HERO = 'home_hero';

    public const POSITION_BANNER = 'home_banner';

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
        return $query->where('position', self::POSITION_HERO)
            ->where('is_active', true)
            ->orderBy('sort')
            ->orderBy('id');
    }

    /** Active banner cards of the home row, sort order preserved. */
    public function scopeHomeBanners(Builder $query): Builder
    {
        return $query->where('position', self::POSITION_BANNER)
            ->where('is_active', true)
            ->orderBy('sort')
            ->orderBy('id');
    }

    public function imageUrl(): string
    {
        return Storage::disk('public')->url($this->image_path);
    }
}
