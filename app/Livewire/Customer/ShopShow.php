<?php

namespace App\Livewire\Customer;

use App\Models\Cart;
use App\Models\Favorite;
use App\Models\Product;
use App\Models\Shop;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;

#[Layout('layouts.customer-slot')]
class ShopShow extends Component
{
    public Shop $shop;

    public string $search = '';

    /** Lazy load (infinite scroll) */
    public int $perPage = 8;

    public bool $hasMore = true;

    private int $lastLoadAt = 0;

    public function mount(string $slug): void
    {
        $shop = Shop::query()
            ->publiclyVisible()
            ->where('slug', $slug)
            ->first();

        abort_if(! $shop, 404);

        $this->shop = $shop;
    }

    public function updatingSearch(): void
    {
        $this->perPage = 8;
        $this->hasMore = true;
    }

    #[On('store-load-more')]
    public function loadMore(): void
    {
        $now = (int) (microtime(true) * 1000);

        if ($now - $this->lastLoadAt < 500) {
            return;
        }

        $this->lastLoadAt = $now;
        $this->perPage   += 8;
    }

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
            'product_id'  => $productId,
        ]);
    }

    public function addToCart(int $productId): void
    {
        $customer = auth('customer')->user();
        $product  = $this->productQuery()->whereKey($productId)->first();

        if (! $customer || ! $product) {
            return;
        }

        if ($product->stock < 1) {
            $this->dispatch('store-toast', message: 'این محصول موجود نیست.');

            return;
        }

        $cart = Cart::ensureForCustomer($customer);

        $item = $cart->items()->firstOrCreate(
            ['product_id' => $productId],
            ['quantity' => 1],
        );

        if (! $item->wasRecentlyCreated) {
            $item->increment('quantity', min(1, max(0, $product->stock - $item->quantity)));
        }

        $this->dispatch('cart-changed', count: $cart->countDistinct());
        $this->dispatch('store-toast', message: '«'.$product->name.'» به سبد خرید اضافه شد.');
    }

    // ── Computed data ────────────────────────────────────────────────

    public function getStatsProperty(): array
    {
        return [
            'products' => $this->publishedQuery()->count(),
            'views'    => (int) $this->publishedQuery()->sum('views'),
        ];
    }

    public function getProductsProperty(): Collection
    {
        $total = $this->productQuery()->count();

        $rows = $this->productQuery()
            ->with(['mainImage', 'category'])
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
        return view('livewire.customer.shop-show', [
            'stats'       => $this->stats,
            'products'    => $this->products,
            'favoriteIds' => $this->favoriteIds,
        ])->title($this->shop->name);
    }

    // ── Helpers ──────────────────────────────────────────────────────

    private function publishedQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return \App\Models\Product::query()
            ->where('shop_id', $this->shop->id)
            ->where('status', 'published');
    }

    private function productQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = $this->publishedQuery();

        if ($this->search !== '') {
            $query->where('name', 'like', '%'.$this->search.'%');
        }

        return $query->latest();
    }
}
