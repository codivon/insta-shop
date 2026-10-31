<?php

namespace App\Livewire\Customer;

use App\Models\Cart;
use App\Models\Customer;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.customer-slot')]
class CartPage extends Component
{
    public function incrementQty(int $itemId): void
    {
        $item = $this->ownedItem($itemId);

        if (! $item || $item->product->stock < 1) {
            return;
        }

        if ($item->quantity >= $item->product->stock) {
            $this->dispatch('cart-toast', message: 'بیش از موجودی انبار نمی‌شود.');

            return;
        }

        $item->increment('quantity');

        $this->syncBadge();
    }

    public function decrementQty(int $itemId): void
    {
        $item = $this->ownedItem($itemId);

        if (! $item) {
            return;
        }

        if ($item->quantity <= 1) {
            $item->delete();
        } else {
            $item->decrement('quantity');
        }

        $this->syncBadge();
    }

    public function removeItem(int $itemId): void
    {
        $this->ownedItem($itemId)?->delete();

        $this->syncBadge();
        $this->dispatch('cart-toast', message: 'محصول از سبد حذف شد.');
    }

    public function clearCart(): void
    {
        $this->cart()->items()->delete();

        $this->syncBadge();
        $this->dispatch('cart-toast', message: 'سبد خرید خالی شد.');
    }

    // ── Computed data ────────────────────────────────────────────────

    public function getCartProperty(): ?Cart
    {
        return $this->customer()?->cart;
    }

    public function getItemsProperty(): Collection
    {
        return $this->cart()
            ? $this->cart()->items()->with(['product.mainImage', 'product.shop:id,name,slug'])->get()
            : collect();
    }

    public function getValidItemsProperty(): Collection
    {
        return $this->items->filter(
            fn ($item) => $item->product && $item->product->status === 'published' && $item->product->stock > 0,
        );
    }

    public function getStaleItemsProperty(): Collection
    {
        return $this->items->reject(
            fn ($item) => $this->validItems->contains('id', $item->id),
        );
    }

    public function getTotalProperty(): int
    {
        return (int) $this->validItems->sum(fn ($item) => $item->lineTotal());
    }

    public function getUnitsProperty(): int
    {
        return (int) $this->validItems->sum('quantity');
    }

    public function render()
    {
        return view('livewire.customer.cart-page', [
            'items'      => $this->items,
            'validItems' => $this->validItems,
            'staleItems' => $this->staleItems,
            'total'      => $this->total,
            'units'      => $this->units,
        ])->title('سبد خرید');
    }

    // ── Helpers ──────────────────────────────────────────────────────

    private function customer(): ?Customer
    {
        return auth('customer')->user();
    }

    private function cart(): ?Cart
    {
        return $this->customer()?->cart()->first();
    }

    /** Only items belonging to the logged-in customer's cart. */
    private function ownedItem(int $itemId): ?\App\Models\CartItem
    {
        $cart = $this->cart();

        if (! $cart) {
            return null;
        }

        return \App\Models\CartItem::query()
            ->where('cart_id', $cart->id)
            ->find($itemId);
    }

    private function syncBadge(): void
    {
        $cart = $this->cart();

        $this->dispatch('cart-changed', count: $cart ? $cart->countDistinct() : 0);
    }
}
