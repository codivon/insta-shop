<?php

namespace App\Livewire\Seller;

use App\Livewire\Concerns\NotifiesOnValidation;
use App\Models\Product;
use App\Models\ProductBump;
use App\Models\Wallet;
use App\Support\ActivityLogger;
use App\Support\Bump\BumpService;
use App\Support\Fmt;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Seller — «نردبان» (v0.23.0, Divar-style): pick a published product,
 * choose a duration and pay from the wallet to keep it at the very top
 * of the home page for that window. Renewal while running extends the
 * current bump from its ends_at (BumpService::grant stacking rules).
 */
#[Layout('layouts.seller-slot')]
class BumpPage extends Component
{
    use NotifiesOnValidation;

    /* ── purchase confirm modal ── */
    public bool $buyOpen = false;

    public ?int $buyProductId = null;

    public int $buyDays = 7;

    public string $productQuery = '';

    public function openBuy(int $productId): void
    {
        $product = $this->bumpableProducts->firstWhere('id', $productId);

        if ($product === null || BumpService::pricePerDay() <= 0) {
            return;
        }

        $this->resetErrorBag();
        $this->buyProductId = $productId;
        $this->buyDays = 7;
        $this->buyOpen = true;
    }

    public function closeBuy(): void
    {
        $this->buyOpen = false;
        $this->buyProductId = null;
        $this->productQuery = '';
        $this->resetErrorBag();
    }

    public function setBuyDays(int $days): void
    {
        $this->buyDays = max(1, min(90, $days));
    }

    /** Confirm modal → debit the wallet and start/extend the bump. */
    public function confirmBuy(): void
    {
        $seller = auth('seller')->user();

        $product = $seller
            ? Product::query()->where('shop_id', $seller->shop?->id)->find($this->buyProductId)
            : null;

        if ($seller === null || $product === null) {
            $this->closeBuy();

            return;
        }

        [$ok, $row, $error] = BumpService::purchaseFromWallet($seller, $product, $this->buyDays);

        if (! $ok) {
            $this->addError('buy', $error ?? 'خرید نردبان انجام نشد.');

            return;
        }

        ActivityLogger::log(
            'bump.purchased',
            'فروشنده @'.($seller->instagram_username ?: $seller->phone).' محصول «'.$product->name.'» را '.Fmt::toFa($this->buyDays).' روز نردبان کرد — '.Fmt::money($row->price_paid),
            subject: $row,
            after: ['product_id' => $product->id, 'days' => $this->buyDays, 'ends_at' => $row->ends_at->toDateTimeString()],
        );

        $this->buyOpen = false;
        $this->buyProductId = null;
        $this->productQuery = '';

        session()->flash('bump-flash', 'نردبان «'.$product->name.'» فعال شد 🚀 مبلغ از کیف پول شما کسر شد.');
    }

    /* ───────────────────────── data ───────────────────────── */

    /** The seller's published products, optionally filtered by the picker query. */
    public function getBumpableProductsProperty(): Collection
    {
        $seller = auth('seller')->user();
        $shopId = (int) $seller?->shop?->id;

        if ($shopId <= 0) {
            return collect();
        }

        $q = trim($this->productQuery);

        return Product::query()
            ->where('shop_id', $shopId)
            ->where('status', 'published')
            ->when($q !== '', fn ($query) => $query->where('name', 'like', '%'.$q.'%'))
            ->with('mainImage')
            ->orderByDesc('id')
            ->limit(12)
            ->get(['id', 'shop_id', 'name', 'price', 'compare_at_price']);
    }

    /** Price preview of the confirm modal. */
    public function getBuyPriceProperty(): int
    {
        return BumpService::priceForDays($this->buyDays);
    }

    public function render()
    {
        $seller = auth('seller')->user();

        $active = ProductBump::query()
            ->where('seller_id', (int) $seller?->id)
            ->where('ends_at', '>', now())
            ->with(['product:id,name'])
            ->orderByDesc('ends_at')
            ->get();

        $history = ProductBump::query()
            ->where('seller_id', (int) $seller?->id)
            ->with('product:id,name')
            ->orderByDesc('id')
            ->limit(10)
            ->get();

        $wallet = $seller ? Wallet::ensureForSeller($seller->id) : null;

        return view('livewire.seller.bump-page', [
            'active' => $active,
            'history' => $history,
            'perDay' => BumpService::pricePerDay(),
            'packages' => BumpService::packages(),
            'products' => $this->bumpableProducts,
            'walletBalance' => (int) ($wallet?->balance ?? 0),
            'buyProduct' => $this->buyProductId ? Product::query()->with('mainImage')->find($this->buyProductId) : null,
            'buyPrice' => $this->buyPrice,
        ])->title('نردبان');
    }
}
