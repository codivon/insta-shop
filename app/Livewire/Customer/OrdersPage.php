<?php

namespace App\Livewire\Customer;

use App\Models\Customer;
use App\Models\Order;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.customer-slot')]
class OrdersPage extends Component
{
    /** Lazy load (infinite scroll) */
    public int $perPage = 6;

    public bool $hasMore = true;

    private int $lastLoadAt = 0;

    /** Triggered by the IntersectionObserver sentinel at the bottom of the list. */
    public function loadMore(): void
    {
        $now = (int) (microtime(true) * 1000);

        if ($now - $this->lastLoadAt < 500) {
            return;
        }

        $this->lastLoadAt = $now;
        $this->perPage   += 6;
    }

    // ── Computed data ────────────────────────────────────────────────

    public function getOrdersProperty(): Collection
    {
        $customer = $this->customer();

        if (! $customer) {
            return collect();
        }

        $query = $customer->orders()->with(['items' => fn ($q) => $q->with('product.mainImage')]);

        $total   = (clone $query)->count();
        $rows    = $query->limit($this->perPage)->get();
        $this->hasMore = $total > $this->perPage;

        return $rows;
    }

    public function render()
    {
        return view('livewire.customer.orders-page', [
            'orders' => $this->orders,
        ])->title('سفارش‌های من');
    }

    // ── Helpers ──────────────────────────────────────────────────────

    private function customer(): ?Customer
    {
        return auth('customer')->user();
    }
}
