<?php

namespace App\Livewire\Seller;

use App\Models\Shop;
use App\Models\ShopCategory;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.seller-slot')]
class CategoryManager extends Component
{
    public string $name = '';

    public ?int $editingId = null;

    public string $editingName = '';

    protected function shop(): Shop
    {
        return once(fn () => Shop::ensureForSeller(auth('seller')->user()));
    }

    public function add(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'min:2', 'max:80'],
        ], [
            'name.required' => 'نام دسته‌بندی را وارد کنید.',
            'name.min'      => 'نام دسته‌بندی حداقل ۲ کاراکتر است.',
            'name.max'      => 'نام دسته‌بندی حداکثر ۸۰ کاراکتر است.',
        ]);

        $shop     = $this->shop();
        $name     = trim($this->name);
        $dupCheck = ShopCategory::query()
            ->where('shop_id', $shop->id)
            ->where('name', $name)
            ->exists();

        if ($dupCheck) {
            $this->addError('name', 'دسته‌بندی‌ای با این نام از قبل وجود دارد.');

            return;
        }

        $maxSort = (int) ShopCategory::query()->where('shop_id', $shop->id)->max('sort');

        ShopCategory::query()->create([
            'shop_id' => $shop->id,
            'name'    => $name,
            'sort'    => $maxSort + 1,
        ]);

        $this->reset('name');
    }

    public function startEdit(int $id): void
    {
        $category = $this->findOwned($id);

        if (! $category) {
            return;
        }

        $this->editingId   = $category->id;
        $this->editingName = $category->name;
    }

    public function updateEdit(): void
    {
        if (! $this->editingId) {
            return;
        }

        $this->validate([
            'editingName' => ['required', 'string', 'min:2', 'max:80'],
        ], [
            'editingName.required' => 'نام دسته‌بندی را وارد کنید.',
            'editingName.min'      => 'نام دسته‌بندی حداقل ۲ کاراکتر است.',
        ]);

        $category = $this->findOwned($this->editingId);

        if (! $category) {
            return;
        }

        $name = trim($this->editingName);
        $dup  = ShopCategory::query()
            ->where('shop_id', $this->shop()->id)
            ->where('name', $name)
            ->whereKeyNot($category->id)
            ->exists();

        if ($dup) {
            $this->addError('editingName', 'دسته‌بندی‌ای با این نام از قبل وجود دارد.');

            return;
        }

        $category->update(['name' => $name]);

        $this->cancelEdit();
    }

    public function cancelEdit(): void
    {
        $this->editingId   = null;
        $this->editingName = '';
    }

    public function remove(int $id): void
    {
        $category = $this->findOwned($id);

        if (! $category) {
            return;
        }

        // Products keep their data; their category becomes "بدون دسته".
        $category->delete();
    }

    /** Tick/untick «نمایش در صفحه فروشگاه» — controls the public storefront chips. */
    public function toggleStorefront(int $id): void
    {
        $category = $this->findOwned($id);

        if (! $category) {
            return;
        }

        $category->update(['show_on_storefront' => ! $category->show_on_storefront]);
    }

    public function moveUp(int $id): void
    {
        $category = $this->findOwned($id);

        if (! $category) {
            return;
        }

        $neighbor = ShopCategory::query()
            ->where('shop_id', $this->shop()->id)
            ->where('sort', '<', $category->sort)
            ->orderByDesc('sort')
            ->first();

        if ($neighbor) {
            [$category->sort, $neighbor->sort] = [$neighbor->sort, $category->sort];
            $category->save();
            $neighbor->save();
        }
    }

    public function moveDown(int $id): void
    {
        $category = $this->findOwned($id);

        if (! $category) {
            return;
        }

        $neighbor = ShopCategory::query()
            ->where('shop_id', $this->shop()->id)
            ->where('sort', '>', $category->sort)
            ->orderBy('sort')
            ->first();

        if ($neighbor) {
            [$category->sort, $neighbor->sort] = [$neighbor->sort, $category->sort];
            $category->save();
            $neighbor->save();
        }
    }

    public function render()
    {
        $categories = ShopCategory::query()
            ->where('shop_id', $this->shop()->id)
            ->withCount('products')
            ->orderBy('sort')
            ->orderBy('id')
            ->get();

        return view('livewire.seller.category-manager', [
            'categories' => $categories,
        ])->title('مدیریت دسته‌بندی‌ها');
    }

    // ── Helpers ──────────────────────────────────────────────────────

    private function findOwned(int $id): ?ShopCategory
    {
        $category = ShopCategory::query()->find($id);

        if (! $category || $category->shop_id !== $this->shop()->id) {
            return null;
        }

        return $category;
    }
}
