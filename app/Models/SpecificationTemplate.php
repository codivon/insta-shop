<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Reusable spec-group library per shop (نوع مشخصات).
 * `groups` shape: [{name: 'مشخصات کلی', items: [{title: 'اندازه'}]}]
 */
#[Fillable(['shop_id', 'title', 'groups'])]
class SpecificationTemplate extends Model
{
    protected function casts(): array
    {
        return [
            'groups' => 'array',
        ];
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    /**
     * Merge a product's current spec groups into the template:
     * existing template titles stay, new group names / item titles
     * created on the product are appended (superset), order preserved.
     *
     * @param  array  $productGroups  normalized product specs [{name, items:[{title, value}]}]
     */
    public function mergeGroups(array $productGroups): void
    {
        $template = $this->groups ?? [];

        // index existing template item titles per group name
        $byName = [];

        foreach ($template as $group) {
            $name = trim((string) ($group['name'] ?? ''));
            $byName[$name] = $group;
        }

        foreach ($productGroups as $group) {
            $name = trim((string) ($group['name'] ?? ''));
            $titles = [];

            foreach ($byName[$name]['items'] ?? [] as $item) {
                $titles[] = trim((string) ($item['title'] ?? ''));
            }

            foreach (($group['items'] ?? []) as $item) {
                $title = trim((string) ($item['title'] ?? ''));
                if ($title !== '' && ! in_array($title, $titles, true)) {
                    $titles[] = $title;
                }
            }

            $items = [];

            foreach ($titles as $title) {
                $items[] = ['title' => $title];
            }

            $byName[$name] = ['name' => $name, 'items' => $items];
        }

        $this->groups = array_values($byName);
    }
}
