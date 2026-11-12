<?php

namespace App\Livewire\Seller;

use App\Models\Category;
use App\Models\InstallmentExclusion;
use App\Models\Product;
use App\Models\SellerBnplSetting;
use App\Models\Shop;
use App\Support\ActivityLogger;
use App\Support\Fmt;
use App\Support\Payments\PaymentService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.seller-slot')]
class InstallmentProducts extends Component
{
    /** جستجوی زندهٔ نام محصول (لاتین/فارسی هر دو با like ساده). */
    public string $search = '';

    /** all | allowed | excluded — وضعیت اقساطی بودن. */
    public string $filter = 'all';

    public int $categoryFilter = 0;

    /** new | old | expensive | cheap. */
    public string $sort = 'new';

    public int $perPage = 12;

    public bool $hasMore = true;

    public function updatedSearch(): void
    {
        $this->resetPaging();
    }

    public function updatedFilter(): void
    {
        $this->resetPaging();
    }

    public function updatedCategoryFilter(): void
    {
        $this->resetPaging();
    }

    public function updatedSort(): void
    {
        $this->resetPaging();
    }

    private function resetPaging(): void
    {
        $this->perPage = 12;
        $this->hasMore = true;
    }

    public function loadMore(): void
    {
        if (! $this->hasMore) {
            return;
        }

        $this->perPage += 12;
    }

    /**
     * v0.32.0 — «محصولات مستثنی اقساطی»: a product the seller does NOT
     * want to sell through the installment gateways. Toggling is one tap
     * and the effect is immediate — the product-page badge disappears and
     * checkout names the product in the eligibility modal.
     */
    public function toggleExclusion(int $productId): void
    {
        $product = Product::query()->find($productId);

        if (! $product || $product->shop_id !== $this->shop()->id) {
            return;
        }

        $sellerId = (int) auth('seller')->id();
        $existing = InstallmentExclusion::query()
            ->where('seller_id', $sellerId)
            ->where('product_id', $productId)
            ->first();

        if ($existing) {
            $existing->delete();

            ActivityLogger::log(
                'installment.restored',
                'محصول «'.$product->name.'» دوباره به خرید اقساطی برگشت',
                subject: $product,
            );

            $this->dispatch('panel-toast', message: '«'.$product->name.'» دوباره قابل خرید اقساطی شد. ✅', type: 'success');
        } else {
            InstallmentExclusion::query()->create([
                'seller_id' => $sellerId,
                'product_id' => $productId,
            ]);

            ActivityLogger::log(
                'installment.excluded',
                'محصول «'.$product->name.'» از خرید اقساطی مستثنی شد',
                subject: $product,
            );

            $this->dispatch('panel-toast', message: '«'.$product->name.'» از خرید اقساطی مستثنی شد.', type: 'success');
        }
    }

    /**
     * استثنا کردن همهٔ محصولاتِ نتایج فعلی (جستجو/فیلتر جاری) — one shot.
     */
    public function excludeFiltered(): void
    {
        $ids = $this->filteredQuery()->pluck('products.id')->all();
        $sellerId = (int) auth('seller')->id();

        $already = InstallmentExclusion::idsForSeller($sellerId);
        $fresh = array_values(array_diff($ids, $already));

        foreach ($fresh as $productId) {
            InstallmentExclusion::query()->create([
                'seller_id' => $sellerId,
                'product_id' => (int) $productId,
            ]);
        }

        if ($fresh !== []) {
            ActivityLogger::log(
                'installment.excluded',
                Fmt::toFa(count($fresh)).' محصول از خرید اقساطی مستثنی شدند (گروهی)',
            );

            $this->dispatch('panel-toast', message: Fmt::toFa(count($fresh)).' محصول از خرید اقساطی مستثنی شد. ✅', type: 'success');
        } else {
            $this->dispatch('panel-toast', message: 'همهٔ محصولات نتایج فعلی قبلاً مستثنی شده‌اند.');
        }
    }

    /**
     * برگرداندن همهٔ محصولاتِ نتایج فعلی به خرید اقساطی — one shot.
     */
    public function restoreFiltered(): void
    {
        $ids = $this->filteredQuery()->pluck('products.id')->all();
        $sellerId = (int) auth('seller')->id();

        $removed = InstallmentExclusion::query()
            ->where('seller_id', $sellerId)
            ->whereIn('product_id', $ids)
            ->pluck('id')
            ->count();

        InstallmentExclusion::query()
            ->where('seller_id', $sellerId)
            ->whereIn('product_id', $ids)
            ->delete();

        if ($removed > 0) {
            ActivityLogger::log(
                'installment.restored',
                Fmt::toFa($removed).' محصول به خرید اقساطی برگشت (گروهی)',
            );

            $this->dispatch('panel-toast', message: Fmt::toFa($removed).' محصول دوباره قابل خرید اقساطی شد. ✅', type: 'success');
        } else {
            $this->dispatch('panel-toast', message: 'در نتایج فعلی محصول مستثنی‌شده‌ای نیست.');
        }
    }

    protected function shop(): Shop
    {
        return once(fn () => Shop::ensureForSeller(auth('seller')->user()));
    }

    public function getShopProperty(): Shop
    {
        return $this->shop();
    }

    /**
     * The page only means anything when the seller actually enabled at
     * least ONE installment gateway («وقتی روش پرداخت اقساطی از سمت
     * فروشنده فعال هست») — otherwise a guidance state renders.
     */
    public function getHasBnplEnabledProperty(): bool
    {
        return SellerBnplSetting::query()
            ->where('seller_id', (int) auth('seller')->id())
            ->where('enabled', true)
            ->whereIn('gateway', array_keys(PaymentService::availableBnplGateways()))
            ->exists();
    }

    /** The installment gateways THIS seller enabled (header chips). */
    public function getEnabledGatewaysProperty(): array
    {
        $rows = SellerBnplSetting::query()
            ->where('seller_id', (int) auth('seller')->id())
            ->where('enabled', true)
            ->pluck('gateway')
            ->all();

        $labels = [];

        foreach ($rows as $gateway) {
            if (isset(PaymentService::BNPL_GATEWAYS[$gateway])) {
                $labels[$gateway] = PaymentService::BNPL_GATEWAYS[$gateway];
            }
        }

        return $labels;
    }

    public function getStatsProperty(): array
    {
        $excluded = count(InstallmentExclusion::idsForSeller((int) auth('seller')->id()));
        $total = Product::query()->where('shop_id', $this->shop()->id)->count();

        return [
            'total' => $total,
            'excluded' => $excluded,
            'allowed' => max(0, $total - $excluded),
        ];
    }

    /** Global categories present among this shop's products (filter chips). */
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
        $limit = min(max($this->perPage, 1), 60);
        $total = (clone $this->filteredQuery())->count();

        $rows = $this->filteredQuery()
            ->with(['mainImage', 'category'])
            ->limit($limit)
            ->get();

        $this->hasMore = $total > $limit;

        return $rows;
    }

    /** The exclusion ids of the loaded rows only — the blade checks membership. */
    public function getExcludedIdsProperty(): array
    {
        return InstallmentExclusion::idsForSeller((int) auth('seller')->id());
    }

    private function filteredQuery(): Builder
    {
        $query = Product::query()->where('products.shop_id', $this->shop()->id);

        if ($this->search !== '') {
            $query->where('products.name', 'like', '%'.$this->search.'%');
        }

        if ($this->categoryFilter !== 0) {
            $query->whereIn('products.category_id', Category::subtreeIds($this->categoryFilter));
        }

        $excludedIds = InstallmentExclusion::idsForSeller((int) auth('seller')->id());

        if ($this->filter === 'excluded') {
            $query->whereIn('products.id', $excludedIds ?: [0]);
        } elseif ($this->filter === 'allowed') {
            $query->whereNotIn('products.id', $excludedIds ?: [0]);
        }

        return match ($this->sort) {
            'old' => $query->oldest(),
            'expensive' => $query->orderByDesc('products.price'),
            'cheap' => $query->orderBy('products.price'),
            default => $query->latest('products.id'),
        };
    }

    public function render()
    {
        return view('livewire.seller.installment-products', [
            'shop' => $this->shop,
            'stats' => $this->stats,
            'categories' => $this->categories,
            'products' => $this->products,
            'excludedIds' => $this->excludedIds,
            'hasBnplEnabled' => $this->hasBnplEnabled,
            'enabledGateways' => $this->enabledGateways,
        ])->title('محصولات مستثنی اقساطی');
    }
}
