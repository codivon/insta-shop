<?php

namespace App\Livewire\Storefront;

use App\Models\Cart;
use App\Models\Favorite;
use App\Models\Product;
use App\Models\Shop;
use App\Models\ShopCategory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
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
        $this->perPage   += 12;
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
            $this->redirect(route('shop.product.show', $productId), navigate: true);

            return;
        }

        if ($product->type !== \App\Models\Product::TYPE_DOWNLOADABLE && $product->stock < 1) {
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
            'product_id'  => $productId,
        ]);
    }

    /* ─────────────────────── computed data ─────────────────────── */

    /** All published products of this shop (base for everything). */
    private function baseQuery(): Builder
    {
        return Product::query()
            ->where('shop_id', $this->shop->id)
            ->where('status', 'published')
            ->withCount(['variants as variants_count' => fn (Builder $q) => $q->where('status', 'active')]);
    }

    /** Categories ticked «نمایش در صفحه فروشگاه» that have published products. */
    public function getCategoriesProperty(): Collection
    {
        return ShopCategory::query()
            ->where('shop_id', $this->shop->id)
            ->showOnStorefront()
            ->whereHas('products', fn (Builder $q) => $q->where('status', 'published'))
            ->withCount(['products' => fn (Builder $q) => $q->where('status', 'published')])
            ->orderBy('sort')
            ->orderBy('id')
            ->get();
    }

    public function getStatsProperty(): array
    {
        return [
            'products'   => $this->baseQuery()->count(),
            'views'      => (int) $this->baseQuery()->sum('views'),
            'categories' => $this->categories->count(),
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

    /** Slider: discounted products, biggest discount first. */
    public function getDiscountedProperty(): Collection
    {
        return $this->baseQuery()
            ->with(['mainImage', 'category'])
            ->whereColumn('compare_at_price', '>', 'price')
            ->where('price', '>', 0)
            ->orderByRaw('(price - compare_at_price) asc')
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

    /** Filtered + sorted main grid. */
    public function getGridProperty(): Collection
    {
        $query = $this->baseQuery()
            ->with(['mainImage', 'category'])
            ->when($this->catId > 0, fn (Builder $q) => $q->where('shop_category_id', $this->catId))
            ->when($this->q !== '', fn (Builder $q) => $q->where('name', 'like', '%'.$this->q.'%'));

        $query = match ($this->sort) {
            'cheap'     => $query->orderBy('price')->latest(),
            'expensive' => $query->orderByDesc('price')->latest(),
            'popular'   => $query->orderByDesc('views')->latest(),
            'discount'  => $query->whereColumn('compare_at_price', '>', 'price')->orderByRaw('(price - compare_at_price) asc')->latest(),
            default     => $query->latest(), // newest
        };

        $total = $query->count();

        $rows = $query->limit($this->perPage)->get();

        $this->hasMore = $total > $this->perPage;

        return $rows;
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
            'stats'             => $this->stats,
            'categories'        => $this->categories,
            'newest'            => $this->newest,
            'discounted'        => $this->discounted,
            'popular'           => $this->popular,
            'special'           => $this->special,
            'specialDeadlineMs' => $this->specialDeadlineMs,
            'grid'              => $this->grid,
            'favoriteIds'       => $this->favoriteIds,
            'storefrontUrl'     => $this->storefrontUrl,
            'cartCount'         => $this->cartCount,
        ])->title($this->shop->name);
    }
}
