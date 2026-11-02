<?php

namespace App\Livewire\Customer;

use App\Models\Order;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Customer — single order page. A multi-shop checkout is displayed
 * SPLIT: every shop gets its own card with its own items, its carrier
 * cost, its share of the discount code, its own shipment status and
 * its own tracking code.
 */
#[Layout('layouts.customer-slot')]
class OrderDetailPage extends Component
{
    public Order $order;

    public function mount(Order $order): void
    {
        $customer = auth('customer')->user();

        abort_unless($customer && $order->customer_id === $customer->id, 403);
    }

    /** Items grouped per shop with each shop's slice + carrier snapshot. */
    public function getShopGroupsProperty(): Collection
    {
        $this->order->ensureShopShipments();

        $carrierByShop = collect();

        foreach ((array) ($this->order->shipments ?? []) as $entry) {
            $carrierByShop->put((int) ($entry['shop_id'] ?? 0), $entry);
        }

        return $this->order->items
            ->groupBy('shop_id')
            ->map(function ($items, $shopId) use ($carrierByShop) {
                $slice = $this->order->shopShipments->firstWhere('shop_id', (int) $shopId);

                return [
                    'shopId'       => (int) $shopId,
                    'shop'         => $items->first?->product?->shop ?? $slice?->shop,
                    'items'        => $items->values(),
                    'slice'        => $slice,
                    'carrierTitle' => (string) ($carrierByShop->get((int) $shopId)['carrier_title'] ?? ''),
                ];
            })
            ->values();
    }

    public function render()
    {
        $this->order->load(['items.product.mainImage', 'items.product.shop:id,name,slug', 'shopShipments.shop:id,name']);

        return view('livewire.customer.order-detail-page', [
            'items'      => $this->order->items,
            'shopGroups' => $this->shopGroups,
        ])->title('سفارش #'.$this->order->id);
    }
}
