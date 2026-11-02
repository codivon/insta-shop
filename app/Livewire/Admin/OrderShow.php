<?php

namespace App\Livewire\Admin;

use App\Livewire\Admin\Concerns\GuardsPermissions;
use App\Livewire\Concerns\NotifiesOnValidation;

use App\Models\Order;
use App\Models\OrderShipment;
use App\Models\PaymentLog;
use App\Models\Shop;
use App\Support\ActivityLogger;
use App\Support\Fmt;
use App\Support\SellerNotifier;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Admin — single order detail. The order is displayed SPLIT per shop:
 * every shop gets its own section with its items, its slice of the money
 * (items + carrier cost − discount share), its OWN shipment lifecycle
 * control and its OWN cancellation (with a mandatory reason).
 */
#[Layout('layouts.admin-slot')]
class OrderShow extends Component
{
    use GuardsPermissions;
    use NotifiesOnValidation;

    protected static string $permission = 'admin.orders.show';
    public Order $order;

    /* ── per-shop shipment control ── */
    public ?int $shipShopId = null;

    public string $shipStatus = 'none';

    public string $shipTracking = '';

    /* ── per-shop cancel (with reason) ── */
    public ?int $cancelShopId = null;

    public string $cancelReason = '';

    public function mount(Order $order): void
    {
        $this->order = $order->load(['customer', 'items.product.mainImage', 'items.shop:id,name', 'shopShipments.shop:id,name']);
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
                    'shop'         => $this->order->shopShipments->firstWhere('shop_id', (int) $shopId)?->shop
                        ?? $items->first?->product?->shop
                        ?? $items->first?->shop,
                    'items'        => $items->values(),
                    'slice'        => $slice,
                    'carrierTitle' => (string) ($carrierByShop->get((int) $shopId)['carrier_title'] ?? ''),
                ];
            })
            ->values();
    }

    /* ── per-shop shipment flow ────────────────────────────────────── */

    public function openShipment(int $shopId): void
    {
        $slice = $this->order->shopShipments->firstWhere('shop_id', $shopId)
            ?? $this->order->shipmentForShop($shopId);

        if ($slice->isCanceled()) {
            $this->dispatch('panel-toast', message: 'سهم این فروشگاه لغو شده است.', type: 'error');

            return;
        }

        $this->shipShopId   = $shopId;
        $this->shipStatus   = $slice->shipment_status !== 'none' ? $slice->shipment_status : 'processing';
        $this->shipTracking = (string) ($slice->tracking_code ?? '');
    }

    public function closeShipment(): void
    {
        $this->shipShopId   = null;
        $this->shipStatus   = 'none';
        $this->shipTracking = '';
    }

    public function saveShipment(): void
    {
        if ($this->shipShopId === null) {
            return;
        }

        $this->validateWithToast(fn () => $this->validate([
            'shipStatus'   => ['required', 'in:'.implode(',', OrderShipment::STATUSES)],
            'shipTracking' => [$this->shipStatus === 'shipped' ? 'required' : 'nullable', 'string', 'max:60'],
        ], [
            'shipStatus.required'   => 'وضعیت ارسال را انتخاب کنید.',
            'shipStatus.in'         => 'وضعیت ارسال نامعتبر است.',
            'shipTracking.required' => 'برای «در حال ارسال» وارد کردن کد رهگیری پستی الزامی است.',
            'shipTracking.max'      => 'کد رهگیری حداکثر ۶۰ کاراکتر است.',
        ]), 'ثبت وضعیت ارسال ناموفق بود');

        $slice = $this->order->shipmentForShop($this->shipShopId);

        if ($this->shipStatus !== 'shipped') {
            $this->shipTracking = (string) ($slice->tracking_code ?? '');
        }

        if (! $this->order->setShopShipment($this->shipShopId, $this->shipStatus, $this->shipTracking)) {
            $this->dispatch('panel-toast', message: 'ثبت وضعیت ارسال فقط برای سفارش‌های پرداخت‌شده امکان‌پذیر است.', type: 'error');

            return;
        }

        $slice = $this->order->shipmentForShop($this->shipShopId);
        $this->order->refresh();

        ActivityLogger::log(
            'order.shipment',
            'وضعیت ارسال سهم فروشگاه «'.($slice->shop?->name ?? $this->shipShopId).'» از سفارش '.Fmt::fa($this->order->id).' به «'.$slice->statusLabel().'» تغییر کرد'
                .($slice->tracking_code ? ' — کد رهگیری: '.$slice->tracking_code : ''),
            subject: $this->order,
            after: ['shop_id' => $this->shipShopId, 'shipment_status' => $slice->shipment_status, 'tracking_code' => (string) $slice->tracking_code],
        );

        // notify THIS shop's seller so their panel + phone stay in sync
        if ($slice->shop?->seller_id) {
            SellerNotifier::notify(
                (int) $slice->shop->seller_id,
                'order.status',
                'به‌روزرسانی ارسال سفارش '.Fmt::fa($this->order->id),
                'وضعیت ارسال فروشگاه شما توسط پشتیبانی: '.$slice->statusLabel()
                    .($slice->tracking_code ? ' — کد رهگیری: '.$slice->tracking_code : ''),
                route('seller.orders'),
                'truck',
                $this->order,
            );
        }

        $this->dispatch('panel-toast', message: 'وضعیت ارسال این فروشگاه با موفقیت ثبت شد. ✅', type: 'success');

        $this->closeShipment();
    }

    /* ── per-shop cancel flow ──────────────────────────────────────── */

    public function openCancel(int $shopId): void
    {
        $slice = $this->order->shopShipments->firstWhere('shop_id', $shopId)
            ?? $this->order->shipmentForShop($shopId);

        if ($slice->isCanceled() || $slice->shipment_status === 'delivered') {
            $this->dispatch('panel-toast', message: 'سهم این فروشگاه قابل لغو نیست.', type: 'error');

            return;
        }

        $this->cancelShopId  = $shopId;
        $this->cancelReason  = '';
    }

    public function closeCancel(): void
    {
        $this->cancelShopId  = null;
        $this->cancelReason  = '';
    }

    public function confirmCancel(): void
    {
        if ($this->cancelShopId === null) {
            return;
        }

        $this->validateWithToast(fn () => $this->validate([
            'cancelReason' => ['required', 'string', 'min:3', 'max:300'],
        ], [
            'cancelReason.required' => 'دلیل لغو را بنویسید.',
            'cancelReason.min'      => 'دلیل لغو باید حداقل ۳ کاراکتر باشد.',
            'cancelReason.max'      => 'دلیل لغو حداکثر ۳۰۰ کاراکتر است.',
        ]), 'لغو ناموفق بود');

        $shop = Shop::query()->find($this->cancelShopId);

        if (! $shop || ! $this->order->cancelShopSlice($shop->id, $this->cancelReason, 'admin')) {
            $this->dispatch('panel-toast', message: 'لغو این سهم از سفارش امکان‌پذیر نیست.', type: 'error');
            $this->closeCancel();

            return;
        }

        ActivityLogger::log(
            'order.cancel',
            'مدیر سهم فروشگاه «'.$shop->name.'» از سفارش '.Fmt::fa($this->order->id).' را لغو کرد — دلیل: '.$this->cancelReason,
            subject: $this->order,
            after: ['shop_id' => $shop->id, 'canceled_by' => 'admin', 'cancel_reason' => $this->cancelReason],
        );

        $this->order->refresh();

        $this->dispatch('panel-toast', message: 'سهم این فروشگاه لغو شد و بازگشت وجه مشتری ثبت شد.', type: 'success');

        $this->closeCancel();
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
            'logs'       => $this->logs,
            'shopGroups' => $this->shopGroups,
        ])->title('سفارش #'.Fmt::fa($this->order->id))
            ->layout('layouts.admin-slot', ['breadcrumb' => 'مدیریت کل — جزئیات سفارش']);
    }
}
