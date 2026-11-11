<?php

namespace App\Livewire\Customer;

use App\Models\Banner;
use App\Models\Cart;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Favorite;
use App\Models\Product;
use App\Models\ProductBump;
use App\Models\SellerSubscription;
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

    /**
     * A story was just watched in the nested viewer — re-render so
     * `storyShops` (computed) re-reads story_views; when the LAST unseen
     * story of a shop is watched, its home ring goes grey instantly
     * without a manual reload (client v0.17.8).
     */
    #[On('story-marked-seen')]
    public function onStoryMarkedSeen(): void {}

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

    /* ───────── v0.23.0 — نردبان + اولویت نمایش (boost) ───────── */

    /**
     * «نردبان‌ها» — products whose seller bought a RUNNING bump window:
     * they render in the dedicated strip at the very top of the home page
     * (Divar-style). Expired bumps drop out by date, no cron needed.
     */
    public function getBumpProductsProperty(): Collection
    {
        return ProductBump::homeStrip(10);
    }

    /**
     * shop_id => boost tier of the shop's seller ACTIVE plan (0/1/2).
     * Plans with boost_level > 0 sell «اولویت نمایش در صفحهٔ اصلی» — the
     * strongest tier wins when a seller somehow holds several rows.
     * Runs once per render; the result is tiny (only boosted sellers).
     */
    private function boostTiersByShopId(): array
    {
        $sellerTiers = SellerSubscription::query()
            ->where('ends_at', '>', now())
            ->whereHas('plan', fn ($q) => $q->where('boost_level', '>', 0))
            ->with('plan:id,boost_level')
            ->get(['seller_id', 'plan_id'])
            ->groupBy('seller_id')
            ->map(fn ($rows) => (int) $rows->max(fn ($row) => (int) ($row->plan?->boost_level ?? 0)));

        if ($sellerTiers->isEmpty()) {
            return [];
        }

        return Shop::query()
            ->whereIn('seller_id', $sellerTiers->keys())
            ->get(['id', 'seller_id'])
            ->mapWithKeys(fn (Shop $shop) => [$shop->id => $sellerTiers[$shop->seller_id] ?? 0])
            ->all();
    }

    /** Daily rotation seed — stable for a whole day (pagination-safe), rotates every day so heavy tiers share the front. */
    private function rotationSeed(): int
    {
        return (int) now()->format('z'); // day of year (0..365)
    }

    /** Sort shops: boost tier desc → daily rotation → newest (PHP-side, portable, no pagination involved). */
    private function sortShopsByBoost(Collection $shops): Collection
    {
        $tiers = $this->boostTiersByShopId();

        if ($tiers === []) {
            return $shops;
        }

        $seed = $this->rotationSeed();

        return $shops->sort(function (Shop $a, Shop $b) use ($tiers, $seed) {
            $tierA = $tiers[$a->id] ?? 0;
            $tierB = $tiers[$b->id] ?? 0;

            if ($tierA !== $tierB) {
                return $tierB <=> $tierA;
            }

            // Fair rotation INSIDE a tier: many boosted shops share the
            // front — every day each shop drifts one bucket forward.
            $bucketA = ($a->id + $seed) % 5;
            $bucketB = ($b->id + $seed) % 5;

            if ($bucketA !== $bucketB) {
                return $bucketA <=> $bucketB;
            }

            return $b->id <=> $a->id;
        })->values();
    }

    /** Basalam-style «خرید بر اساس دسته‌بندی» grid — first 7 root categories. */
    public function getTopCategoriesProperty(): Collection
    {
        return Category::query()
            ->whereNull('parent_id')
            ->orderBy('sort')
            ->orderBy('id')
            ->limit(7)
            ->get(['id', 'name', 'image']);
    }

    /** «پیشنهاد شگفت‌انگیز» — published products with a real compare-at discount. */
    public function getFlashProductsProperty(): Collection
    {
        return $this->publicProductQuery()
            ->where('compare_at_price', '>', 0)
            ->whereColumn('compare_at_price', '>', 'price')
            ->with('mainImage')
            ->latest('updated_at')
            ->limit(10)
            ->get();
    }

    /** Basalam-style «پیشنهادهای امروز بازار» — active shops + 3 products each. */
    public function getSpotlightShopsProperty(): Collection
    {
        $shops = Shop::query()
            ->publiclyVisible()
            ->whereHas('publishedProducts')
            ->withCount(['publishedProducts as products_count'])
            ->latest()
            ->limit(20) // wide candidate pool — the boost ordering narrows it below
            ->get();

        $shops = $this->sortShopsByBoost($shops)->take(8);

        return $shops->map(function (Shop $shop) {
            $shop->spotlight_products = $shop->publishedProducts()
                ->with('mainImage')
                ->latest()
                ->limit(3)
                ->get(['id', 'shop_id', 'name', 'price', 'compare_at_price', 'type', 'stock']);

            return $shop;
        })->filter(fn (Shop $shop) => $shop->spotlight_products->isNotEmpty())->values();
    }

    public function getShopsProperty(): Collection
    {
        $shops = Shop::query()
            ->publiclyVisible()
            ->withCount(['publishedProducts as products_count'])
            ->latest()
            ->get();

        return $this->sortShopsByBoost($shops);
    }

    public function getProductsProperty(): Collection
    {
        $total = $this->productQuery()->count();

        $rows = $this->productQuery()
            ->with(['mainImage', 'category', 'shop.seller:id,instagram_username'])
            ->withCount(['variants as variants_count' => fn ($q) => $q->where('status', 'active')])
            ->limit(min(max($this->perPage, 1), 60))
            ->get();

        $this->hasMore = $total > min(max($this->perPage, 1), 60);

        return $rows;
    }

    public function getFavoriteIdsProperty(): array
    {
        return $this->customer()?->favoriteProductIds() ?? [];
    }

    /**
     * client v0.17.15: «در نتیجه جستجو، فروشگاه هم اضافه بشه» — while
     * searching, publicly visible shops whose NAME, instagram @handle or
     * seller full-name match the term appear as a scroller above products.
     */
    public function getSearchShopsProperty(): Collection
    {
        $term = trim($this->search);

        if ($term === '') {
            return collect();
        }

        return Shop::query()
            ->publiclyVisible()
            ->where(fn (Builder $q) => $q
                ->where('name', 'like', '%'.$term.'%')
                ->orWhere('instagram_username', 'like', '%'.$term.'%')
                ->orWhereHas('seller', fn (Builder $s) => $s
                    ->where('full_name', 'like', '%'.$term.'%')
                    ->orWhere('instagram_username', 'like', '%'.$term.'%')))
            ->withCount(['publishedProducts as products_count'])
            ->orderBy('name')
            ->limit(10)
            ->get();
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
            'topCategories' => $this->topCategories,
            'flashProducts' => $this->flashProducts,
            'spotlightShops' => $this->spotlightShops,
            'searchShops' => $this->searchShops,
            'bumpProducts' => $this->bumpProducts,
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
            $term = $this->search;

            // client v0.17.15: «اسم فروشندگان یا آیدی فروشندگان یا فروشگاه‌ها را
            // هم در جستجو داشته باشیم» — a shop-name / seller-name / @handle
            // hit surfaces that shop's products alongside name matches.
            $query->where(fn (Builder $q) => $q
                ->where('name', 'like', '%'.$term.'%')
                ->orWhereHas('shop', fn (Builder $s) => $s
                    ->where('name', 'like', '%'.$term.'%')
                    ->orWhere('instagram_username', 'like', '%'.$term.'%')
                    ->orWhereHas('seller', fn (Builder $se) => $se
                        ->where('full_name', 'like', '%'.$term.'%')
                        ->orWhere('instagram_username', 'like', '%'.$term.'%'))));
        }

        match ($this->sort) {
            'popular' => $query->orderByDesc('views'),
            'cheap' => $query->orderBy('price'),
            'expensive' => $query->orderByDesc('price'),
            // v0.23.0 — default feed: boosted shops' products FIRST
            // (plan benefit «اولویت نمایش در صفحهٔ اصلی»), then a daily
            // fair rotation inside every tier, then newest. The expression
            // is deterministic per day → infinite-scroll pagination stays
            // consistent. Explicit customer sorts (price/views) win over
            // the paid boost — customer intent beats merchandising.
            default => $this->applyBoostOrdering($query),
        };

        return $query;
    }

    /** SQL ordering: boost tier desc → daily rotation bucket → newest (portable CASE, SQLite + MySQL). */
    private function applyBoostOrdering(Builder $query): Builder
    {
        $tiers = $this->boostTiersByShopId();

        if ($tiers === []) {
            return $query->latest();
        }

        $tier2 = array_keys(array_filter($tiers, fn ($t) => $t === 2));
        $tier1 = array_keys(array_filter($tiers, fn ($t) => $t === 1));
        $seed = $this->rotationSeed();

        $cases = '';

        if ($tier2 !== []) {
            $cases .= 'case when shop_id in ('.implode(',', array_fill(0, count($tier2), '?')).') then 2 ';
        } else {
            $cases .= 'case when 1 = 0 then 2 ';
        }

        if ($tier1 !== []) {
            $cases .= 'when shop_id in ('.implode(',', array_fill(0, count($tier1), '?')).') then 1 ';
        }

        $cases .= 'else 0 end';

        // ids are server-generated integers — safe to inline in the rotation
        $bindings = [...$tier2, ...$tier1, $seed];

        return $query
            ->orderByRaw($cases.' desc, ((shop_id * 7 + ?) % 5) asc, id desc', $bindings);
    }
}
