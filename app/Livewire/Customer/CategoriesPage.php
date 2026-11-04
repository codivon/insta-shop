<?php

namespace App\Livewire\Customer;

use App\Models\Category;
use App\Models\Favorite;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Browse the platform (Basalam-style) category tree: main categories as
 * image cards → sub-category chips → product grid. Picking any node of
 * the tree filters products by that whole branch.
 */
#[Layout('layouts.customer-slot')]
class CategoriesPage extends Component
{
    /** Selected category id (0 = all). */
    public int $selected = 0;

    /** Which main category panel is open (0 = root cards grid). */
    public int $openRoot = 0;

    /** Lazy load (infinite scroll) */
    public int $perPage = 8;

    public bool $hasMore = true;

    private int $lastLoadAt = 0;

    public function select(int $categoryId): void
    {
        $this->selected = $categoryId;
        $this->openRoot = $this->rootAncestorId($categoryId);
        $this->perPage = 8;
        $this->hasMore = true;
    }

    /** Back to the main-category grid. */
    public function backToRoots(): void
    {
        $this->select(0);
    }

    #[On('categories-load-more')]
    public function loadMore(): void
    {
        $now = (int) (microtime(true) * 1000);

        if ($now - $this->lastLoadAt < 500) {
            return;
        }

        $this->lastLoadAt = $now;
        $this->perPage += 8;
    }

    /** Toggle the heart on a product card. */
    public function toggleFavorite(int $productId): void
    {
        $customer = auth('customer')->user();

        if (! $customer) {
            return;
        }

        $existing = Favorite::query()
            ->where('customer_id', $customer->id)
            ->where('product_id', $productId)
            ->first();

        if ($existing) {
            $existing->delete();

            return;
        }

        Favorite::query()->firstOrCreate([
            'customer_id' => $customer->id,
            'product_id' => $productId,
        ]);
    }

    // ── Computed data ────────────────────────────────────────────────

    /** Published-product counts per category id (across public shops). */
    private function publishedCounts(): array
    {
        $rows = Product::query()
            ->where('status', 'published')
            ->whereHas('shop', fn ($q) => $q->publiclyVisible())
            ->whereNotNull('category_id')
            ->get(['category_id'])
            ->countBy('category_id');

        return $rows->all();
    }

    /** Main categories (with images) + product counts across their subtree. */
    public function getRootsProperty(): Collection
    {
        $counts = $this->publishedCounts();

        $all = Category::query()->orderBy('sort')->orderBy('id')->get(['id', 'parent_id', 'name', 'image']);

        $byParent = $all->groupBy('parent_id');

        $subtreeCount = function (Category $node) use (&$subtreeCount, $byParent, $counts): int {
            $sum = (int) ($counts[$node->id] ?? 0);

            foreach ($byParent->get($node->id, collect()) as $child) {
                $sum += $subtreeCount($child);
            }

            return $sum;
        };

        return Category::query()
            ->whereNull('parent_id')
            ->orderBy('sort')
            ->orderBy('id')
            ->get(['id', 'name', 'image'])
            ->each(function (Category $root) use ($subtreeCount) {
                // re-hydrate a lightweight tree node for counting
                $node = new Category(['id' => $root->id, 'parent_id' => null, 'name' => $root->name, 'image' => $root->image]);
                $root->products_total = $subtreeCount($node);
            });
    }

    /** Sub-categories (level 2) of the open main category + branch counts. */
    public function getSubcategoriesProperty(): Collection
    {
        if ($this->openRoot <= 0) {
            return collect();
        }

        $counts = $this->publishedCounts();

        $all = Category::query()->orderBy('sort')->orderBy('id')->get(['id', 'parent_id', 'name']);
        $byParent = $all->groupBy('parent_id');

        $subtreeCount = function (Category $node) use (&$subtreeCount, $byParent, $counts): int {
            $sum = (int) ($counts[$node->id] ?? 0);

            foreach ($byParent->get($node->id, collect()) as $child) {
                $sum += $subtreeCount($child);
            }

            return $sum;
        };

        return Category::query()
            ->where('parent_id', $this->openRoot)
            ->orderBy('sort')
            ->orderBy('id')
            ->get(['id', 'name'])
            ->each(function (Category $sub) use ($subtreeCount) {
                $node = new Category(['id' => $sub->id, 'parent_id' => $this->openRoot, 'name' => $sub->name]);
                $sub->products_total = $subtreeCount($node);
            });
    }

    public function getSelectedNameProperty(): string
    {
        if ($this->selected === 0) {
            return 'همه محصولات';
        }

        return Category::query()->whereKey($this->selected)->value('name') ?? 'همه محصولات';
    }

    public function getProductsProperty(): Collection
    {
        $total = $this->productQuery()->count();

        $rows = $this->productQuery()
            ->with(['mainImage', 'category', 'shop.seller:id,instagram_username'])
            ->latest()
            ->limit($this->perPage)
            ->get();

        $this->hasMore = $total > $this->perPage;

        return $rows;
    }

    public function getFavoriteIdsProperty(): array
    {
        $customer = auth('customer')->user();

        return $customer ? $customer->favoriteProductIds() : [];
    }

    public function render()
    {
        return view('livewire.customer.categories-page', [
            'roots' => $this->roots,
            'subcategories' => $this->subcategories,
            'products' => $this->products,
            'favoriteIds' => $this->favoriteIds,
            'selectedName' => $this->selectedName,
        ])->title('دسته‌بندی‌ها');
    }

    // ── Helpers ──────────────────────────────────────────────────────

    private function rootAncestorId(int $categoryId): int
    {
        if ($categoryId <= 0) {
            return 0;
        }

        $node = Category::query()->with('parent')->find($categoryId);

        while ($node !== null && $node->parent_id !== null) {
            $node = $node->parent;
        }

        return (int) ($node->id ?? 0);
    }

    /** Published products of public shops, optionally filtered by branch. */
    private function productQuery(): Builder
    {
        $query = Product::query()
            ->where('status', 'published')
            ->whereHas('shop', fn ($q) => $q->publiclyVisible());

        if ($this->selected !== 0) {
            $query->whereIn('category_id', Category::subtreeIds($this->selected));
        }

        return $query;
    }
}
