<?php

namespace App\Livewire\Seller;

use App\Models\Order;
use App\Models\Shop;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.seller-slot')]
class OrdersPage extends Component
{
    public string $statusFilter = 'all';

    /** Lazy load (infinite scroll) */
    public int $perPage = 8;

    public bool $hasMore = true;

    private int $lastLoadAt = 0;

    public function updating(string $property): void
    {
        if ($property === 'statusFilter') {
            $this->perPage = 8;
            $this->hasMore = true;
        }
    }

    /** Triggered by the IntersectionObserver sentinel at the bottom of the list. */
    public function loadMore(): void
    {
        $now = (int) (microtime(true) * 1000);

        if ($now - $this->lastLoadAt < 500) {
            return;
        }

        $this->lastLoadAt = $now;
        $this->perPage   += 8;
    }

    /** The single shop of the logged-in seller. */
    protected function shop(): Shop
    {
        return once(fn () => Shop::ensureForSeller(auth('seller')->user()));
    }

    // ── Computed data ────────────────────────────────────────────────

    public function getOrdersProperty(): Collection
    {
        $shopId = $this->shop()->id;

        $query = Order::query()
            ->whereHas('items', fn ($q) => $q->where('shop_id', $shopId))
            ->when($this->statusFilter !== 'all', fn ($q) => $q->where('status', $this->statusFilter))
            ->with(['customer:id,full_name,phone', 'items' => fn ($q) => $q->with('product.mainImage')])
            ->latest('id');

        $total = (clone $query)->count();
        $rows  = $query->limit($this->perPage)->get();

        $this->hasMore = $total > $this->perPage;

        return $rows;
    }

    /** Items of this order that belong to the seller's shop. */
    public function shopItems(Order $order): Collection
    {
        return $order->items->where('shop_id', $this->shop()->id)->values();
    }

    /** The seller's own slice of the order total (Toman). */
    public function shopTotal(Order $order): int
    {
        return (int) $this->shopItems($order)->sum(fn ($item) => $item->lineTotal());
    }

    public function render()
    {
        return view('livewire.seller.orders-page', [
            'shop' => $this->shop(),
        ])->title('سفارش‌های فروشگاه');
    }
}
