<?php

namespace App\Livewire\Admin\Users;

use App\Livewire\Admin\Concerns\GuardsPermissions;
use App\Models\BankCard;
use App\Models\Carrier;
use App\Models\Category;
use App\Models\Discount;
use App\Models\Order;
use App\Models\Product;
use App\Models\Seller;
use App\Models\Shop;
use App\Models\ShopFilter;
use App\Models\Wallet;
use App\Support\Fmt;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Admin — seller SHOW page (batch 76): cards + charts + everything,
 * every block a separate URL-persisted tab (shareable / back-friendly):
 *
 *   overview  → identity card + stat cards + 14-day sales chart
 *   products  → the shop's products with search + status filter
 *   orders    → orders that contain this shop's items
 *   discounts → the shop's discount codes
 *   wallet    → balance + deposit/withdrawal ledger
 *   catalog   → categories / carriers / filters / bank cards inventory
 */
#[Layout('layouts.admin-slot')]
class SellerShow extends Component
{
    use GuardsPermissions;

    protected static string $permission = 'admin.users.sellers';

    #[Url]
    public string $tab = 'overview';

    #[Url]
    public string $productSearch = '';

    #[Url]
    public string $productStatus = 'all'; // all|published|draft

    #[Url]
    public string $orderStatus = 'all'; // all|pending|paid|failed|canceled

    public int $perPage = 12;

    public bool $hasMore = true;

    private int $lastLoadAt = 0;

    public function mount(Seller $seller): void
    {
        $this->sellerId = $seller->id;

        $valid = ['overview', 'products', 'orders', 'discounts', 'wallet', 'catalog'];

        if (! in_array($this->tab, $valid, true)) {
            $this->tab = 'overview';
        }

        if (! in_array($this->productStatus, ['all', 'published', 'draft'], true)) {
            $this->productStatus = 'all';
        }

        if (! in_array($this->orderStatus, array_merge(['all'], Order::STATUSES), true)) {
            $this->orderStatus = 'all';
        }
    }

    public function updating(string $property): void
    {
        if (in_array($property, ['productSearch', 'productStatus', 'orderStatus'], true)) {
            $this->perPage = 12;
            $this->hasMore = true;
        }
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

    // ── Core ─────────────────────────────────────────────────────────

    public function getSellerProperty(): ?Seller
    {
        return Seller::query()->with('shop:id,seller_id,name,status,slug,instagram_username,created_at')->find($this->sellerId);
    }

    public int $sellerId = 0;

    private function shop()
    {
        return $this->seller?->shop;
    }

    /** 14-day chart of this shop's PAID order totals. */
    public function getChartProperty(): Collection
    {
        $from = Carbon::today()->subDays(13);

        $rows = Order::query()
            ->where('status', 'paid')
            ->whereHas('items', fn ($q) => $q->where('shop_id', $this->shopId()))
            ->whereBetween('paid_at', [$from->copy()->startOfDay(), now()])
            ->get(['paid_at', 'total_amount'])
            ->groupBy(fn ($order) => $order->paid_at?->format('Y-m-d'));

        return collect(range(0, 13))->map(function (int $i) use ($from, $rows) {
            $day = $from->copy()->addDays($i);
            $key = $day->format('Y-m-d');

            return [
                'label' => $day->locale('fa')->minDayName.' '.Fmt::toFa($day->day),
                'orders' => (int) ($rows[$key] ?? collect())->count(),
                'total' => (int) ($rows[$key] ?? collect())->sum('total_amount'),
            ];
        });
    }

    private function shopId(): int
    {
        return (int) ($this->seller?->shop?->id ?? 0);
    }

    // ── Overview stats ───────────────────────────────────────────────

    public function getStatsProperty(): array
    {
        $shopId = $this->shopId();

        $itemsTotal = Order::query()
            ->where('status', 'paid')
            ->whereHas('items', fn ($q) => $q->where('shop_id', $shopId))
            ->get(['id'])
            ->count();

        $paidOrders = Order::query()
            ->where('status', 'paid')
            ->whereHas('items', fn ($q) => $q->where('shop_id', $shopId))
            ->with('items:id,order_id,shop_id,unit_price,quantity')
            ->get();

        $goodsTotal = (int) $paidOrders->sum(fn ($order) => $order->items
            ->where('shop_id', $shopId)
            ->sum(fn ($item) => $item->unit_price * $item->quantity));

        $wallet = $this->seller ? Wallet::query()->where('seller_id', $this->seller->id)->first() : null;

        return [
            'products' => Product::query()->where('shop_id', $shopId)->count(),
            'published' => Product::query()->where('shop_id', $shopId)->where('status', 'published')->count(),
            'orders' => $itemsTotal,
            'goods_total' => $goodsTotal,
            'discounts' => Discount::query()->where('shop_id', $shopId)->count(),
            'wallet_balance' => (int) ($wallet?->balance ?? 0),
            'categories' => $shopId > 0 ? Category::query()->whereIn('id', Product::query()->where('shop_id', $shopId)->where('status', 'published')->select('category_id'))->count() : 0,
            'carriers' => Carrier::query()->where('shop_id', $shopId)->count(),
            'filters' => ShopFilter::query()->where('shop_id', $shopId)->count(),
            'views' => Product::query()->where('shop_id', $shopId)->sum('views'),
        ];
    }

    // ── Tab datasets ─────────────────────────────────────────────────

    public function getProductsProperty(): Collection
    {
        $term = Fmt::toLatin($this->productSearch);

        $query = Product::query()
            ->where('shop_id', $this->shopId())
            ->when($term !== '', fn ($q) => $q->where('name', 'like', "%{$this->productSearch}%"))
            ->when($this->productStatus !== 'all', fn ($q) => $q->where('status', $this->productStatus))
            ->with(['mainImage', 'category:id,name'])
            ->latest('id');

        $total = (clone $query)->count();
        $rows = $query->limit($this->perPage)->get();

        $this->hasMore = $total > $this->perPage;

        return $rows;
    }

    public function getOrdersProperty(): Collection
    {
        return Order::query()
            ->whereHas('items', fn ($q) => $q->where('shop_id', $this->shopId()))
            ->when($this->orderStatus !== 'all', fn ($q) => $q->where('status', $this->orderStatus))
            ->with('customer:id,full_name,phone')
            ->latest('id')
            ->limit(20)
            ->get();
    }

    public function getDiscountsProperty(): Collection
    {
        return Discount::query()
            ->where('shop_id', $this->shopId())
            ->latest('id')
            ->limit(20)
            ->get();
    }

    public function getWalletProperty(): ?Wallet
    {
        return $this->seller ? Wallet::query()->where('seller_id', $this->seller->id)->first() : null;
    }

    public function getTransactionsProperty(): Collection
    {
        $wallet = $this->wallet;

        return $wallet
            ? $wallet->transactions()->with('order:id,total_amount,status')->limit(20)->get()
            : collect();
    }

    public function getBankCardsProperty(): Collection
    {
        return $this->sellerId > 0
            ? BankCard::query()->where('seller_id', $this->sellerId)->orderByDesc('id')->limit(10)->get()
            : collect();
    }

    public function getCategoriesProperty(): Collection
    {
        $shop = $this->shopId() > 0 ? Shop::query()->find($this->shopId()) : null;

        return $shop
            ? $shop->usedCategories()->limit(30)->get()
            : collect();
    }

    public function getCarriersProperty(): Collection
    {
        return $this->shopId() > 0
            ? Carrier::query()->where('shop_id', $this->shopId())->with('tariffs')->orderBy('sort')->orderBy('id')->get()
            : collect();
    }

    public function getFiltersProperty(): Collection
    {
        return $this->shopId() > 0
            ? ShopFilter::query()->where('shop_id', $this->shopId())->with('items')->orderBy('sort')->get()
            : collect();
    }

    public function render()
    {
        $seller = $this->seller;

        abort_if(! $seller, 404);

        return view('livewire.admin.users.seller-show', [
            'seller' => $seller,
            'shop' => $seller->shop,
            'stats' => $this->stats,
            'chart' => $this->chart,
            'products' => $this->products,
            'orders' => $this->orders,
            'discounts' => $this->discounts,
            'wallet' => $this->wallet,
            'transactions' => $this->transactions,
            'bankCards' => $this->bankCards,
            'categories' => $this->categories,
            'carriers' => $this->carriers,
            'filters' => $this->filters,
            'hasMore' => $this->hasMore,
        ])->title('فروشنده: '.($seller->full_name ?: '@'.$seller->instagram_username))
            ->layout('layouts.admin-slot', ['breadcrumb' => 'مدیریت کل — فروشندگان']);
    }
}
