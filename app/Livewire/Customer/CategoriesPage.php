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
 * and the best-sellers of that branch.
 */
#[Layout('layouts.customer-slot')]
class CategoriesPage extends Component
{
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

    public function render()
    {
        return view('livewire.customer.categories-page', [
            'sections' => $this->sections,
        ])->title('دسته‌بندی‌ها');
    }
}
