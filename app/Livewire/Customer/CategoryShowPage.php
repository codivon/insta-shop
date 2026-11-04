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
 * categories as image cards, then the product showcases — «پرفروش‌ترین‌ها»
 * (paid-order quantity, newest as tiebreak) and «جدیدترین‌ها» — and finally
 * the whole branch feed «همهٔ محصولات» with infinite scroll. The showcases
 * only appear when the branch holds more than one page (8) of products,
 * otherwise they would just repeat the feed below them. A live search box
 * filters the branch by product name and replaces the showcases while
 * active.
 */
#[Layout('layouts.customer-slot')]
class CategoryShowPage extends Component
{
    /** Showcases are pointless until they beat one full page of the feed. */
    private const SHOWCASE_SIZE = 8;

    public Category $category;

    /** In-branch product search («جستجو در این دسته‌بندی»). */
    public string $search = '';

    /** Lazy load (infinite scroll) */
    public int $perPage = 8;

    public bool $hasMore = true;

    private int $lastLoadAt = 0;

    public function mount(Category $category): void
    {
        $this->category = $category;
    }

    /** A new search term → back to the first page of the feed. */
    public function updating(string $property): void
    {
        if ($property === 'search') {
            $this->perPage = self::SHOWCASE_SIZE;
            $this->hasMore = true;
        }
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

    /** Every published product of the branch — size of the whole catalog. */
    public function getTotalProperty(): int
    {
        return (clone $this->productQuery())->count();
    }

    /** Top sellers of the branch: paid-order quantity, newest tiebreak. */
    public function getBestSellersProperty(): Collection
    {
        return $this->feedQuery()
            ->orderByDesc('paid_sales')
            ->orderByDesc('id')
            ->limit(self::SHOWCASE_SIZE)
            ->get();
    }

    /** Freshest products of the branch. */
    public function getNewestProperty(): Collection
    {
        return $this->feedQuery()
            ->orderByDesc('id')
            ->limit(self::SHOWCASE_SIZE)
            ->get();
    }

    /** The whole branch feed (newest first) — grows with infinite scroll. */
    public function getProductsProperty(): Collection
    {
        $rows = $this->feedQuery()
            ->orderByDesc('id')
            ->limit($this->perPage)
            ->get();

        $this->hasMore = $this->total > $this->perPage;

        return $rows;
    }

    public function getFavoriteIdsProperty(): array
    {
        $customer = auth('customer')->user();

        return $customer ? $customer->favoriteProductIds() : [];
    }

    /** True while the visitor is filtering the branch by a search term. */
    public function getSearchingProperty(): bool
    {
        return filled($this->search);
    }

    public function render()
    {
        $searching = $this->searching;

        return view('livewire.customer.category-show-page', [
            'children' => $this->children,
            'trail' => $this->trail,
            'total' => $this->total,
            // Showcases are pointless (and wrong-scoped) while searching.
            'bestSellers' => $searching ? collect() : $this->bestSellers,
            'newest' => $searching ? collect() : $this->newest,
            'products' => $this->products,
            'favoriteIds' => $this->favoriteIds,
        ])->title($this->category->name);
    }

    // ── Helpers ──────────────────────────────────────────────────────

    /** Published products of public shops inside this branch (incl. self). */
    private function productQuery(): Builder
    {
        $query = Product::query()
            ->where('status', 'published')
            ->whereHas('shop', fn ($q) => $q->publiclyVisible())
            ->whereIn('category_id', Category::subtreeIds($this->category->id));

        if (filled($this->search)) {
            $query->where('name', 'like', '%'.trim($this->search).'%');
        }

        return $query;
    }

    /**
     * productQuery + the eager loads and the paid-sales sum every product
     * card needs. Alias must be exactly `paid_sales`: MySQL resolves
     * ORDER BY/backtick identifiers strictly against it (sqlite would
     * silently treat a mismatched "paid_sales" as a constant string and
     * skip the sort).
     */
    private function feedQuery(): Builder
    {
        return $this->productQuery()
            ->with(['mainImage', 'category', 'shop.seller:id,instagram_username'])
            ->withSum(['orderItems as paid_sales' => fn ($q) => $q->whereHas('order', fn ($w) => $w->where('status', 'paid'))], 'quantity');
    }
}
