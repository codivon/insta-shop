<?php

namespace App\Livewire\Storefront;

use App\Models\Cart;
use App\Models\Category;
use App\Models\Favorite;
use App\Models\Product;
use App\Models\ProductVariantValue;
use App\Models\Shop;
use App\Models\ShopAttribute;
use App\Models\ShopFilter;
use App\Models\ShopFilterItem;
use App\Models\Story;
use App\Models\StoryView;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Public seller storefront — the shop's own front page (no login),
 * reachable at «دامنه/shop/{آیدی اینستا}» and directly at
 * «دامنه/{آیدی اینستا}» via the web.php fallback route.
 *
 * Beautiful modern front: hero (banner/logo/stats), ticked-category
 * chips, product sliders (جدیدترین / تخفیف‌دار / پربازدید) and a
 * filterable product grid (category + search + sort).
 */
#[Layout('layouts.storefront-slot')]
class StorefrontShow extends Component
{
    public Shop $shop;

    /* ── URL-persisted filters ── */
    #[Url(as: 'cat')]
    public int $catId = 0;

    #[Url(as: 'sort')]
    public string $sort = 'newest';

    #[Url(as: 'q')]
    public string $q = '';

    /* ── فیلترها modal (batch 72) ── */
    public bool $showFilters = false;

    /** Active picks: block key => [value, …] — keys: «s:{spec title}» / «a:{attribute id}». */
    public array $specPicks = [];

    /** Search box inside the filters modal. */
    public string $specSearch = '';

    /* ── grid pagination ── */
    public int $perPage = 12;

    public bool $hasMore = false;

    private int $lastLoadAt = 0;

    public function mount(?string $instagram = null): void
    {
        // The fallback route matches every unmatched verb as well.
        if (! request()->isMethod('get')) {
            abort(404);
        }

        // /shop/{handle} passes the param; the bare-domain fallback has
        // no params, so there the handle IS the first URL segment.
        $handle = mb_strtolower(trim($instagram ?? (string) request()->segment(1)));

        abort_if($handle === '', 404);

        // Resolve by the seller's (immutable) instagram handle first,
        // then the shop mirror. Only publicly visible shops answer.
        $shop = Shop::query()
            ->publiclyVisible()
            ->where(function (Builder $query) use ($handle) {
                $query
                    ->whereHas('seller', fn (Builder $s) => $s->whereRaw('lower(instagram_username) = ?', [$handle]))
                    ->orWhereRaw('lower(instagram_username) = ?', [$handle]);
            })
            ->first();

        abort_if(! $shop, 404);

        $this->shop = $shop;
    }

    /* ─────────────────── filter lifecycle ─────────────────── */

    public function updatingCatId(): void
    {
        $this->resetPagination();
    }

    public function updatingSort(): void
    {
        $this->resetPagination();
    }

    public function updatingQ(): void
    {
        $this->resetPagination();
    }

    public function updatingSpecPicks(): void
    {
        $this->resetPagination();
    }

    /* ───────── فیلترها modal (batch 72) ───────── */

    public function openFilters(): void
    {
        $this->showFilters = true;
    }

    public function closeFilters(): void
    {
        $this->showFilters = false;
    }

    /** Toggle one value checkbox inside a filter block (spec or attribute). */
    public function toggleSpecPick(string $key, string $value): void
    {
        $current = (array) ($this->specPicks[$key] ?? []);

        if (in_array($value, $current, true)) {
            $current = array_values(array_diff($current, [$value]));
        } else {
            $current[] = $value;
        }

        if ($current === []) {
            unset($this->specPicks[$key]);
        } else {
            $this->specPicks[$key] = $current;
        }

        $this->resetPagination();
    }

    public function clearSpecPicks(): void
    {
        $this->specPicks = [];
        $this->resetPagination();
    }

    /** Number of active (spec,value) picks — drives the badge on the button. */
    public function getSpecPicksCountProperty(): int
    {
        return array_sum(array_map('count', $this->specPicks));
    }

    private function resetPagination(): void
    {
        $this->perPage = 12;
        $this->hasMore = false;
    }

    public function loadMore(): void
    {
        $now = (int) (microtime(true) * 1000);

        if ($now - $this->lastLoadAt < 400) {
            return;
        }

        $this->lastLoadAt = $now;
        $this->perPage += 12;
    }

    /* ───────────────────── customer actions ───────────────────── */

    public function addToCart(int $productId): void
    {
        $product = $this->baseQuery()->whereKey($productId)->first();

        if (! $product) {
            return;
        }

        // Variant products need an explicit combination choice → product page.
        if ($product->variants_count > 0) {
            $this->redirect($product->publicUrl(), navigate: true);

            return;
        }

        if ($product->type !== Product::TYPE_DOWNLOADABLE && $product->stock < 1) {
            $this->dispatch('storefront-toast', message: 'این محصول موجود نیست.');

            return;
        }

        // Guests fill their own cart too — the login wall stands at checkout.
        $cart = Cart::ensureForVisitor(auth('customer')->user());

        $item = $cart->items()->firstOrCreate(
            ['product_id' => $productId],
            ['quantity' => 1],
        );

        if (! $item->wasRecentlyCreated) {
            $item->increment('quantity', min(1, max(0, $product->stock - $item->quantity)));
        }

        $this->dispatch('cart-changed', count: $cart->countDistinct());
        $this->dispatch('storefront-toast', message: '«'.$product->name.'» به سبد خرید اضافه شد.');
    }

    public function toggleFavorite(int $productId): void
    {
        $customer = auth('customer')->user();

        if (! $customer) {
            $this->redirect(route('shop.login'), navigate: true);

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

    /* ─────────────────── storefront stories (per-story rings) ─────────────────── */

    /**
     * Active stories of THIS shop, oldest → newest. On the seller's own
     * storefront EVERY story is its own story ring (client: «۵ استوری =
     * انگار ۵ اکانت استوری گذاشته») — home keeps the one-ring-per-shop
     * style, this is storefront-only.
     */
    public function getStorefrontStoriesProperty(): Collection
    {
        return Story::query()
            ->visible()
            ->ofPublicShops()
            ->where('shop_id', $this->shop->id)
            ->with(['shop:id,name,logo_path,instagram_username', 'product.mainImage'])
            ->withCount('likes')
            ->orderBy('id')
            ->get();
    }

    /** Story ids the signed-in customer already watched (ring seen-state). */
    public function getStorefrontSeenIdsProperty(): array
    {
        $customer = auth('customer')->user();

        if (! $customer || $this->storefrontStories->isEmpty()) {
            return [];
        }

        return StoryView::query()
            ->where('customer_id', $customer->id)
            ->whereIn('story_id', $this->storefrontStories->pluck('id'))
            ->pluck('story_id')
            ->all();
    }

    /** Open the story viewer at a SPECIFIC story ring (not always the first). */
    public function openStoryAt(int $index): void
    {
        if ($this->storefrontStories->isEmpty()) {
            return;
        }

        $this->dispatch('open-stories', shopId: $this->shop->id, startIndex: max(0, $index));
    }

    /**
     * A story was just watched in the nested viewer — re-render so
     * `storefrontSeenIds` (computed) re-reads story_views and THAT ring
     * loses its gradient border instantly, without a manual reload
     * (client v0.17.8).
     */
    #[On('story-marked-seen')]
    public function onStoryMarkedSeen(): void {}

    /* ─────────────────────── computed data ─────────────────────── */

    /** All published products of this shop (base for everything). */
    private function baseQuery(): Builder
    {
        return Product::query()
            ->where('shop_id', $this->shop->id)
            ->where('status', 'published')
            ->with(['shop.seller:id,instagram_username'])
            ->withCount(['variants as variants_count' => fn (Builder $q) => $q->where('status', 'active')]);
    }

    /** Global categories with published products in this shop (?cat= deep links). */
    public function getCategoriesProperty(): Collection
    {
        return $this->shop->usedCategories()
            ->get(['categories.id', 'categories.name'])
            ->map(fn ($cat) => (object) [
                'id' => (int) $cat->id,
                'name' => $cat->name,
                'products_count' => (int) $cat->shop_products_count,
            ]);
    }

    public function getStatsProperty(): array
    {
        return [
            'products' => $this->baseQuery()->count(),
            'views' => (int) $this->baseQuery()->sum('views'),
        ];
    }

    /** Slider: newest published products (shop-wide). */
    public function getNewestProperty(): Collection
    {
        return $this->baseQuery()
            ->with(['mainImage', 'category'])
            ->latest()
            ->limit(10)
            ->get();
    }

    /**
     * Slider: discounted products, biggest discount first.
     * The subtraction MUST be «compare_at_price - price» (always positive
     * under the whereColumn guard): on MySQL both columns are UNSIGNED
     * BIGINT and the reversed «price - compare_at_price» throws
     * 1690 «Numeric value out of range» for every discounted row
     * (v0.17.14 — /shop/demo_store 500).
     */
    public function getDiscountedProperty(): Collection
    {
        return $this->baseQuery()
            ->with(['mainImage', 'category'])
            ->whereColumn('compare_at_price', '>', 'price')
            ->where('price', '>', 0)
            ->orderByRaw('(compare_at_price - price) desc')
            ->latest()
            ->limit(10)
            ->get();
    }

    /** Slider: most viewed products. */
    public function getPopularProperty(): Collection
    {
        return $this->baseQuery()
            ->with(['mainImage', 'category'])
            ->where('views', '>', 0)
            ->orderByDesc('views')
            ->latest()
            ->limit(10)
            ->get();
    }

    /** فروش ویژه — products whose special-sale window is open, soonest deadline first. */
    public function getSpecialProperty(): Collection
    {
        return $this->baseQuery()
            ->with(['mainImage', 'category'])
            ->specialActive()
            ->orderByRaw('special_ends_at IS NULL, special_ends_at ASC')
            ->latest()
            ->limit(10)
            ->get();
    }

    /** Filtered + sorted main grid (with spec-pick filtering — batch 72). */
    public function getGridProperty(): Collection
    {
        $query = $this->baseQuery()
            ->with(['mainImage', 'category'])
            ->when($this->catId > 0, fn (Builder $q) => $q->whereIn('category_id', Category::subtreeIds($this->catId)))
            ->when($this->q !== '', fn (Builder $q) => $q->where('name', 'like', '%'.$this->q.'%'))
            ->when($this->specPicks !== [], fn (Builder $q) => $q->whereIn('id', $this->specMatchedIds()));

        $query = match ($this->sort) {
            'cheap' => $query->orderBy('price')->latest(),
            'expensive' => $query->orderByDesc('price')->latest(),
            'popular' => $query->orderByDesc('views')->latest(),
            // same unsigned-safe direction as getDiscountedProperty() (v0.17.14)
            'discount' => $query->whereColumn('compare_at_price', '>', 'price')->orderByRaw('(compare_at_price - price) desc')->latest(),
            default => $query->latest(), // newest
        };

        $total = $query->count();

        $rows = $query->limit($this->perPage)->get();

        $this->hasMore = $total > $this->perPage;

        return $rows;
    }

    /**
     * Ids of the shop's published products matching ALL active picks.
     * A pick is either a spec title («s:نسخه بلوتوث» → values from the
     * specifications JSON) or a shop attribute («a:12» → values from the
     * products' variant combinations). Inside ONE attribute any value
     * matches (OR); across attributes every pick must match (AND).
     * specifications JSON is matched in PHP — portable across MySQL/MariaDB.
     */
    private function specMatchedIds(): array
    {
        if ($this->specPicks === []) {
            return [];
        }

        $specPicks = [];
        $attrPicks = [];

        foreach ($this->specPicks as $key => $wanted) {
            if (str_starts_with((string) $key, 'a:')) {
                $attrPicks[(int) substr((string) $key, 2)] = array_map(fn ($v) => trim((string) $v), (array) $wanted);
            } else {
                $specPicks[(string) substr((string) $key, 2)] = array_map(fn ($v) => trim((string) $v), (array) $wanted);
            }
        }

        return $this->baseQuery()
            ->when($attrPicks !== [], fn (Builder $q) => $q->with('activeVariants.values.value'))
            ->get(['id', 'specifications'])
            ->filter(function (Product $product) use ($specPicks, $attrPicks) {
                $items = collect($product->specifications ?? [])
                    ->flatMap(fn ($group) => collect($group['items'] ?? []))
                    ->groupBy(fn ($item) => trim((string) ($item['title'] ?? '')));

                foreach ($specPicks as $spec => $wanted) {
                    $values = ($items->get((string) $spec) ?? collect())
                        ->pluck('value')
                        ->map(fn ($v) => trim((string) $v))
                        ->filter()
                        ->all();

                    // ANY wanted value of this attribute on the product (OR)
                    $hit = collect($wanted)->contains(fn ($needle) => in_array($needle, $values, true));

                    if (! $hit) {
                        return false;
                    }
                }

                foreach ($attrPicks as $attributeId => $wanted) {
                    // ANY active variant carrying ANY wanted value of this attribute
                    $hit = $product->activeVariants->contains(
                        fn ($variant) => $variant->values->contains(
                            fn ($v) => (int) $v->shop_attribute_id === (int) $attributeId
                                && in_array(trim((string) ($v->value_text ?: ($v->value->value ?? ''))), $wanted, true)
                        )
                    );

                    if (! $hit) {
                        return false;
                    }
                }

                return true;
            })
            ->pluck('id')
            ->all();
    }

    /** Nearest active special-sale deadline (ms epoch) — drives the section countdown. */
    public function getSpecialDeadlineMsProperty(): ?int
    {
        $ends = $this->special
            ->pluck('special_ends_at')
            ->filter()
            ->sort()
            ->first();

        return $ends ? $ends->timestamp * 1000 : null;
    }

    public function getFavoriteIdsProperty(): array
    {
        $customer = auth('customer')->user();

        return $customer ? $customer->favoriteProductIds() : [];
    }

    /* ───────────── فیلترهای فروشگاه (batch 72) ───────────── */

    /** Published filters of THIS shop with their spec groups. */
    public function getPublishedFiltersProperty(): Collection
    {
        return ShopFilter::query()
            ->where('shop_id', $this->shop->id)
            ->published()
            ->with('items')
            ->orderBy('sort')
            ->orderBy('id')
            ->get()
            ->filter(fn (ShopFilter $filter) => $filter->items->isNotEmpty())
            ->values();
    }

    /**
     * Distinct values of ONE attribute title (e.g. «نسخه بلوتوث») across
     * the shop's published products — the customer-facing options of a
     * filter block. Titles are matched INSIDE every specification group,
     * so all 10 phones contribute their own bluetooth version.
     */
    public function specValues(string $specName): array
    {
        $name = trim($specName);

        return $this->baseQuery()
            ->get(['specifications'])
            ->flatMap(fn ($product) => collect($product->specifications ?? []))
            ->flatMap(fn ($group) => collect($group['items'] ?? []))
            ->filter(fn ($item) => trim((string) ($item['title'] ?? '')) === $name)
            ->pluck('value')
            ->map(fn ($value) => trim((string) $value))
            ->filter(fn ($value) => $value !== '')
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    /**
     * Distinct values of ONE shop attribute (ویژگی) as used on the shop's
     * published products' active variants — the customer-facing options of
     * an attribute filter block, plus the color hex per value when the
     * attribute is رنگی.
     */
    public function attributeFilterData(int $attributeId): array
    {
        $attribute = ShopAttribute::query()
            ->where('shop_id', $this->shop->id)
            ->find($attributeId);

        if (! $attribute) {
            return ['values' => [], 'colors' => []];
        }

        $rows = ProductVariantValue::query()
            ->where('shop_attribute_id', $attributeId)
            ->whereHas('variant', fn ($q) => $q
                ->where('status', 'active')
                ->whereHas('product', fn ($p) => $p
                    ->where('shop_id', $this->shop->id)
                    ->where('status', 'published')))
            ->with('value:id,shop_attribute_id,value,color_hex')
            ->get(['shop_attribute_value_id', 'value_text']);

        $values = $rows
            ->toBase() // Eloquent::unique() expects models — dedupe plain strings on the base collection
            ->map(fn ($row) => trim((string) ($row->value_text ?: ($row->value->value ?? ''))))
            ->filter()
            ->unique()
            ->sort()
            ->values();

        $colors = [];

        if ($attribute->type === 'color') {
            foreach ($rows as $row) {
                $preset = $row->value;

                if ($row->value_text || ! $preset || ! $preset->color_hex) {
                    continue;
                }

                $label = trim((string) $preset->value);

                if ($label !== '') {
                    $colors[$label] = $preset->color_hex;
                }
            }
        }

        return ['values' => $values->all(), 'colors' => $colors];
    }

    /** Filters × their values, prepared for the modal (with active-pick counts). */
    public function getFilterBlocksProperty(): Collection
    {
        return $this->publishedFilters->map(function (ShopFilter $filter) {
            $blocks = $filter->items
                ->map(function (ShopFilterItem $item) {
                    if ($item->isAttribute()) {
                        $data = $this->attributeFilterData((int) $item->shop_attribute_id);

                        if ($data['values'] === []) {
                            return null;
                        }

                        return [
                            'key' => 'a:'.$item->shop_attribute_id,
                            'label' => $item->spec_name,
                            'values' => $data['values'],
                            'colors' => $data['colors'],
                        ];
                    }

                    $values = $this->specValues($item->spec_name);

                    if ($values === []) {
                        return null;
                    }

                    return [
                        'key' => 's:'.$item->spec_name,
                        'label' => $item->spec_name,
                        'values' => $values,
                        'colors' => [],
                    ];
                })
                ->filter()
                ->values();

            return [
                'filter' => $filter,
                'blocks' => $blocks,
            ];
        })->filter(fn (array $row) => $row['blocks']->isNotEmpty())->values();
    }

    /** Distinct cart lines of the current visitor — drives the header badge. */
    public function getCartCountProperty(): int
    {
        return Cart::forVisitor(auth('customer')->user())?->countDistinct() ?? 0;
    }

    /** Public shareable address of this storefront. */
    public function getStorefrontUrlProperty(): string
    {
        $handle = $this->shop->seller?->instagram_username ?: $this->shop->instagram_username;

        return $handle ? route('shop.storefront', ['instagram' => $handle]) : url('/shop');
    }

    public function render()
    {
        return view('livewire.storefront.storefront-show', [
            'stats' => $this->stats,
            'categories' => $this->categories,
            'newest' => $this->newest,
            'discounted' => $this->discounted,
            'popular' => $this->popular,
            'special' => $this->special,
            'specialDeadlineMs' => $this->specialDeadlineMs,
            'grid' => $this->grid,
            'favoriteIds' => $this->favoriteIds,
            'storefrontUrl' => $this->storefrontUrl,
            'cartCount' => $this->cartCount,
            'filterBlocks' => $this->filterBlocks,
            'specPicksCount' => $this->specPicksCount,
            'storefrontStories' => $this->storefrontStories,
            'storefrontSeenIds' => $this->storefrontSeenIds,
        ])->title($this->shop->name);
    }
}
