<?php

namespace App\Livewire\Admin;

use App\Livewire\Admin\Concerns\GuardsPermissions;
use App\Livewire\Concerns\NotifiesOnValidation;

use App\Models\Order;
use App\Models\PaymentLog;
use App\Support\ActivityLogger;
use App\Support\AdminNotifier;
use App\Support\Fmt;
use App\Support\SellerNotifier;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Admin — single order detail with items, delivery info, payment logs
 * AND the shipment lifecycle control (processing / shipped / delivered
 * + post tracking code) — the admin-side twin of the seller control.
 */
#[Layout('layouts.admin-slot')]
class OrderShow extends Component
{
    use GuardsPermissions;
    use NotifiesOnValidation;

    protected static string $permission = 'admin.orders.show';
    public Order $order;

    /* ── shipment control ── */
    public string $shipStatus = 'none';

    public string $shipTracking = '';

    public function mount(Order $order): void
    {
        $this->order = $order->load(['customer', 'items.product.mainImage', 'items.shop:id,name']);
        $this->shipStatus   = $order->shipment_status;
        $this->shipTracking = (string) ($order->tracking_code ?? '');
    }

    public function saveShipment(): void
    {
        $this->validateWithToast(fn () => $this->validate([
            'shipStatus'   => ['required', 'in:'.implode(',', Order::SHIPMENT_STATUSES)],
            'shipTracking' => [$this->shipStatus === 'shipped' ? 'required' : 'nullable', 'string', 'max:60'],
        ], [
            'shipStatus.required'   => 'وضعیت ارسال را انتخاب کنید.',
            'shipStatus.in'         => 'وضعیت ارسال نامعتبر است.',
            'shipTracking.required' => 'برای «در حال ارسال» وارد کردن کد رهگیری پستی الزامی است.',
            'shipTracking.max'      => 'کد رهگیری حداکثر ۶۰ کاراکتر است.',
        ]), 'ثبت وضعیت ارسال ناموفق بود');

        if ($this->shipStatus !== 'shipped') {
            $this->shipTracking = (string) ($this->order->tracking_code ?? '');
        }

        if (! $this->order->setShipment($this->shipStatus, $this->shipTracking)) {
            $this->dispatch('panel-toast', message: 'ثبت وضعیت ارسال فقط برای سفارش‌های پرداخت‌شده امکان‌پذیر است.', type: 'error');

            return;
        }

        $this->order->refresh();

        ActivityLogger::log(
            'order.shipment',
            'وضعیت ارسال سفارش '.Fmt::fa($this->order->id).' به «'.$this->order->shipmentStatusLabel().'» تغییر کرد'
                .($this->order->tracking_code ? ' — کد رهگیری: '.$this->order->tracking_code : ''),
            subject: $this->order,
            after: ['shipment_status' => $this->order->shipment_status, 'tracking_code' => (string) $this->order->tracking_code],
        );

        // notify the involved sellers so their panel + phone stay in sync
        SellerNotifier::toShops(
            $this->order->items->pluck('shop_id')->all(),
            'order.status',
            'به‌روزرسانی ارسال سفارش '.Fmt::fa($this->order->id),
            'وضعیت ارسال توسط پشتیبانی: '.$this->order->shipmentStatusLabel()
                .($this->order->tracking_code ? ' — کد رهگیری: '.$this->order->tracking_code : ''),
            route('seller.orders'),
            'truck',
            $this->order,
        );

        $this->dispatch('panel-toast', message: 'وضعیت ارسال با موفقیت ثبت شد. ✅', type: 'success');
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
