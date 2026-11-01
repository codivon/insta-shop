<?php

namespace App\Livewire\Customer;

use App\Models\Cart;
use App\Models\Customer;
use App\Models\Product;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.customer-slot')]
class FavoritesPage extends Component
{
    /** Un-favorite straight from the list. */
    public function removeFavorite(int $productId): void
    {
        $customer = $this->customer();

        $customer?->favorites()
            ->where('product_id', $productId)
            ->delete();
    }

    /** Quick add-to-cart from the favorites list. */
    public function addToCart(int $productId): void
    {
        $customer = $this->customer();
        $product  = $customer?->favorites()->where('product_id', $productId)->first()?->product;

        if (! $customer || ! $product || $product->status !== 'published') {
            return;
        }

        // Variant products need an explicit combination choice → product page.
        if ($product->hasVariants()) {
            $this->redirect(route('shop.product.show', $productId), navigate: true);

            return;
        }

        if ($product->type !== Product::TYPE_DOWNLOADABLE && $product->stock < 1) {
            $this->dispatch('fav-toast', message: 'این محصول موجود نیست.');

            return;
        }

        $cart = Cart::ensureForCustomer($customer);

        $item = $cart->items()->firstOrCreate(
            ['product_id' => $productId],
            ['quantity' => 1],
        );

        if (! $item->wasRecentlyCreated) {
            $item->increment('quantity', min(1, max(0, $product->stock - $item->quantity)));
        }

        $this->dispatch('cart-changed', count: $cart->countDistinct());
        $this->dispatch('fav-toast', message: '«'.$product->name.'» به سبد خرید اضافه شد.');
    }

    // ── Computed data ────────────────────────────────────────────────

    public function getFavoritesProperty(): \Illuminate\Support\Collection
    {
        return $this->customer()
            ? $this->customer()->favorites()
                ->with(['product.mainImage', 'product.shop.seller:id,instagram_username'])
                ->latest('id')
                ->get()
                ->filter(fn ($fav) => $fav->product && $fav->product->status === 'published')
            : collect();
    }

    public function render()
    {
        return view('livewire.customer.favorites-page', [
            'favorites' => $this->favorites,
        ])->title('علاقه‌مندی‌ها');
    }

    // ── Helpers ──────────────────────────────────────────────────────

    private function customer(): ?Customer
    {
        return auth('customer')->user();
    }
}
