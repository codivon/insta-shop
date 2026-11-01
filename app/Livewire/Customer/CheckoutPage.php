<?php

namespace App\Livewire\Customer;

use App\Livewire\Concerns\NotifiesOnValidation;
use App\Models\Carrier;
use App\Models\Customer;
use App\Models\Discount;
use App\Models\Order;
use App\Support\Checkout\DiscountEvaluator;
use App\Support\Payments\PaymentService;
use App\Support\Phone;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.customer-slot')]
class CheckoutPage extends Component
{
    use NotifiesOnValidation;

    public string $receiver_name = '';

    public string $delivery_phone = '';

    public string $delivery_address = '';

    public string $postal_code = '';

    /** shop_id → carrier_id — حامل هر فروشگاه، برای فروشگاه‌های فیزیکی اجباری است. */
    public array $carrierPicks = [];

    public string $discountCode = '';

    /** Result of the last «اعمال کد تخفیف» click (drives the green/red box). */
    public bool $discountApplied = false;

    public int $discountAmount = 0;

    public string $discountMessage = '';

    /** shop_id → chosen payment gateway key (in-active gateway = default). */
    public string $gateway = '';

    public function mount(): void
    {
        $customer = $this->customer();

        $this->receiver_name  = (string) ($customer?->full_name ?? '');
        $this->delivery_phone = Phone::toLocal($customer?->phone);
        $this->gateway        = PaymentService::activeGateway();

        // preselect the FIRST active carrier of every physical shop
        foreach ($this->shopGroups as $group) {
            $first = $group['carriers']->first();

            if ($first) {
                $this->carrierPicks[(int) $group['shop']->id] = $first->id;
            }
        }
    }

    protected function rules(): array
    {
        return [
            'receiver_name'    => ['required', 'string', 'min:3', 'max:80'],
            'delivery_phone'   => ['required', 'string'],
            'delivery_address' => ['required', 'string', 'min:10', 'max:500'],
            'postal_code'      => ['nullable', 'string', 'regex:/^\d{10}$/'],
        ];
    }

    protected function messages(): array
    {
        return [
            'receiver_name.required'    => 'نام گیرنده را وارد کنید.',
            'receiver_name.min'         => 'نام گیرنده باید حداقل ۳ حرف باشد.',
            'delivery_phone.required'   => 'شماره تماس گیرنده را وارد کنید.',
            'delivery_address.required' => 'نشانی کامل تحویل را وارد کنید.',
            'delivery_address.min'      => 'نشانی باید حداقل ۱۰ حرف باشد.',
            'postal_code.regex'         => 'کد پستی باید ۱۰ رقم باشد.',
        ];
    }

    /* ───────────────────── interactions ───────────────────── */

    /** «اعمال» click — validates the code against the CURRENT cart groups. */
    public function applyDiscount(): void
    {
        $this->discountApplied  = false;
        $this->discountAmount   = 0;
        $this->discountMessage  = '';

        $code = trim($this->discountCode);

        if ($code === '') {
            $this->discountMessage = 'کد تخفیف را وارد کنید.';
            $this->addError('discountCode', $this->discountMessage);

            return;
        }

        $discount = Discount::findActive($code);

        $groupLines = $discount
            ? ($this->shopGroups->first(fn ($g) => (int) $g['shop']->id === (int) $discount->shop_id)['items'] ?? collect())
            : collect();

        [$ok, $amount, $message] = DiscountEvaluator::evaluate($discount, $this->customer(), $groupLines);

        $this->discountApplied = $ok;
        $this->discountAmount  = $amount;
        $this->discountMessage = $message;

        if ($ok) {
            $this->dispatch('panel-toast', message: $message.' ✅', type: 'success');
            $this->resetErrorBag('discountCode');
        } else {
            $this->addError('discountCode', $message);
        }
    }

    /** «حذف کد» — clears the applied discount. */
    public function removeDiscount(): void
    {
        $this->discountCode     = '';
        $this->discountApplied  = false;
        $this->discountAmount   = 0;
        $this->discountMessage  = '';
        $this->resetErrorBag('discountCode');
    }

    /** Validate delivery info → snapshot cart (+ carriers/discount/gateway) → gateway. */
    public function placeOrder()
    {
        try {
            $this->validate();
        } catch (\Illuminate\Validation\ValidationException $exception) {
            $this->dispatch('panel-toast', message: collect($exception->errors())->flatten()->filter()->first()
                ?: 'لطفاً اطلاعات تحویل را کامل کنید.');

            throw $exception;
        }

        $customer = $this->customer();

        if (! $customer) {
            return $this->redirect(route('shop.login'), navigate: false);
        }

        // Delivery phone accepts any common Iranian mobile shape.
        $normalized = Phone::normalize($this->delivery_phone);

        if (! Phone::isValid($normalized)) {
            $this->addError('delivery_phone', 'شماره موبایل معتبر نیست (مثلاً ۰۹۱۲۳۴۵۶۷۸۹).');
            $this->dispatch('panel-toast', message: 'شماره موبایل معتبر نیست (مثلاً ۰۹۱۲۳۴۵۶۷۸۹).');

            return;
        }

        /* ── حامل هر فروشگاه فیزیکی اجباری است ── */
        foreach ($this->shopGroups as $group) {
            if (! $group['needsCarrier']) {
                continue;
            }

            $pick = (int) ($this->carrierPicks[(int) $group['shop']->id] ?? 0);

            if ($pick < 1 || ! $group['carriers']->contains('id', $pick)) {
                $this->addError('carrierPicks.'.((int) $group['shop']->id), 'روش ارسال برای «'.$group['shop']->name.'» را انتخاب کنید.');
                $this->dispatch('panel-toast', message: 'روش ارسال برای فروشگاه «'.$group['shop']->name.'» انتخاب نشده است.');

                return;
            }
        }

        /* ── gateway ── */
        $available = PaymentService::availableGateways();

        if ($available !== [] && ! array_key_exists($this->gateway, $available)) {
            $this->gateway = array_key_first($available);
        }

        /* ── discount (fresh server-side evaluation) ── */
        [$discount, $discountAmount] = $this->resolveDiscount();

        /* ── shipments snapshot ── */
        $shipments    = [];
        $shippingCost = 0;

        foreach ($this->shopGroups as $group) {
            if (! $group['needsCarrier']) {
                continue;
            }

            $pick    = (int) ($this->carrierPicks[(int) $group['shop']->id] ?? 0);
            $carrier = $group['carriers']->firstWhere('id', $pick);

            if (! $carrier) {
                continue;
            }

            $cost = $carrier->costFor((int) $group['subtotal'], (int) $group['weight']);

            if (! $carrier->isPayOnDelivery()) {
                $shippingCost += $cost;
            }

            $shipments[] = [
                'shop_id'       => (int) $group['shop']->id,
                'carrier_id'    => $carrier->id,
                'carrier_title' => $carrier->title,
                'cost'          => $cost,
            ];
        }

        $order = Order::createFromCart($customer, [
            'receiver_name'    => trim($this->receiver_name),
            'delivery_phone'   => (string) $normalized,
            'delivery_address' => trim($this->delivery_address),
            'postal_code'      => $this->postal_code !== '' ? $this->postal_code : null,
        ], $this->gateway, [
            'shipments'       => $shipments,
            'shipping_cost'   => $shippingCost,
            'discount'        => $discount,
            'discount_amount' => $discountAmount,
        ]);

        if (! $order) {
            $this->addError('cart', 'سبد خرید شما خالی است یا هیچ کالای قابل خریدی ندارد.');
            $this->dispatch('panel-toast', message: 'سبد خرید شما خالی است یا هیچ کالای قابل خریدی ندارد.');

            return;
        }

        // Full page load (not wire:navigate) — the next response is the
        // gateway's redirection form, not a Livewire page.
        return $this->redirect(route('shop.payment.go', $order->id), navigate: false);
    }

    // ── Computed data ────────────────────────────────────────────────

    public function getItemsProperty(): Collection
    {
        $cart = $this->customer()?->cart;

        return $cart
            ? $cart->items()->with(['product.mainImage', 'product.shop:id,name,slug', 'variant.values.attribute', 'variant.values.value'])->get()
            : collect();
    }

    public function getValidItemsProperty(): Collection
    {
        return $this->items->filter(
            fn ($item) => $item->product
                && $item->product->status === 'published'
                && Order::lineStock($item) > 0,
        );
    }

    /**
     * Items grouped by store — each card shows its own thumbnail rows and
     * its own carrier picker (روش ارسال هر فروشگاه، هزینهٔ هر حامل جدا).
     *
     * @return Collection<int, array{shop, items: Collection, subtotal: int, weight: int, carriers: Collection, needsCarrier: bool}>
     */
    public function getShopGroupsProperty(): Collection
    {
        return $this->validItems
            ->groupBy(fn ($item) => (int) ($item->product->shop_id ?? 0))
            ->map(function (Collection $lines) {
                $shop     = $lines->first()->product->shop;
                $physical = $lines->contains(fn ($item) => $item->product->type !== \App\Models\Product::TYPE_DOWNLOADABLE);

                $carriers = $physical && $shop
                    ? Carrier::query()->where('shop_id', $shop->id)->active()->orderBy('sort')->orderBy('id')->get()
                    : collect();

                return [
                    'shop'         => $shop,
                    'items'        => $lines->values(),
                    'subtotal'     => (int) $lines->sum(fn ($item) => $item->lineTotal()),
                    'weight'       => (int) $lines->sum(fn ($item) => (int) ($item->product->weight_grams ?? 0) * (int) $item->quantity),
                    'carriers'     => $carriers,
                    'needsCarrier' => $physical,
                ];
            })
            ->values();
    }

    public function getTotalProperty(): int
    {
        return (int) $this->validItems->sum(fn ($item) => $item->lineTotal());
    }

    public function getUnitsProperty(): int
    {
        return (int) $this->validItems->sum('quantity');
    }

    /** مجموع هزینهٔ حامل‌های انتخاب‌شده (پس‌کرایه‌ها آنلاین حساب نمی‌شوند). */
    public function getShippingCostProperty(): int
    {
        $sum = 0;

        foreach ($this->shopGroups as $group) {
            if (! $group['needsCarrier']) {
                continue;
            }

            $pick    = (int) ($this->carrierPicks[(int) $group['shop']->id] ?? 0);
            $carrier = $group['carriers']->firstWhere('id', $pick);

            if ($carrier && ! $carrier->isPayOnDelivery()) {
                $sum += $carrier->costFor((int) $group['subtotal'], (int) $group['weight']);
            }
        }

        return $sum;
    }

    /** True when at least one picked carrier is پس‌کرایه (پرداخت در محل). */
    public function getHasPayOnDeliveryProperty(): bool
    {
        foreach ($this->shopGroups as $group) {
            if (! $group['needsCarrier']) {
                continue;
            }

            $pick    = (int) ($this->carrierPicks[(int) $group['shop']->id] ?? 0);
            $carrier = $group['carriers']->firstWhere('id', $pick);

            if ($carrier && $carrier->isPayOnDelivery()) {
                return true;
            }
        }

        return false;
    }

    /** Live discount amount — only when the applied state is still valid. */
    public function getLiveDiscountProperty(): array
    {
        if (! $this->discountApplied) {
            return [0, null];
        }

        [$discount, $amount] = $this->resolveDiscount();

        return [$amount, $discount];
    }

    /** Payable = items + online shipping − discount. */
    public function getPayableProperty(): int
    {
        [$discountAmount] = $this->liveDiscount;

        return max(0, $this->total + $this->shippingCost - $discountAmount);
    }

    /** Gateways actually usable in this installation (credentials present). */
    public function getGatewaysProperty(): array
    {
        return PaymentService::availableGateways();
    }

    public function render()
    {
        return view('livewire.customer.checkout-page', [
            'items'        => $this->items,
            'validItems'   => $this->validItems,
            'shopGroups'   => $this->shopGroups,
            'total'        => $this->total,
            'units'        => $this->units,
            'shippingCost' => $this->shippingCost,
            'hasPayOnDelivery' => $this->hasPayOnDelivery,
            'payable'      => $this->payable,
            'liveDiscount' => $this->liveDiscount,
            'gateways'     => $this->gateways,
        ])->title('تکمیل خرید');
    }

    // ── Helpers ──────────────────────────────────────────────────────

    private function customer(): ?Customer
    {
        return auth('customer')->user();
    }

    /**
     * Fresh evaluation of the typed code (used by placeOrder and the live
     * summary) — returns [?Discount, int amount].
     *
     * @return array{0: ?Discount, 1: int}
     */
    private function resolveDiscount(): array
    {
        $code = trim($this->discountCode);

        if ($code === '') {
            return [null, 0];
        }

        $discount = Discount::findActive($code);

        $groupLines = $discount
            ? ($this->shopGroups->first(fn ($g) => (int) $g['shop']->id === (int) $discount->shop_id)['items'] ?? collect())
            : collect();

        [, $amount] = DiscountEvaluator::evaluate($discount, $this->customer(), $groupLines);

        return [$discount, $amount];
    }
}
