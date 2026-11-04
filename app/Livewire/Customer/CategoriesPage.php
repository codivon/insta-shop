<?php

namespace App\Livewire\Customer;

use App\Models\Category;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Customer — categories HOME, Basalam-style: each MAIN category is a
 * section (image + name + «همه» link) followed by a horizontal row of
 * its CHILD categories as image cards. Tapping a card / «همه» opens
 * the dedicated category page (CategoryShowPage) with the deeper tree
 * and the best-sellers of that branch. The header search box filters
 * the WHOLE tree by category name.
 */
#[Layout('layouts.customer-slot')]
class CategoriesPage extends Component
{
    /** The flat search view shows at most this many matches. */
    private const RESULT_LIMIT = 30;

    /** Free-text filter over every category name in the tree. */
    public string $search = '';

    /** Roots with their direct children (images resolved, fallback = root image). */
    public function getSectionsProperty(): Collection
    {
        return Category::query()
            ->roots()
            ->with(['children:id,parent_id,name,image,sort'])
            ->get(['id', 'name', 'image'])
            ->each(function (Category $root) {
                $root->children->each(function (Category $child) use ($root) {
                    // 2 of 1116 nodes ship without an image — fall back to the parent's
                    $child->image = $child->image ?: $root->image;
                });
            });
    }

    /** Name matches from the whole tree, most relevant first. */
    public function getResultsProperty(): Collection
    {
        $needle = trim($this->search);

        if ($needle === '') {
            return collect();
        }

        $rows = Category::query()
            ->where('name', 'like', '%'.$needle.'%')
            ->with('parent.parent.parent.parent') // pathLabel()/imageOrParentUrl() stay in-memory
            ->limit(self::RESULT_LIMIT)
            ->get(['id', 'parent_id', 'name', 'image']);

        // Names that START with the query first, then shorter names, then by id.
        return $rows->sort(function (Category $a, Category $b) use ($needle) {
            $aKey = [(int) ! str_starts_with($a->name, $needle), mb_strlen($a->name), $a->id];
            $bKey = [(int) ! str_starts_with($b->name, $needle), mb_strlen($b->name), $b->id];

            return $aKey <=> $bKey;
        })->values();
    }

    /** Total matches — may exceed the displayed RESULT_LIMIT. */
    public function getResultsTotalProperty(): int
    {
        $needle = trim($this->search);

        return $needle === '' ? 0 : Category::query()->where('name', 'like', '%'.$needle.'%')->count();
    }

    public function render()
    {
        return view('livewire.customer.categories-page', [
            'sections' => $this->sections,
            'results' => $this->results,
            'resultsTotal' => $this->resultsTotal,
        ])->title('دسته‌بندی‌ها');
    }
}
