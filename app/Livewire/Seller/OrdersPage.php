<?php

namespace App\Livewire\Seller;

use App\Livewire\Concerns\NotifiesOnValidation;
use App\Models\Order;
use App\Models\OrderShipment;
use App\Models\Shop;
use App\Support\ActivityLogger;
use App\Support\AdminNotifier;
use App\Support\Fmt;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Seller — سفارش‌ها, scoped to THIS shop's slice of every order:
 * a multi-shop checkout appears here with only this shop's items,
 * its own carrier cost, its own discount share and its OWN shipment
 * lifecycle (processing / shipped / delivered + tracking code) and
 * its own cancellation (with a reason).
 */
#[Layout('layouts.seller-slot')]
class OrdersPage extends Component
{
    use NotifiesOnValidation;

    public string $statusFilter = 'all';

    /** Lazy load (infinite scroll) */
    public int $perPage = 8;

    public bool $hasMore = true;

    private int $lastLoadAt = 0;

    /* ── detail modal ── */
    public ?int $detailId = null;

    /* ── shipment modal (this shop's slice) ── */
    public ?int $shipOrderId = null;

    public string $shipStatus = 'processing';

    public string $shipTracking = '';

    /* ── cancel modal (this shop's slice) ── */
    public ?int $cancelOrderId = null;

    public string $cancelReason = '';

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

    /* ── detail modal ──────────────────────────────────────────────── */

    public function openDetail(int $id): void
    {
        $order = Order::query()->find($id);

        if ($order && $order->hasShopItems($this->shop()->id)) {
            $this->detailId = $id;
        }
    }

    public function closeDetail(): void
    {
        $this->detailId = null;
    }

    public function getDetailProperty(): ?Order
    {
        if ($this->detailId === null) {
            return null;
        }

        return Order::query()
            ->with(['customer:id,full_name,phone', 'items.product.mainImage', 'shopShipments.shop:id,name'])
            ->find($this->detailId);
    }

    /* ── shipment flow (this shop's slice) ─────────────────────────── */

    public function openShipment(int $id): void
    {
        $order = Order::query()->find($id);

        if (! $order || ! $order->hasShopItems($this->shop()->id)) {
            return;
        }

        $slice = $order->shipmentForShop($this->shop()->id);

        if ($slice->isCanceled()) {
            $this->dispatch('panel-toast', message: 'سهم فروشگاه شما از این سفارش لغو شده است.', type: 'error');

            return;
        }

        $this->shipOrderId  = $order->id;
        $this->shipStatus   = $slice->shipment_status !== 'none' ? $slice->shipment_status : 'processing';
        $this->shipTracking = (string) ($slice->tracking_code ?? '');
    }

    public function closeShipment(): void
    {
        $this->shipOrderId  = null;
        $this->shipStatus   = 'processing';
        $this->shipTracking = '';
    }

    /** Persist the shipment transition of THIS shop's slice (tracking mandatory on «در حال ارسال»). */
    public function saveShipment(): void
    {
        if ($this->shipOrderId === null) {
            return;
        }

        $order = Order::query()->find($this->shipOrderId);

        if (! $order || ! $order->hasShopItems($this->shop()->id)) {
            $this->dispatch('panel-toast', message: 'سفارش یافت نشد یا به فروشگاه شما تعلق ندارد.', type: 'error');
            $this->closeShipment();

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

        if ($this->shipStatus !== 'shipped') {
            // keep the previously stored code when stepping back/forward
            $slice = $order->shipmentForShop($this->shop()->id);
            $this->shipTracking = (string) ($slice->tracking_code ?? '');
        }

        if (! $order->setShopShipment($this->shop()->id, $this->shipStatus, $this->shipTracking)) {
            $this->dispatch('panel-toast', message: 'ثبت وضعیت ارسال فقط برای سفارش‌های پرداخت‌شده امکان‌پذیر است.', type: 'error');

            return;
        }

        $slice = $order->shipmentForShop($this->shop()->id);

        ActivityLogger::log(
            'order.shipment',
            'وضعیت ارسال سهم فروشگاه از سفارش '.Fmt::fa($order->id).' به «'.$slice->statusLabel().'» تغییر کرد'
                .($slice->tracking_code ? ' — کد رهگیری: '.$slice->tracking_code : ''),
            subject: $order,
            after: ['shipment_status' => $slice->shipment_status, 'tracking_code' => (string) $slice->tracking_code],
        );

        // notify the admin panel too (new seller event)
        AdminNotifier::notify(
            'order.shipment',
            'به‌روزرسانی ارسال سفارش '.Fmt::fa($order->id),
            'وضعیت سهم فروشگاه: '.$slice->statusLabel()
                .($slice->tracking_code ? ' — کد رهگیری: '.$slice->tracking_code : ''),
            route('admin.orders.show', $order->id),
            'truck',
            $order,
        );

        $this->closeShipment();

        session()->flash('seller-orders-flash', 'وضعیت ارسال با موفقیت ثبت شد.');
    }

    /* ── cancel flow (this shop's slice) ───────────────────────────── */

    public function openCancel(int $id): void
    {
        $order = Order::query()->find($id);

        if (! $order || $order->status !== 'paid' || ! $order->hasShopItems($this->shop()->id)) {
            return;
        }

        $slice = $order->shipmentForShop($this->shop()->id);

        if ($slice->isCanceled() || $slice->shipment_status === 'delivered') {
            $this->dispatch('panel-toast', message: 'این سهم از سفارش قابل لغو نیست.', type: 'error');

            return;
        }

        $this->cancelOrderId = $order->id;
        $this->cancelReason  = '';
    }

    public function closeCancel(): void
    {
        $this->cancelOrderId = null;
        $this->cancelReason  = '';
    }

    /** Cancel THIS shop's slice of the order with a mandatory reason. */
    public function confirmCancel(): void
    {
        if ($this->cancelOrderId === null) {
            return;
        }

        $this->validateWithToast(fn () => $this->validate([
            'cancelReason' => ['required', 'string', 'min:3', 'max:300'],
        ], [
            'cancelReason.required' => 'دلیل لغو سفارش را بنویسید.',
            'cancelReason.min'      => 'دلیل لغو باید حداقل ۳ کاراکتر باشد.',
            'cancelReason.max'      => 'دلیل لغو حداکثر ۳۰۰ کاراکتر است.',
        ]), 'لغو سفارش ناموفق بود');

        $order = Order::query()->find($this->cancelOrderId);

        if (! $order || ! $order->cancelShopSlice($this->shop()->id, $this->cancelReason, 'seller')) {
            $this->dispatch('panel-toast', message: 'لغو این سهم از سفارش امکان‌پذیر نیست.', type: 'error');
            $this->closeCancel();

            return;
        }

        ActivityLogger::log(
            'order.cancel',
            'فروشنده سهم فروشگاه خود از سفارش '.Fmt::fa($order->id).' را لغو کرد — دلیل: '.$this->cancelReason,
            subject: $order,
            after: ['canceled_by' => 'seller', 'cancel_reason' => $this->cancelReason],
        );

        $this->closeCancel();

        session()->flash('seller-orders-flash', 'سهم فروشگاه شما از سفارش لغو شد و مبلغ به مشتری بازگردانده می‌شود.');
    }

    /* ── Computed data ──────────────────────────────────────────────── */

    public function getOrdersProperty(): Collection
    {
        $shopId = $this->shop()->id;

        $query = Order::query()
            ->whereHas('items', fn ($q) => $q->where('shop_id', $shopId))
            ->when($this->statusFilter !== 'all', fn ($q) => $q->where('status', $this->statusFilter))
            ->with([
                'customer:id,full_name,phone',
                'items' => fn ($q) => $q->with('product.mainImage'),
                'shopShipments' => fn ($q) => $q->where('shop_id', $shopId),
            ])
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

    /** This shop's shipment slice of the order (created on demand). */
    public function shopSlice(Order $order): OrderShipment
    {
        return $order->shopShipments->firstWhere('shop_id', $this->shop()->id)
            ?? $order->shipmentForShop($this->shop()->id);
    }

    /** The seller's own slice total (items + own shipping − discount share). */
    public function shopTotal(Order $order): int
    {
        return $this->shopSlice($order)->total;
    }

    public function render()
    {
        return view('livewire.seller.orders-page', [
            'shop'    => $this->shop(),
            'orders'  => $this->orders,
            'hasMore' => $this->hasMore,
        ])->title('سفارش‌های فروشگاه');
    }
}
