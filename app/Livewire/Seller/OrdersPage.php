<?php

namespace App\Livewire\Seller;

use App\Livewire\Concerns\NotifiesOnValidation;
use App\Models\Order;
use App\Models\Shop;
use App\Support\ActivityLogger;
use App\Support\AdminNotifier;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Component;

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

    /* ── shipment modal ── */
    public ?int $shipOrderId = null;

    public string $shipStatus = 'processing';

    public string $shipTracking = '';

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
            ->with(['customer:id,full_name,phone', 'items.product.mainImage'])
            ->find($this->detailId);
    }

    /* ── shipment flow ─────────────────────────────────────────────── */

    public function openShipment(int $id): void
    {
        $order = Order::query()->find($id);

        if (! $order || ! $order->hasShopItems($this->shop()->id)) {
            return;
        }

        $this->shipOrderId  = $order->id;
        $this->shipStatus   = $order->shipment_status !== 'none' ? $order->shipment_status : 'processing';
        $this->shipTracking = (string) ($order->tracking_code ?? '');
    }

    public function closeShipment(): void
    {
        $this->shipOrderId  = null;
        $this->shipStatus   = 'processing';
        $this->shipTracking = '';
    }

    /** Persist the shipment transition for a paid order (tracking mandatory on «در حال ارسال»). */
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
            'shipStatus'   => ['required', 'in:'.implode(',', Order::SHIPMENT_STATUSES)],
            'shipTracking' => [$this->shipStatus === 'shipped' ? 'required' : 'nullable', 'string', 'max:60'],
        ], [
            'shipStatus.required'   => 'وضعیت ارسال را انتخاب کنید.',
            'shipStatus.in'         => 'وضعیت ارسال نامعتبر است.',
            'shipTracking.required' => 'برای «در حال ارسال» وارد کردن کد رهگیری پستی الزامی است.',
            'shipTracking.max'      => 'کد رهگیری حداکثر ۶۰ کاراکتر است.',
        ]), 'ثبت وضعیت ارسال ناموفق بود');

        if ($this->shipStatus !== 'shipped') {
            // keep the previously stored code when stepping back/forward
            $this->shipTracking = (string) ($order->tracking_code ?? '');
        }

        if (! $order->setShipment($this->shipStatus, $this->shipTracking)) {
            $this->dispatch('panel-toast', message: 'ثبت وضعیت ارسال فقط برای سفارش‌های پرداخت‌شده امکان‌پذیر است.', type: 'error');

            return;
        }

        ActivityLogger::log(
            'order.shipment',
            'وضعیت ارسال سفارش '.\App\Support\Fmt::fa($order->id).' به «'.$order->shipmentStatusLabel().'» تغییر کرد'
                .($order->tracking_code ? ' — کد رهگیری: '.$order->tracking_code : ''),
            subject: $order,
            after: ['shipment_status' => $order->shipment_status, 'tracking_code' => (string) $order->tracking_code],
        );

        // notify the admin panel too (new seller event)
        AdminNotifier::notify(
            'order.shipment',
            'به‌روزرسانی ارسال سفارش '.\App\Support\Fmt::fa($order->id),
            'وضعیت: '.$order->shipmentStatusLabel()
                .($order->tracking_code ? ' — کد رهگیری: '.$order->tracking_code : ''),
            route('admin.orders.show', $order->id),
            'truck',
            $order,
        );

        $this->closeShipment();

        session()->flash('seller-orders-flash', 'وضعیت ارسال با موفقیت ثبت شد.');
    }

    /* ── Computed data ──────────────────────────────────────────────── */

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
            'shop'    => $this->shop(),
            'orders'  => $this->orders,
            'hasMore' => $this->hasMore,
        ])->title('سفارش‌های فروشگاه');
    }
}
