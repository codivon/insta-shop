<?php

namespace App\Livewire\Admin;

use App\Models\Order;
use App\Models\PaymentLog;
use App\Support\Fmt;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Admin — single order detail with items, delivery info & payment logs.
 */
#[Layout('layouts.admin-slot')]
class OrderShow extends Component
{
    public Order $order;

    public function mount(Order $order): void
    {
        $this->order = $order->load(['customer', 'items.product.mainImage', 'items.shop:id,name']);
    }

    public function getLogsProperty(): Collection
    {
        return PaymentLog::query()
            ->where('order_id', $this->order->id)
            ->latest('id')
            ->limit(20)
            ->get();
    }

    /** Human-readable payment-log event label. */
    public function eventLabel(string $event): string
    {
        return match ($event) {
            'purchase' => 'درخواست پرداخت',
            'redirect' => 'هدایت به درگاه',
            'precheck_failed' => 'عدم تأیید مقدماتی',
            'verify_ok' => 'تأیید پرداخت',
            'verify_failed' => 'خطای تأیید',
            default => $event,
        };
    }

    public function render()
    {
        return view('livewire.admin.order-show', [
            'logs' => $this->logs,
        ])->title('سفارش #'.Fmt::fa($this->order->id))
            ->layout('layouts.admin-slot', ['breadcrumb' => 'مدیریت کل — جزئیات سفارش']);
    }
}
