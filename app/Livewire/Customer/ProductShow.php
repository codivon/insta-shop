<?php

namespace App\Livewire\Customer;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Favorite;
use App\Models\Product;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.customer-slot')]
class ProductShow extends Component
{
    public Product $product;

    /** Selected gallery image id (null = main image). */
    public ?int $selectedImageId = null;

    /** Picked variant (null = plain product / no choice yet). */
    public ?int $selectedVariantId = null;

    public function mount(int $productId): void
    {
        // Only published products of publicly visible shops are reachable.
        $product = Product::query()
            ->where('status', 'published')
            ->whereHas('shop', fn ($q) => $q->publiclyVisible())
            ->with(['shop.seller', 'category', 'images'])
            ->find($productId);

        abort_if(! $product, 404);

        $this->product = $product;

        // Default to the first in-stock variant (if the product has any).
        $firstAvailable = $this->variants->first(fn ($variant) => $variant->stock > 0)
            ?? $this->variants->first();

        $this->selectedVariantId = $firstAvailable?->id;

        // Count a view once per component mount (= per page visit):
        // cumulative counter on the product + daily log for reports.
        \App\Models\ProductView::recordView($product);
    }

    /* ─────────────────────── interactions ─────────────────────── */

    public function selectImage(?int $imageId): void
    {
        $this->selectedImageId = $imageId;
    }

    public function selectVariant(int $variantId): void
    {
        if ($this->variants->contains('id', $variantId)) {
            $this->selectedVariantId = $variantId;
        }
    }

    public function toggleFavorite(): void
    {
        $customer = auth('customer')->user();

        if (! $customer) {
            return;
        }

        $existing = Favorite::query()
            ->where('customer_id', $customer->id)
            ->where('product_id', $this->product->id)
            ->first();

        if ($existing) {
            $existing->delete();

            return;
        }

        Favorite::query()->firstOrCreate([
            'customer_id' => $customer->id,
            'product_id'  => $this->product->id,
        ]);
    }

    /* ───────────────────── cart (fixed bottom bar) ───────────────────── */

    /** «افزودن به سبد خرید» — adds one unit of the picked combination. */
    public function addToCart(): void
    {
        $customer = auth('customer')->user();

        if (! $customer) {
            $this->redirect(route('shop.login'), navigate: true);

            return;
        }

        if ($this->product->hasVariants() && ! $this->selectedVariant) {
            $this->dispatch('product-toast', message: 'لطفاً ابتدا یک تنوع انتخاب کنید.');

            return;
        }

        $cap = $this->purchaseCap;

        if (! $this->isDownloadable && $this->purchaseCap < 1) {
            $this->dispatch('product-toast', message: 'این محصول موجود نیست.');

            return;
        }

        $cart = Cart::ensureForCustomer($customer);
        $item = $cart->items()->firstOrCreate(
            ['product_id' => $this->product->id, 'variant_id' => $this->variantKey],
            ['quantity' => 0],
        );

        $item->refresh();

        if ($item->quantity >= $cap) {
            $this->dispatch('product-toast', message: 'بیش از این مقدار نمی‌توانید خرید کنید.');

            return;
        }

        $item->update(['quantity' => $item->quantity + 1]);

        $this->dispatch('cart-changed', count: $cart->countDistinct());
    }

    /** + on the fixed bar stepper — instant one-unit bump of the cart line. */
    public function cartIncrement(): void
    {
        $customer = auth('customer')->user();

        if (! $customer) {
            return;
        }

        $item = $this->cartLine;

        if (! $item) {
            $this->addToCart();

            return;
        }

        $cap = $this->purchaseCap;

        if ($item->quantity >= $cap) {
            $this->dispatch('product-toast', message: 'بیش از این مقدار نمی‌توانید خرید کنید.');

            return;
        }

        $item->update(['quantity' => $item->quantity + 1]);

        $this->dispatch('cart-changed', count: Cart::ensureForCustomer($customer)->countDistinct());
    }

    /** − on the fixed bar stepper — instant one-unit drop (below the minimum → line removed). */
    public function cartDecrement(): void
    {
        $customer = auth('customer')->user();

        if (! $customer) {
            return;
        }

        $item = $this->cartLine;

        if (! $item) {
            return;
        }

        $minPurchasable = max(1, (int) ($this->selectedVariant?->min_purchase ?? 0));

        if ($item->quantity <= $minPurchasable) {
            $item->delete();
        } else {
            $item->decrement('quantity');
        }

        $this->dispatch('cart-changed', count: Cart::ensureForCustomer($customer)->countDistinct());
    }

    // ── Computed data ────────────────────────────────────────────────

    public function getGalleryProperty(): array
    {
        return $this->product->images->map(fn ($img) => [
            'id'  => $img->id,
            'url' => $img->url(),
        ])->all();
    }

    public function getActiveImageProperty(): ?array
    {
        $gallery = $this->gallery;

        if ($gallery === []) {
            return null;
        }

        return collect($gallery)->firstWhere('id', $this->selectedImageId) ?? $gallery[0];
    }

    public function getIsFavoriteProperty(): bool
    {
        $customer = auth('customer')->user();

        if (! $customer) {
            return false;
        }

        return Favorite::query()
            ->where('customer_id', $customer->id)
            ->where('product_id', $this->product->id)
            ->exists();
    }

    /** Active variants of this product (with their attribute picks). */
    public function getVariantsProperty(): Collection
    {
        if ($this->product->type === Product::TYPE_DOWNLOADABLE) {
            return collect();
        }

        return $this->product->activeVariants()
            ->with(['values.attribute', 'values.value'])
            ->get();
    }

    public function getSelectedVariantProperty(): ?\App\Models\ProductVariant
    {
        if ($this->variants->isEmpty()) {
            return null;
        }

        return $this->variants->firstWhere('id', $this->selectedVariantId)
            ?? $this->variants->first();
    }

    /** 0 = plain product line, otherwise the picked variant id. */
    public function getVariantKeyProperty(): int
    {
        return (int) ($this->selectedVariant?->id ?? 0);
    }

    /** Price on display: picked variant (discount-aware) or the product price. */
    public function getDisplayPriceProperty(): int
    {
        return $this->selectedVariant
            ? $this->selectedVariant->finalPrice()
            : (int) $this->product->price;
    }

    /** Downloadable goods are never stock-limited. */
    public function getIsDownloadableProperty(): bool
    {
        return $this->product->type === Product::TYPE_DOWNLOADABLE;
    }

    public function getDisplayStockProperty(): int
    {
        return $this->selectedVariant
            ? (int) $this->selectedVariant->stock
            : (int) $this->product->stock;
    }

    /** Max units purchasable for the current selection (stock vs max_purchase). */
    public function getPurchaseCapProperty(): int
    {
        if ($this->isDownloadable) {
            return 99;
        }

        $cap = $this->displayStock;
        $maxPerOrder = (int) ($this->selectedVariant?->max_purchase ?? 0);

        return $maxPerOrder > 0 ? min($cap, $maxPerOrder) : $cap;
    }

    /** The current (product, variant) cart line of the logged-in customer. */
    public function getCartLineProperty(): ?CartItem
    {
        $customer = auth('customer')->user();

        if (! $customer) {
            return null;
        }

        $cartId = Cart::query()->where('customer_id', $customer->id)->value('id');

        if (! $cartId) {
            return null;
        }

        return CartItem::query()
            ->where('cart_id', $cartId)
            ->where('product_id', $this->product->id)
            ->where('variant_id', $this->variantKey)
            ->first();
    }

    /** Quantity of the current selection already in the cart (0 = not added yet). */
    public function getCartQtyProperty(): int
    {
        return (int) ($this->cartLine?->quantity ?? 0);
    }

    public function getDiscountPercentProperty(): int
    {
        $compare = $this->product->compare_at_price;

        if (! $compare || $compare <= $this->product->price) {
            return 0;
        }

        return (int) round(100 - ($this->product->price / $compare * 100));
    }

    /** «فروش ویژه» deadline in ms epoch (null = no active special sale) — drives the countdown. */
    public function getSpecialEndsMsProperty(): ?int
    {
        if (! $this->product->isSpecialActive() || ! $this->product->special_ends_at) {
            return null;
        }

        return $this->product->special_ends_at->timestamp * 1000;
    }

    /** Related products: same shop first, same category prioritized. */
    public function getRelatedProperty(): Collection
    {
        return Product::query()
            ->where('shop_id', $this->product->shop_id)
            ->where('status', 'published')
            ->whereKeyNot($this->product->id)
            ->with(['mainImage'])
            ->when($this->product->shop_category_id, fn ($q) => $q->orderByRaw('shop_category_id = ? desc', [$this->product->shop_category_id]))
            ->latest()
            ->limit(12)
            ->get();
    }

    public function render()
    {
        return view('livewire.customer.product-show', [
            'gallery'        => $this->gallery,
            'activeImg'      => $this->activeImage,
            'isFavorite'     => $this->isFavorite,
            'discount'       => $this->discountPercent,
            'specialEndsMs'  => $this->specialEndsMs,
            'variants'       => $this->variants,
            'selectedVariant'=> $this->selectedVariant,
            'displayPrice'   => $this->displayPrice,
            'displayStock'   => $this->displayStock,
            'cartQty'        => $this->cartQty,
            'purchaseCap'    => $this->purchaseCap,
            'isDownloadable' => $this->isDownloadable,
            'related'        => $this->related,
        ])->title($this->product->name);
    }
}
