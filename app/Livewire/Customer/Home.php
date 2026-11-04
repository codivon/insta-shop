<?php

namespace App\Livewire\Customer;

use App\Models\Banner;
use App\Models\Cart;
use App\Models\Customer;
use App\Models\Favorite;
use App\Models\Product;
use App\Models\Shop;
use App\Models\Story;
use App\Models\StoryView;
use Illuminate\Database\Eloquent\Builder;
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
        $this->perPage += 6;
    }

    /** Story ring tap → tell the StoryViewer component to open this shop. */
    public function openStories(int $shopId): void
    {
        $this->dispatch('open-stories', shopId: $shopId);
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
            'product_id' => $productId,
        ]);
    }

    /** Quick add-to-cart straight from the product card (guests included). */
    public function addToCart(int $productId): void
    {
        $product = $this->publicProductQuery()->whereKey($productId)->first();

        if (! $product) {
            return;
        }

        // Variant products need an explicit combination choice → product page.
        if ($product->variants_count > 0) {
            $this->redirect($product->publicUrl(), navigate: true);

            return;
        }

        if ($product->type !== Product::TYPE_DOWNLOADABLE && $product->stock < 1) {
            $this->dispatch('home-toast', message: 'این محصول موجود نیست.');

            return;
        }

        // Guests fill their own cart too — the login wall stands at checkout.
        $cart = Cart::ensureForVisitor($this->customer());

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

    /**
     * Instagram-style story rings: every shop that has at least one
     * visible story becomes ONE ring holding all of its running stories
     * (oldest → newest inside the viewer). The ring is “fresh” (gradient)
     * until the logged-in customer has seen every story in it.
     */
    public function getStoryShopsProperty(): Collection
    {
        $stories = Story::query()
            ->visible()
            ->ofPublicShops()
            ->with(['shop:id,name,logo_path,instagram_username', 'product.mainImage'])
            ->orderBy('id')
            ->get();

        if ($stories->isEmpty()) {
            return collect();
        }

        $customer = $this->customer();

        $seenIds = $customer
            ? StoryView::query()
                ->where('customer_id', $customer->id)
                ->whereIn('story_id', $stories->pluck('id'))
                ->pluck('story_id')
                ->all()
            : [];

        return $stories
            ->groupBy('shop_id')
            ->values()
            ->map(fn ($group) => [
                'shop' => $group->first()->shop,
                'stories' => $group->values(),
                'count' => $group->count(),
                'hasUnseen' => $group->contains(fn (Story $s) => ! in_array($s->id, $seenIds, true)),
            ]);
    }

    /** Admin-managed hero slides (falls back to the static promo card). */
    public function getBannersProperty(): Collection
    {
        return Banner::query()->homeHero()->get();
    }

    /** Admin-managed banner cards row under the hero slider. */
    public function getHomeBannersProperty(): Collection
    {
        return Banner::query()->homeBanners()->get();
    }

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
            ->with(['mainImage', 'category', 'shop.seller:id,instagram_username'])
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
            'shops' => $this->shops,
            'products' => $this->products,
            'favoriteIds' => $this->favoriteIds,
            'storyShops' => $this->storyShops,
            'banners' => $this->banners,
            'homeBanners' => $this->homeBanners,
        ])->title('فروشگاه InstaShop');
    }

    // ── Helpers ──────────────────────────────────────────────────────

    private function customer(): ?Customer
    {
        return auth('customer')->user();
    }

    /** Products that belong to a publicly visible shop. */
    private function publicProductQuery(): Builder
    {
        return Product::query()
            ->where('status', 'published')
            ->whereHas('shop', fn ($q) => $q->publiclyVisible())
            ->withCount(['variants as variants_count' => fn ($q) => $q->where('status', 'active')]);
    }

    /** Feed query with search + sort applied. */
    private function productQuery(): Builder
    {
        $query = $this->publicProductQuery();

        if ($this->search !== '') {
            $query->where('name', 'like', '%'.$this->search.'%');
        }

        match ($this->sort) {
            'popular' => $query->orderByDesc('views'),
            'cheap' => $query->orderBy('price'),
            'expensive' => $query->orderByDesc('price'),
            default => $query->latest(),
        };

        return $query;
    }
}
