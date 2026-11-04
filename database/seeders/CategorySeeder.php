<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

/**
 * Seeds the platform category tree from the Basalam navigation dump
 * (database/seeders/data/basalam-categories.php):
 *
 *   14 root categories → 119 sub-categories → 983 sub-sub-categories
 *
 * Each row keeps the Basalam id, Persian title, slug, image URL and
 * display rank. Idempotent: rows are matched on basalam_id, so re-running
 * refreshes titles/images without breaking product assignments.
 */
class CategorySeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        /** @var array<int, array{id:int,title:string,slug:string,image:string,level:int,parent_id:int,rank:int,children:array}> $tree */
        $tree = require database_path('seeders/data/basalam-categories.php');

        $this->walk($tree, null);
    }

    private function walk(array $nodes, ?int $parentId): void
    {
        foreach ($nodes as $index => $node) {
            $slug = filled($node['slug']) ? trim((string) $node['slug']) : null;

            // Slugs are unique in our table — Basalam slugs are not. On a
            // collision, disambiguate with the numeric Basalam id.
            if ($slug !== null) {
                $taken = Category::query()
                    ->where('slug', $slug)
                    ->where(fn ($q) => $q->whereNull('basalam_id')->orWhere('basalam_id', '!=', (int) $node['id']))
                    ->exists();

                if ($taken) {
                    $slug .= '-'.$node['id'];
                }
            }

            $category = Category::query()->updateOrCreate(
                ['basalam_id' => (int) $node['id']],
                [
                    'parent_id' => $parentId,
                    'name' => trim((string) $node['title']),
                    'slug' => $slug,
                    'image' => filled($node['image']) ? trim((string) $node['image']) : null,
                    'sort' => max(1, (int) $node['rank']) ?: ($index + 1),
                ],
            );

            if (! empty($node['children'])) {
                $this->walk($node['children'], $category->id);
            }
        }
    }
}
