<?php

namespace App\Livewire\Customer;

use App\Models\Customer;
use App\Models\Order;
use App\Support\Payments\PaymentService;
use App\Support\Phone;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.customer-slot')]
class CheckoutPage extends Component
{
    public string $receiver_name = '';

    public string $delivery_phone = '';

    public string $delivery_address = '';

    public string $postal_code = '';

    public function mount(): void
    {
        $customer = $this->customer();

        $this->receiver_name    = (string) ($customer?->full_name ?? '');
        $this->delivery_phone   = Phone::toLocal($customer?->phone);
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

    /** Validate delivery info → snapshot cart → hand over to the gateway. */
    public function placeOrder()
    {
        $this->validate();

        $customer = $this->customer();

        if (! $customer) {
            return $this->redirect(route('shop.login'), navigate: false);
        }

        // Delivery phone accepts any common Iranian mobile shape.
        $normalized = Phone::normalize($this->delivery_phone);

        if (! Phone::isValid($normalized)) {
            $this->addError('delivery_phone', 'شماره موبایل معتبر نیست (مثلاً ۰۹۱۲۳۴۵۶۷۸۹).');

            return;
        }

        $order = Order::createFromCart($customer, [
            'receiver_name'    => trim($this->receiver_name),
            'delivery_phone'   => (string) $normalized,
            'delivery_address' => trim($this->delivery_address),
            'postal_code'      => $this->postal_code !== '' ? $this->postal_code : null,
        ], PaymentService::activeGateway());

        if (! $order) {
            $this->addError('cart', 'سبد خرید شما خالی است یا هیچ کالای قابل خریدی ندارد.');

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

    public function getTotalProperty(): int
    {
        return (int) $this->validItems->sum(fn ($item) => $item->lineTotal());
    }

    public function getUnitsProperty(): int
    {
        return (int) $this->validItems->sum('quantity');
    }

    public function render()
    {
        return view('livewire.customer.checkout-page', [
            'items'      => $this->items,
            'validItems' => $this->validItems,
            'total'      => $this->total,
            'units'      => $this->units,
        ])->title('تکمیل خرید');
    }

    // ── Helpers ──────────────────────────────────────────────────────

    private function customer(): ?Customer
    {
        return auth('customer')->user();
    }
}
