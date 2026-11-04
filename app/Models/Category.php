<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Platform-wide, admin-managed category tree (Basalam-style).
 * Sellers cannot create/edit/delete categories — they only pick one
 * for each product. Level 1 rows (parent_id = null) are the main
 * categories; deeper rows are sub-categories.
 */
#[Fillable(['parent_id', 'name', 'slug', 'image', 'basalam_id', 'sort'])]
class Category extends Model
{
    protected function casts(): array
    {
        return [
            'sort' => 'integer',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /** Direct children, sort-order preserved. */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort')->orderBy('id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /** Scope: top-level (main) categories. */
    public function scopeRoots(Builder $query): Builder
    {
        return $query->whereNull('parent_id')->orderBy('sort')->orderBy('id');
    }

    /** Full subtree ids of the given category (itself included). */
    public static function subtreeIds(int $id): array
    {
        $ids = [$id];
        $frontier = [$id];

        while ($frontier !== []) {
            $next = self::query()->whereIn('parent_id', $frontier)->pluck('id')->all();

            foreach ($next as $nid) {
                $ids[] = (int) $nid;
            }

            $frontier = array_map(intval(...), $next);
        }

        return array_values(array_unique($ids));
    }

    /** Display image: own image, else nearest ancestor image. */
    public function imageOrParentUrl(): string
    {
        $node = $this;

        while ($node !== null) {
            if (filled($node->image)) {
                return (string) $node->image;
            }

            $node = $node->parent;
        }

        return '';
    }

    /** «خانه و آشپزخانه › سرویس غذاخوری» — breadcrumbs without links. */
    public function pathLabel(): string
    {
        $parts = [$this->name];
        $node = $this->parent;

        while ($node !== null) {
            array_unshift($parts, $node->name);
            $node = $node->parent;
        }

        return implode(' › ', $parts);
    }
}
