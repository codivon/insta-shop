<?php

namespace App\Livewire\Customer;

use App\Models\Cart;
use App\Models\Favorite;
use App\Models\Product;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.customer-slot')]
class ProductShow extends Component
{
    public Product $product;

    /** Selected gallery image id (null = main image). */
    public ?int $selectedImageId = null;

    /** Quantity stepper (add-to-cart). */
    public int $qty = 1;

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

        // Count a view once per component mount (= per page visit).
        $product->increment('views');
    }

    public function selectImage(?int $imageId): void
    {
        $this->selectedImageId = $imageId;
    }

    public function incrementQty(): void
    {
        $this->qty = min($this->qty + 1, max(1, $this->product->stock));
    }

    public function decrementQty(): void
    {
        $this->qty = max(1, $this->qty - 1);
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

    /** Add the chosen quantity to the cart (never above stock). */
    public function addToCart(): void
    {
        $customer = auth('customer')->user();

        if (! $customer) {
            return;
        }

        if ($this->product->stock < 1) {
            $this->dispatch('product-toast', message: 'این محصول موجود نیست.');

            return;
        }

        $cart  = Cart::ensureForCustomer($customer);
        $qty   = max(1, $this->qty);
        $item  = $cart->items()->firstOrCreate(
            ['product_id' => $this->product->id],
            ['quantity' => 0],
        );

        $item->refresh();
        $item->update(['quantity' => min($item->quantity + $qty, $this->product->stock)]);

        $this->qty = 1;

        $this->dispatch('cart-changed', count: $cart->countDistinct());
        $this->dispatch('product-toast', message: 'به سبد خرید اضافه شد. ✅');
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

    public function getDiscountPercentProperty(): int
    {
        $compare = $this->product->compare_at_price;

        if (! $compare || $compare <= $this->product->price) {
            return 0;
        }

        return (int) round(100 - ($this->product->price / $compare * 100));
    }

    public function render()
    {
        return view('livewire.customer.product-show', [
            'gallery'    => $this->gallery,
            'activeImg'  => $this->activeImage,
            'isFavorite' => $this->isFavorite,
            'discount'   => $this->discountPercent,
        ])->title($this->product->name);
    }
}
