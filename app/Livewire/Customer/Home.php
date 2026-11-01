<?php

namespace App\Livewire\Customer;

use App\Models\Cart;
use App\Models\Customer;
use App\Models\Favorite;
use App\Models\Product;
use App\Models\Shop;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;

#[Layout('layouts.customer-slot')]
class Home extends Component
{
    /** Search term (live, debounced from the view). */
    public string $search = '';

    /** newest | popular | cheap | expensive */
    public string $sort = 'newest';

    /** Lazy load (infinite scroll) */
    public int $perPage = 6;

    public bool $hasMore = true;

    private int $lastLoadAt = 0;

    public function updating(string $property): void
    {
        if (in_array($property, ['search', 'sort'], true)) {
            $this->perPage = 6;
            $this->hasMore = true;
        }
    }

    /** Triggered by the IntersectionObserver sentinel at the bottom of the grid. */
    #[On('home-load-more')]
    public function loadMore(): void
    {
        $now = (int) (microtime(true) * 1000);

        if ($now - $this->lastLoadAt < 500) {
            return;
        }

        $this->lastLoadAt = $now;
        $this->perPage   += 6;
    }

    /** Toggle the heart on a product card. */
    public function toggleFavorite(int $productId): void
    {
        $customer = $this->customer();

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
            'product_id'  => $productId,
        ]);
    }

    /** Quick add-to-cart straight from the product card. */
    public function addToCart(int $productId): void
    {
        $customer = $this->customer();
        $product  = $this->publicProductQuery()->whereKey($productId)->first();

        if (! $customer || ! $product) {
            return;
        }

        // Variant products need an explicit combination choice → product page.
        if ($product->variants_count > 0) {
            $this->redirect(route('shop.product.show', $productId), navigate: true);

            return;
        }

        if ($product->type !== Product::TYPE_DOWNLOADABLE && $product->stock < 1) {
            $this->dispatch('home-toast', message: 'این محصول موجود نیست.');

            return;
        }

        $cart = Cart::ensureForCustomer($customer);

        $item = $cart->items()->firstOrCreate(
            ['product_id' => $productId],
            ['quantity' => 1],
        );

        // Already in cart → bump quantity (never above available stock).
        if (! $item->wasRecentlyCreated) {
            $item->increment('quantity', min(1, max(0, $product->stock - $item->quantity)));
        }

        $this->dispatch('cart-changed', count: $cart->countDistinct());
        $this->dispatch('home-toast', message: '«'.$product->name.'» به سبد خرید اضافه شد.');
    }

    // ── Computed data ────────────────────────────────────────────────

    public function getShopsProperty(): Collection
    {
        return Shop::query()
            ->publiclyVisible()
            ->withCount(['publishedProducts as products_count'])
            ->latest()
            ->get();
    }

    public function getProductsProperty(): Collection
    {
        $total = $this->productQuery()->count();

        $rows = $this->productQuery()
            ->with(['mainImage', 'category', 'shop:id,name,slug'])
            ->withCount(['variants as variants_count' => fn ($q) => $q->where('status', 'active')])
            ->limit($this->perPage)
            ->get();

        $this->hasMore = $total > $this->perPage;

        return $rows;
    }

    public function getFavoriteIdsProperty(): array
    {
        return $this->customer()?->favoriteProductIds() ?? [];
    }

    public function render()
    {
        return view('livewire.customer.home', [
            'shops'       => $this->shops,
            'products'    => $this->products,
            'favoriteIds' => $this->favoriteIds,
        ])->title('فروشگاه InstaShop');
    }

    // ── Helpers ──────────────────────────────────────────────────────

    private function customer(): ?Customer
    {
        return auth('customer')->user();
    }

    /** Products that belong to a publicly visible shop. */
    private function publicProductQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return Product::query()
            ->where('status', 'published')
            ->whereHas('shop', fn ($q) => $q->publiclyVisible())
            ->withCount(['variants as variants_count' => fn ($q) => $q->where('status', 'active')]);
    }

    /** Feed query with search + sort applied. */
    private function productQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = $this->publicProductQuery();

        if ($this->search !== '') {
            $query->where('name', 'like', '%'.$this->search.'%');
        }

        match ($this->sort) {
            'popular'   => $query->orderByDesc('views'),
            'cheap'     => $query->orderBy('price'),
            'expensive' => $query->orderByDesc('price'),
            default     => $query->latest(),
        };

        return $query;
    }
}
