<?php

namespace App\Livewire\Customer;

use App\Models\ShopCategory;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;

#[Layout('layouts.customer-slot')]
class CategoriesPage extends Component
{
    /** Selected category id (0 = all). */
    public int $selected = 0;

    /** Lazy load (infinite scroll) */
    public int $perPage = 8;

    public bool $hasMore = true;

    private int $lastLoadAt = 0;

    public function select(int $categoryId): void
    {
        $this->selected = $categoryId;
        $this->perPage  = 8;
        $this->hasMore  = true;
    }

    #[On('categories-load-more')]
    public function loadMore(): void
    {
        $now = (int) (microtime(true) * 1000);

        if ($now - $this->lastLoadAt < 500) {
            return;
        }

        $this->lastLoadAt = $now;
        $this->perPage   += 8;
    }

    /** Toggle the heart on a product card. */
    public function toggleFavorite(int $productId): void
    {
        $customer = auth('customer')->user();

        if (! $customer) {
            return;
        }

        $existing = \App\Models\Favorite::query()
            ->where('customer_id', $customer->id)
            ->where('product_id', $productId)
            ->first();

        if ($existing) {
            $existing->delete();

            return;
        }

        \App\Models\Favorite::query()->firstOrCreate([
            'customer_id' => $customer->id,
            'product_id'  => $productId,
        ]);
    }

    // ── Computed data ────────────────────────────────────────────────

    public function getCategoriesProperty(): Collection
    {
        return ShopCategory::query()
            ->whereHas('shop', fn ($q) => $q->publiclyVisible())
            ->with(['shop:id,name,slug'])
            ->withCount(['products' => fn ($q) => $q->where('status', 'published')])
            ->orderBy('shop_id')
            ->orderBy('sort')
            ->orderBy('id')
            ->get()
            ->filter(fn ($cat) => $cat->products_count > 0);
    }

    public function getProductsProperty(): Collection
    {
        $total = $this->productQuery()->count();

        $rows = $this->productQuery()
            ->with(['mainImage', 'category', 'shop:id,name,slug'])
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
            'categories'  => $this->categories,
            'products'    => $this->products,
            'favoriteIds' => $this->favoriteIds,
        ])->title('دسته‌بندی‌ها');
    }

    // ── Helpers ──────────────────────────────────────────────────────

    /** Published products of public shops, optionally filtered by category. */
    private function productQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = \App\Models\Product::query()
            ->where('status', 'published')
            ->whereHas('shop', fn ($q) => $q->publiclyVisible());

        if ($this->selected !== 0) {
            $query->where('shop_category_id', $this->selected);
        }

        return $query;
    }
}
