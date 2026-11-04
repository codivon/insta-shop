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
 * Customer — ONE category page, Basalam-style: breadcrumb, the child
 * categories as image cards, then the best-sellers of the whole branch
 * (paid-order quantity, newest first as the tiebreak / when nobody has
 * sold anything yet). Infinite scroll keeps feeding the grid.
 */
#[Layout('layouts.customer-slot')]
class CategoryShowPage extends Component
{
    public Category $category;

    /** Lazy load (infinite scroll) */
    public int $perPage = 8;

    public bool $hasMore = true;

    private int $lastLoadAt = 0;

    public function mount(Category $category): void
    {
        $this->category = $category;
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

    /** Direct children as image cards (image fallback = this category's). */
    public function getChildrenProperty(): Collection
    {
        return $this->category->children()
            ->get(['id', 'parent_id', 'name', 'image'])
            ->each(fn (Category $child) => $child->image = $child->image ?: $this->category->image);
    }

    /** Ancestors root → … → this category (breadcrumb trail). */
    public function getTrailProperty(): Collection
    {
        $trail = collect([$this->category]);

        $node = $this->category->parent;

        while ($node !== null) {
            $trail->prepend($node);
            $node = $node->parent;
        }

        return $trail;
    }

    /** Published products of the whole branch, best-seller first. */
    public function getProductsProperty(): Collection
    {
        $base = $this->productQuery();

        $total = (clone $base)->count();

        $rows = $base
            ->with(['mainImage', 'category', 'shop.seller:id,instagram_username'])
            // Alias must be exactly `paid_sales`: MySQL resolves ORDER BY/backtick
            // identifiers strictly against it (sqlite would silently treat a
            // mismatched "paid_sales" as a constant string and skip the sort).
            ->withSum(['orderItems as paid_sales' => fn ($q) => $q->whereHas('order', fn ($w) => $w->where('status', 'paid'))], 'quantity')
            ->orderByDesc('paid_sales')
            ->orderByDesc('id')
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
        return view('livewire.customer.category-show-page', [
            'children' => $this->children,
            'trail' => $this->trail,
            'products' => $this->products,
            'favoriteIds' => $this->favoriteIds,
        ])->title($this->category->name);
    }

    // ── Helpers ──────────────────────────────────────────────────────

    /** Published products of public shops inside this branch (incl. self). */
    private function productQuery(): Builder
    {
        return Product::query()
            ->where('status', 'published')
            ->whereHas('shop', fn ($q) => $q->publiclyVisible())
            ->whereIn('category_id', Category::subtreeIds($this->category->id));
    }
}
