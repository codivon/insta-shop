<?php

namespace App\Livewire\Seller;

use App\Models\BumpPlan;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductBump;
use App\Models\Shop;
use App\Models\Wallet;
use App\Support\ActivityLogger;
use App\Support\Bump\BumpService;
use App\Support\Fmt;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
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

    /* ── bump purchase confirm modal (v0.25.0 — از صفحهٔ نردبان به کارت محصول منتقل شد) ── */
    public bool $buyOpen = false;

    public ?int $buyProductId = null;

    public ?int $buyPlanId = null;

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
        $this->perPage += 8;
    }

    /* ───────────── product bump purchase (v0.25.0) ───────────── */

    /** Product card «نردبان» button → the plan-choice modal. */
    public function openBuy(int $productId): void
    {
        if (! BumpService::enabled()) {
            return;
        }

        $product = $this->findOwnedProduct($productId);

        if ($product->status !== Product::STATUS_PUBLISHED) {
            return; // only published products can ride the bump
        }

        $this->resetErrorBag();
        $this->buyProductId = $productId;
        $this->buyPlanId = BumpService::plans()->first()?->id;
        $this->buyOpen = true;
    }

    public function closeBuy(): void
    {
        $this->buyOpen = false;
        $this->buyProductId = null;
        $this->buyPlanId = null;
        $this->resetErrorBag();
    }

    public function setBuyPlan(int $planId): void
    {
        $this->buyPlanId = BumpService::plans()->contains('id', $planId) ? $planId : $this->buyPlanId;
    }

    /** Confirm modal → debit the wallet and start/extend the product bump. */
    public function confirmBuy(): void
    {
        $seller = auth('seller')->user();

        $product = $this->buyProductId !== null
            ? Product::query()->where('shop_id', $this->shop()->id)->find($this->buyProductId)
            : null;

        $plan = BumpPlan::query()->where('is_active', true)->find($this->buyPlanId);

        if ($seller === null || $product === null || $plan === null) {
            $this->closeBuy();

            return;
        }

        [$ok, $row, $error] = BumpService::purchaseFromWallet($seller, $product, $plan);

        if (! $ok) {
            $this->addError('buy', $error ?? 'خرید نردبان انجام نشد.');

            return;
        }

        ActivityLogger::log(
            'bump.purchased',
            'فروشنده @'.($seller->instagram_username ?: $seller->phone).' محصول «'.$product->name.'» را با طرح «'.$plan->title.'» ('.$plan->daysLabel().') نردبان کرد — '.Fmt::money($row->price_paid),
            subject: $row,
            after: ['product_id' => $product->id, 'bump_plan_id' => $plan->id, 'ends_at' => $row->ends_at->toDateTimeString()],
        );

        $this->buyOpen = false;
        $this->buyProductId = null;
        $this->buyPlanId = null;

        session()->flash('shop-flash', 'نردبان «'.$product->name.'» فعال شد 🚀 مبلغ از کیف پول شما کسر شد.');
    }

    /** The plan chosen in the confirm modal (for the price preview). */
    public function getBuyPlanProperty(): ?BumpPlan
    {
        return $this->buyPlanId !== null ? BumpPlan::query()->find($this->buyPlanId) : null;
    }

    /** The product currently in the confirm modal. */
    public function getBuyProductProperty(): ?Product
    {
        return $this->buyProductId !== null
            ? Product::query()->with('mainImage')->where('shop_id', $this->shop()->id)->find($this->buyProductId)
            : null;
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
        $this->search = '';
        $this->categoryFilter = 0;
        $this->statusFilter = 'all';
        $this->stockFilter = 'all';
        $this->specialFilter = 'all';
        $this->perPage = 8;
        $this->hasMore = true;
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

        $shop = $this->shop();
        $handle = $shop->instagram_username ?: $seller->instagram_username;

        return $handle ? route('shop.storefront', ['instagram' => $handle]) : null;
    }

    public function getStatsProperty(): array
    {
        $base = Product::query()->where('shop_id', $this->shop()->id);

        return [
            'total' => (clone $base)->count(),
            'published' => (clone $base)->where('status', 'published')->count(),
            'outOfStock' => (clone $base)->where('stock', 0)->count(),
            'special' => (clone $base)->specialActive()->count(),
        ];
    }

    /** Global categories present among this shop's published products (filter chips). */
    public function getCategoriesProperty(): Collection
    {
        return $this->shop()
            ->usedCategories()
            ->get(['categories.id', 'categories.name'])
            ->map(fn ($cat) => (object) [
                'id' => (int) $cat->id,
                'name' => $cat->name,
                'products_count' => (int) $cat->shop_products_count,
            ]);
    }

    public function getProductsProperty(): Collection
    {
        $total = $this->filteredQuery()->count();

        $rows = $this->filteredQuery()
            ->with(['mainImage', 'category'])
            ->latest()
            ->limit(min(max($this->perPage, 1), 60))
            ->get();

        $this->hasMore = $total > min(max($this->perPage, 1), 60);

        return $rows;
    }

    public function render()
    {
        $seller = auth('seller')->user();

        return view('livewire.seller.shop-index', [
            'shop' => $this->shop,
            'stats' => $this->stats,
            'storefrontUrl' => $this->storefrontUrl,
            'categories' => $this->categories,
            'products' => $this->products,
            'bumpEnabled' => BumpService::enabled(),
            'bumpPlans' => BumpService::plans(),
            'bumpedProductIds' => $seller ? ProductBump::activeProductIds((int) $seller->id) : [],
            'walletBalance' => (int) ($seller ? Wallet::ensureForSeller($seller->id)->balance : 0),
            'buyProduct' => $this->buyProduct,
            'buyPlan' => $this->buyPlan,
        ])->title('فروشگاه من');
    }

    // ── Helpers ──────────────────────────────────────────────────────

    private function findOwnedProduct(int $productId): Product
    {
        $product = Product::query()->findOrFail($productId);

        abort_unless($product->shop_id === $this->shop()->id, 404);

        return $product;
    }

    private function filteredQuery(): Builder
    {
        $query = Product::query()->where('shop_id', $this->shop()->id);

        if ($this->search !== '') {
            $query->where('name', 'like', '%'.$this->search.'%');
        }

        if ($this->categoryFilter !== 0) {
            $query->whereIn('category_id', Category::subtreeIds($this->categoryFilter));
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
