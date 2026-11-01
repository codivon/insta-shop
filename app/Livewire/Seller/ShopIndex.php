<?php

namespace App\Livewire\Seller;

use App\Models\Product;
use App\Models\Shop;
use App\Models\ShopCategory;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;

#[Layout('layouts.seller-slot')]
class ShopIndex extends Component
{
    /** Filters */
    public string $search = '';

    public int $categoryFilter = 0;          // 0 = all categories

    public string $statusFilter = 'all';     // all|published|draft

    public string $stockFilter = 'all';      // all|in|out

    public string $specialFilter = 'all';    // all|special|plain  (فروش ویژه)

    /** Lazy load (infinite scroll) */
    public int $perPage = 8;

    public bool $hasMore = true;

    private int $lastLoadAt = 0;

    public function mount(): void
    {
        // Defensive: also ensure the shop exists for sellers created before Phase 3.
        $this->shop();
    }

    /** The single shop of the logged-in seller (cached per request via once()). */
    protected function shop(): Shop
    {
        return once(fn () => Shop::ensureForSeller(auth('seller')->user()));
    }

    /** Reset pagination when any filter changes. */
    public function updating(string $property): void
    {
        if (in_array($property, ['search', 'categoryFilter', 'statusFilter', 'stockFilter', 'specialFilter'], true)) {
            $this->perPage = 8;
            $this->hasMore = true;
        }
    }

    /** Triggered by the IntersectionObserver sentinel at the bottom of the list. */
    #[On('shop-load-more')]
    public function loadMore(): void
    {
        $now = (int) (microtime(true) * 1000);

        if ($now - $this->lastLoadAt < 500) {
            return;
        }

        $this->lastLoadAt = $now;
        $this->perPage   += 8;
    }

    public function togglePublish(int $productId): void
    {
        $product = $this->findOwnedProduct($productId);

        $product->update([
            'status' => $product->status === 'published' ? 'draft' : 'published',
        ]);
    }

    public function removeProduct(int $productId): void
    {
        $product = Product::query()->with('images')->find($productId);

        if (! $product || $product->shop_id !== $this->shop()->id) {
            return;
        }

        foreach ($product->images as $image) {
            $image->deleteFile();
        }

        $product->delete();

        session()->flash('shop-flash', 'محصول حذف شد.');
    }

    public function resetFilters(): void
    {
        $this->search        = '';
        $this->categoryFilter = 0;
        $this->statusFilter  = 'all';
        $this->stockFilter   = 'all';
        $this->specialFilter = 'all';
        $this->perPage       = 8;
        $this->hasMore       = true;
    }

    // ── Computed data ────────────────────────────────────────────────

    public function getShopProperty(): Shop
    {
        return $this->shop();
    }

    /** Public storefront address (only when the shop is publicly visible). */
    public function getStorefrontUrlProperty(): ?string
    {
        $seller = auth('seller')->user();

        if (! $seller || $seller->status !== 'approved') {
            return null; // pending shops 404 publicly → hide the button
        }

        $shop   = $this->shop();
        $handle = $shop->instagram_username ?: $seller->instagram_username;

        return $handle ? route('shop.storefront', ['instagram' => $handle]) : null;
    }

    public function getStatsProperty(): array
    {
        $base = Product::query()->where('shop_id', $this->shop()->id);

        return [
            'total'     => (clone $base)->count(),
            'published' => (clone $base)->where('status', 'published')->count(),
            'outOfStock' => (clone $base)->where('stock', 0)->count(),
            'categories' => ShopCategory::query()->where('shop_id', $this->shop()->id)->count(),
            'special'   => (clone $base)->specialActive()->count(),
        ];
    }

    public function getCategoriesProperty(): Collection
    {
        return ShopCategory::query()
            ->where('shop_id', $this->shop()->id)
            ->withCount('products')
            ->orderBy('sort')
            ->orderBy('id')
            ->get();
    }

    public function getProductsProperty(): Collection
    {
        $total = $this->filteredQuery()->count();

        $rows = $this->filteredQuery()
            ->with(['mainImage', 'category'])
            ->latest()
            ->limit($this->perPage)
            ->get();

        $this->hasMore = $total > $this->perPage;

        return $rows;
    }

    public function render()
    {
        return view('livewire.seller.shop-index', [
            'shop'          => $this->shop,
            'stats'         => $this->stats,
            'storefrontUrl' => $this->storefrontUrl,
            'categories'    => $this->categories,
            'products'      => $this->products,
        ])->title('فروشگاه من');
    }

    // ── Helpers ──────────────────────────────────────────────────────

    private function findOwnedProduct(int $productId): Product
    {
        $product = Product::query()->findOrFail($productId);

        abort_unless($product->shop_id === $this->shop()->id, 404);

        return $product;
    }

    private function filteredQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = Product::query()->where('shop_id', $this->shop()->id);

        if ($this->search !== '') {
            $query->where('name', 'like', '%'.$this->search.'%');
        }

        if ($this->categoryFilter !== 0) {
            $query->where('shop_category_id', $this->categoryFilter);
        }

        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        if ($this->stockFilter === 'in') {
            $query->where('stock', '>', 0);
        } elseif ($this->stockFilter === 'out') {
            $query->where('stock', 0);
        }

        if ($this->specialFilter === 'special') {
            $query->where('is_special', true);
        } elseif ($this->specialFilter === 'plain') {
            $query->where('is_special', false);
        }

        return $query;
    }
}
