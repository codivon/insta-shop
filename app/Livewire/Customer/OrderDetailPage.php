<?php

namespace App\Livewire\Customer;

use App\Models\Order;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.customer-slot')]
class OrderDetailPage extends Component
{
    public Order $order;

    public function mount(Order $order): void
    {
        $customer = auth('customer')->user();

        abort_unless($customer && $order->customer_id === $customer->id, 403);
    }

    public function render()
    {
        return view('livewire.customer.order-detail-page', [
            'items' => $this->order->items()->with(['product.mainImage', 'product.shop:id,name,slug'])->get(),
        ])->title('سفارش #'.$this->order->id);
    }
}
