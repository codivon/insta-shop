<?php

namespace App\Livewire\Customer;

use App\Livewire\Concerns\NotifiesOnValidation;
use App\Models\Carrier;
use App\Models\City;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\Discount;
use App\Models\Order;
use App\Models\Product;
use App\Models\Province;
use App\Models\Shop;
use App\Support\ActivityLogger;
use App\Support\AdminNotifier;
use App\Support\Checkout\DiscountEvaluator;
use App\Support\Fmt;
use App\Support\IranCalendar;
use App\Support\Payments\PaymentService;
use App\Support\Phone;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
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

    /* ── آدرس‌های من (73) ── */

    public ?int $selectedAddressId = null;

    /** لیست آدرس‌ها — مودال تمام‌صفحه روی موبایل (کارت ← لیست ← افزودن/ویرایش). */
    public bool $showAddressModal = false;

    /** مودال افزودن/ویرایش آدرس — همان فرم «آدرس‌های من». */
    public bool $showAddressForm = false;

    /** When set, the form modal edits this address instead of creating. */
    public ?int $addressEditId = null;

    public string $newTitle = '';

    public string $newRecipient = '';

    public string $newPhone = '';

    public int $newProvinceId = 0;

    public int $newCityId = 0;

    public string $newAddress = '';

    public string $newPostal = '';

    public string $newPlaque = '';

    public string $newUnit = '';

    public bool $newIsDefault = false;

    /** shop_id → carrier_id — حامل هر فروشگاه، برای فروشگاه‌های فیزیکی اجباری است. */
    public array $carrierPicks = [];

    /** shop_id → Y-m-d — روز تحویل انتخابی مشتری (حامل‌های «انتخاب توسط کاربر»). */
    public array $deliveryPicks = [];

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

        $this->gateway = PaymentService::activeGateway();

        // Preselect the DEFAULT address (or the only one) and fill the
        // delivery fields from it — batch 73.
        $default = $this->addresses->firstWhere('is_default', true) ?? $this->addresses->first();

        if ($default) {
            $this->selectAddress((int) $default->id);
        } else {
            $this->receiver_name = (string) ($customer?->full_name ?? '');
            $this->delivery_phone = Phone::toLocal($customer?->phone);
        }

        // preselect the FIRST usable carrier of every physical shop
        foreach ($this->shopGroups as $group) {
            $this->autoPickCarrier((int) $group['shop']->id, $group['carriers']);
        }
    }

    /* ───────────────────── address selection (73) ───────────────────── */

    public function getAddressesProperty(): Collection
    {
        $customerId = (int) auth('customer')->id();

        return $customerId > 0
            ? CustomerAddress::query()->forCustomer($customerId)->get()
            : collect();
    }

    public function getSelectedAddressProperty(): ?CustomerAddress
    {
        return $this->selectedAddressId
            ? $this->addresses->firstWhere('id', $this->selectedAddressId)
            : null;
    }

    /** Open the address-book modal (لیست آدرس‌ها — full-screen on phones). */
    public function openAddressModal(): void
    {
        $this->showAddressForm = false;
        $this->addressEditId = null;
        $this->resetErrorBag();
        $this->showAddressModal = true;
    }

    public function closeAddressModal(): void
    {
        $this->showAddressModal = false;
        $this->showAddressForm = false;
        $this->addressEditId = null;
    }

    /** Open the «افزودن آدرس» modal — the same form as the address book. */
    public function openAddAddress(): void
    {
        $customer = $this->customer();

        $this->resetNewAddressForm();
        $this->addressEditId = null;

        // prefill from the profile — saves typing on the first address
        $this->newRecipient = (string) ($customer?->full_name ?? '');
        $this->newPhone = Phone::toLocal($customer?->phone);
        $this->newIsDefault = $this->addresses->isEmpty();

        $this->resetErrorBag();

        $this->showAddressModal = false;
        $this->showAddressForm = true;
    }

    /** Open the «ویرایش آدرس» modal for one saved address. */
    public function openEditAddress(int $id): void
    {
        $address = $this->addresses->firstWhere('id', $id);

        if (! $address) {
            return;
        }

        $this->resetNewAddressForm();
        $this->resetErrorBag();

        $this->addressEditId = $address->id;
        $this->newTitle = (string) ($address->title ?? '');
        $this->newRecipient = (string) ($address->recipient_name ?? '');
        $this->newPhone = Phone::toLocal($address->phone);
        $this->newProvinceId = (int) ($address->province_id ?? 0);
        $this->newCityId = (int) ($address->city_id ?? 0);
        $this->newAddress = (string) $address->address;
        $this->newPostal = (string) ($address->postal_code ?? '');
        $this->newPlaque = (string) ($address->plaque ?? '');
        $this->newUnit = (string) ($address->unit ?? '');
        $this->newIsDefault = (bool) $address->is_default;

        $this->showAddressModal = false;
        $this->showAddressForm = true;
    }

    /** Back from the form modal to the address-book list. */
    public function backToAddressList(): void
    {
        $this->showAddressForm = false;
        $this->addressEditId = null;
        $this->showAddressModal = true;
    }

    private function resetNewAddressForm(): void
    {
        $this->addressEditId = null;
        $this->newTitle = '';
        $this->newRecipient = '';
        $this->newPhone = '';
        $this->newProvinceId = 0;
        $this->newCityId = 0;
        $this->newAddress = '';
        $this->newPostal = '';
        $this->newPlaque = '';
        $this->newUnit = '';
        $this->newIsDefault = false;
    }

    public function updatedNewProvinceId(): void
    {
        $this->newCityId = 0;
    }

    public function updatedNewPhone($value): void
    {
        $this->newPhone = Fmt::toLatin((string) $value);
    }

    public function updatedNewPostal($value): void
    {
        $this->newPostal = Fmt::toLatin((string) $value);
    }

    public function updatedNewPlaque($value): void
    {
        $this->newPlaque = Fmt::toLatin((string) $value);
    }

    public function updatedNewUnit($value): void
    {
        $this->newUnit = Fmt::toLatin((string) $value);
    }

    /** Pick an existing address from the modal — fills the delivery fields. */
    public function pickAddress(int $id): void
    {
        $this->selectAddress($id);
        $this->closeAddressModal();

        $this->dispatch('panel-toast', message: 'آدرس تحویل انتخاب شد. ✅', type: 'success');
    }

    private function selectAddress(int $id, bool $syncFields = true): void
    {
        $address = $this->addresses->firstWhere('id', $id);

        if (! $address) {
            return;
        }

        $this->selectedAddressId = $address->id;

        if ($syncFields) {
            $this->fillDeliveryFromAddress($address);
        }

        // the carrier zone may flip (درون/بیرون استانی) → re-pick carriers
        foreach ($this->shopGroups as $group) {
            $this->autoPickCarrier((int) $group['shop']->id, $group['carriers']);
        }

        // the browser re-syncs the delivery-day section from the freshly
        // rendered carrier picks (the zone flip may auto-pick another carrier)
        $this->dispatch('address-picked');
    }

    private function fillDeliveryFromAddress(CustomerAddress $address): void
    {
        $this->receiver_name = (string) ($address->recipient_name ?: $this->customer()?->full_name ?? '');
        $this->delivery_phone = $address->phone ? Phone::toLocal($address->phone) : Phone::toLocal($this->customer()?->phone);
        $this->delivery_address = $address->fullLabel();
        $this->postal_code = (string) ($address->postal_code ?? '');
    }

    /** Save the add/edit form — same semantics as the address book page. */
    public function saveAddressForm(): void
    {
        $this->validateWithToast(fn () => $this->validate($this->newAddressRules(), $this->newAddressMessages()));

        $customer = $this->customer();

        if (! $customer) {
            return;
        }

        $province = $this->newProvinceId > 0 ? Province::find($this->newProvinceId) : null;
        $city = $this->newCityId > 0 ? City::find($this->newCityId) : null;

        $address = $this->addressEditId
            ? CustomerAddress::query()->where('customer_id', $customer->id)->find($this->addressEditId)
            : new CustomerAddress(['customer_id' => $customer->id]);

        if (! $address) {
            return;
        }

        $wasEditing = $this->addressEditId !== null;

        $address->fill([
            'title' => trim($this->newTitle) !== '' ? trim($this->newTitle) : null,
            'recipient_name' => trim($this->newRecipient) !== '' ? trim($this->newRecipient) : null,
            'phone' => $this->newPhone !== '' ? Phone::normalize($this->newPhone) : null,
            'province_id' => $province?->id,
            'city_id' => $city?->id,
            'province_name' => $province?->name,
            'city_name' => $city?->name,
            'address' => trim($this->newAddress),
            'postal_code' => $this->newPostal !== '' ? $this->newPostal : null,
            'plaque' => trim($this->newPlaque) !== '' ? trim($this->newPlaque) : null,
            'unit' => trim($this->newUnit) !== '' ? trim($this->newUnit) : null,
            'is_default' => $this->newIsDefault,
        ]);

        $address->save();

        // default promotion — exactly one row is default by definition
        $book = CustomerAddress::query()->where('customer_id', $customer->id);

        if ($this->newIsDefault) {
            (clone $book)->whereKeyNot($address->id)->update(['is_default' => false]);
        } elseif ((clone $book)->count() <= 1) {
            (clone $book)->update(['is_default' => true]);
        } elseif (! (clone $book)->where('is_default', true)->exists()) {
            $book->orderByDesc('id')->first()?->forceFill(['is_default' => true])->save();
        }

        $this->resetNewAddressForm();

        // (re)select: a fresh address is always picked; an edited one only
        // when it is the current selection — delivery fields stay in sync.
        $reselect = ! $wasEditing || (int) $this->selectedAddressId === (int) $address->id;

        if ($reselect) {
            $this->selectAddress($address->id);
        }

        if ($wasEditing) {
            $this->backToAddressList();

            $this->dispatch('panel-toast', message: 'آدرس به‌روزرسانی شد. ✅', type: 'success');
        } else {
            // new address → selected already → back to the checkout with the card
            $this->closeAddressModal();

            $this->dispatch('panel-toast', message: 'آدرس جدید ذخیره و انتخاب شد. ✅', type: 'success');
        }
    }

    protected function rules(): array
    {
        return [
            'receiver_name' => ['required', 'string', 'min:3', 'max:80'],
            'delivery_phone' => ['required', 'string'],
            'delivery_address' => ['required', 'string', 'min:10', 'max:500'],
            'postal_code' => ['nullable', 'string', 'regex:/^\d{10}$/'],
        ];
    }

    protected function messages(): array
    {
        return [
            'receiver_name.required' => 'نام گیرنده را وارد کنید.',
            'receiver_name.min' => 'نام گیرنده باید حداقل ۳ حرف باشد.',
            'delivery_phone.required' => 'شماره تماس گیرنده را وارد کنید.',
            'delivery_address.required' => 'نشانی کامل تحویل را وارد کنید.',
            'delivery_address.min' => 'نشانی باید حداقل ۱۰ حرف باشد.',
            'postal_code.regex' => 'کد پستی باید ۱۰ رقم باشد.',
        ];
    }

    private function newAddressRules(): array
    {
        return [
            'newTitle' => ['nullable', 'string', 'max:60'],
            'newRecipient' => ['required', 'string', 'min:3', 'max:80'],
            'newPhone' => ['required', 'string', 'min:10', 'max:14'],
            'newProvinceId' => ['required', 'integer', 'min:1', 'exists:provinces,id'],
            'newCityId' => ['required', 'integer', 'min:1', Rule::exists('cities', 'id')->where('province_id', $this->newProvinceId)],
            'newAddress' => ['required', 'string', 'min:10', 'max:500'],
            'newPostal' => ['nullable', 'string', 'regex:/^\d{10}$/'],
            'newPlaque' => ['nullable', 'string', 'max:20'],
            'newUnit' => ['nullable', 'string', 'max:20'],
            'newIsDefault' => ['boolean'],
        ];
    }

    private function newAddressMessages(): array
    {
        return [
            'newRecipient.required' => 'نام گیرنده را وارد کنید.',
            'newRecipient.min' => 'نام گیرنده باید حداقل ۳ حرف باشد.',
            'newPhone.required' => 'شماره همراه را وارد کنید.',
            'newPhone.min' => 'شماره همراه معتبر نیست.',
            'newProvinceId.required' => 'استان را انتخاب کنید.',
            'newProvinceId.min' => 'استان را انتخاب کنید.',
            'newCityId.required' => 'شهر را انتخاب کنید.',
            'newCityId.min' => 'شهر را انتخاب کنید.',
            'newCityId.exists' => 'شهر انتخابی با استان هم‌خوانی ندارد.',
            'newAddress.required' => 'نشانی کامل را وارد کنید.',
            'newAddress.min' => 'نشانی باید حداقل ۱۰ حرف باشد.',
            'newPostal.regex' => 'کد پستی باید ۱۰ رقم باشد.',
        ];
    }

    /* ───────────────────── carrier zone (74) ───────────────────── */

    /** درون استانی when the delivery address shares the carrier's origin province. */
    public function isInProvince(Carrier $carrier): bool
    {
        $address = $this->selectedAddress;

        return $address
            && $address->province_id
            && $carrier->origin_province_id
            && (int) $carrier->origin_province_id === (int) $address->province_id;
    }

    private function autoPickCarrier(int $shopId, Collection $carriers): void
    {
        $current = (int) ($this->carrierPicks[$shopId] ?? 0);

        // keep the current pick when it still works for this zone
        if ($current > 0 && $carriers->contains('id', $current)) {
            return;
        }

        $first = $carriers->first();

        if ($first) {
            $this->carrierPicks[$shopId] = $first->id;
        }
    }

    /* ───────────── انتخاب روز تحویل (حامل‌های user_select) ───────────── */

    /**
     * The selectable delivery days of a shop's PICKED carrier — only
     * carriers of type «انتخاب توسط کاربر» return a non-empty list.
     */
    public function deliveryDaysFor(int $shopId): array
    {
        $group = $this->shopGroups->first(fn ($g) => (int) $g['shop']->id === $shopId);

        $carrier = $group
            ? $group['carriers']->firstWhere('id', (int) ($this->carrierPicks[$shopId] ?? 0))
            : null;

        return $carrier?->selectableDeliveryDays() ?? [];
    }

    /**
     * Switching the carrier invalidates a no-longer-valid delivery day.
     *
     * The browser already drops an obsolete pick the moment the carrier
     * changes (zero-roundtrip checkout). This hook is the server-side
     * safety net — and it MUST be order-safe: when carrier + day updates
     * ride the SAME deferred commit (e.g. a quick 1→2→1 switch then a day
     * pick), Livewire can run this hook AFTER the day-pick update has been
     * applied. So never unset blindly: only drop the pick when it is
     * genuinely invalid for the NEW carrier.
     */
    public function updatedCarrierPicks($value, string $shopId): void
    {
        $shopId = (int) $shopId;

        $group = $this->shopGroups->first(fn ($g) => (int) $g['shop']->id === $shopId);

        $carrier = $group
            ? $group['carriers']->firstWhere('id', (int) $value)
            : null;

        // a non-user_select carrier has no day picker at all → drop the pick
        if (! $carrier || $carrier->delivery_time_type !== Carrier::TIME_USER_SELECT) {
            unset($this->deliveryPicks[$shopId]);

            return;
        }

        // user_select carrier: keep the pick when it is still inside the
        // new carrier's selectable days (IranCalendar filters Fridays…)
        if (! IranCalendar::isAllowedPick($carrier->selectableDeliveryDays(), (string) ($this->deliveryPicks[$shopId] ?? ''))) {
            unset($this->deliveryPicks[$shopId]);
        }
    }

    /* ───────────────────── interactions ───────────────────── */

    /** «اعمال» click — validates the code against the CURRENT cart groups. */
    public function applyDiscount(): void
    {
        $this->discountApplied = false;
        $this->discountAmount = 0;
        $this->discountMessage = '';

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
        $this->discountAmount = $amount;
        $this->discountMessage = $message;

        if ($ok) {
            $this->dispatch('panel-toast', message: $message.' ✅', type: 'success');
            $this->resetErrorBag('discountCode');

            // the payable total is owned by the browser (zero-roundtrip
            // checkout) — hand the fresh amount over so it re-renders live
            $this->dispatch('discount-updated', amount: $amount);
        } else {
            $this->addError('discountCode', $message);
        }
    }

    /** «حذف کد» — clears the applied discount. */
    public function removeDiscount(): void
    {
        $this->discountCode = '';
        $this->discountApplied = false;
        $this->discountAmount = 0;
        $this->discountMessage = '';
        $this->resetErrorBag('discountCode');

        $this->dispatch('discount-updated', amount: 0);
    }

    /** Validate delivery info → snapshot cart (+ carriers/discount/gateway) → gateway. */
    public function placeOrder()
    {
        $customer = $this->customer();

        if (! $customer) {
            return $this->redirect(route('shop.login'), navigate: false);
        }

        // The address book is the source of truth — a customer WITH saved
        // addresses must have one selected (the manual form is only for
        // first-time customers without any address).
        if ($this->addresses->isNotEmpty() && ! $this->selectedAddress) {
            $this->addError('selectedAddressId', 'آدرس تحویل را انتخاب کنید.');
            $this->dispatch('panel-toast', message: 'آدرس تحویل را انتخاب کنید.');

            return;
        }

        try {
            $this->validate();
        } catch (ValidationException $exception) {
            $this->dispatch('panel-toast', message: collect($exception->errors())->flatten()->filter()->first()
                ?: 'لطفاً اطلاعات تحویل را کامل کنید.');

            throw $exception;
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

        /* ── روز تحویل حامل‌های «انتخاب توسط کاربر» اجباری است ── */
        foreach ($this->shopGroups as $group) {
            if (! $group['needsCarrier']) {
                continue;
            }

            $shopId = (int) $group['shop']->id;
            $carrier = $group['carriers']->firstWhere('id', (int) ($this->carrierPicks[$shopId] ?? 0));

            if (! $carrier || $carrier->delivery_time_type !== Carrier::TIME_USER_SELECT) {
                continue;
            }

            $picked = (string) ($this->deliveryPicks[$shopId] ?? '');

            if (! IranCalendar::isAllowedPick($carrier->selectableDeliveryDays(), $picked)) {
                $this->addError('deliveryPicks.'.$shopId, 'روز تحویل سفارش از فروشگاه «'.$group['shop']->name.'» را انتخاب کنید.');
                $this->dispatch('panel-toast', message: 'روز تحویل سفارش از فروشگاه «'.$group['shop']->name.'» را انتخاب کنید.');

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

        /* ── shipments snapshot (tariff-aware, batch 74) ── */
        $shipments = [];
        $shippingCost = 0;

        foreach ($this->shopGroups as $group) {
            if (! $group['needsCarrier']) {
                continue;
            }

            $pick = (int) ($this->carrierPicks[(int) $group['shop']->id] ?? 0);
            $carrier = $group['carriers']->firstWhere('id', $pick);

            if (! $carrier) {
                continue;
            }

            $cost = $carrier->costFor((int) $group['subtotal'], (int) $group['weight'], $this->isInProvince($carrier));

            if (! $carrier->isPayOnDelivery()) {
                $shippingCost += $cost;
            }

            $shipments[] = [
                'shop_id' => (int) $group['shop']->id,
                'carrier_id' => $carrier->id,
                'carrier_title' => $carrier->title,
                'cost' => $cost,
                'delivery_date' => $carrier->delivery_time_type === Carrier::TIME_USER_SELECT
                    ? (string) ($this->deliveryPicks[(int) $group['shop']->id] ?? '')
                    : null,
            ];
        }

        $order = Order::createFromCart($customer, [
            'receiver_name' => trim($this->receiver_name),
            'delivery_phone' => (string) $normalized,
            'delivery_address' => trim($this->delivery_address),
            'postal_code' => $this->postal_code !== '' ? $this->postal_code : null,
        ], $this->gateway, [
            'shipments' => $shipments,
            'shipping_cost' => $shippingCost,
            'discount' => $discount,
            'discount_amount' => $discountAmount,
        ]);

        if (! $order) {
            $this->addError('cart', 'سبد خرید شما خالی است یا هیچ کالای قابل خریدی ندارد.');
            $this->dispatch('panel-toast', message: 'سبد خرید شما خالی است یا هیچ کالای قابل خریدی ندارد.');

            return;
        }

        ActivityLogger::log(
            'order.created',
            'سفارش جدید '.Fmt::fa($order->id).' ثبت شد — مبلغ '.Fmt::money($order->total_amount),
            subject: $order,
            after: ['status' => 'pending', 'gateway' => $this->gateway],
        );

        AdminNotifier::notify(
            'order.new',
            'سفارش جدید 🛍️ '.Fmt::fa($order->id),
            'مبلغ '.Fmt::money($order->total_amount).' — در انتظار پرداخت',
            route('admin.orders.show', $order->id),
            'bag',
            $order,
        );

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
     * Carriers WITHOUT any weight tariff are excluded («بدون تعرفه کار
     * نمی‌کنه» — batch 74); each kept carrier carries a zone_ok flag for
     * the current delivery province.
     *
     * @return Collection<int, array{Shop, items: Collection, subtotal: int, weight: int, carriers: Collection, allCarriers: Collection, needsCarrier: bool}>
     */
    public function getShopGroupsProperty(): Collection
    {
        return $this->validItems
            ->groupBy(fn ($item) => (int) ($item->product->shop_id ?? 0))
            ->map(function (Collection $lines) {
                $shop = $lines->first()->product->shop;
                $physical = $lines->contains(fn ($item) => $item->product->type !== Product::TYPE_DOWNLOADABLE);

                $allCarriers = $physical && $shop
                    ? Carrier::query()->where('shop_id', $shop->id)->active()->with('tariffs')->orderBy('sort')->orderBy('id')->get()
                    : collect();

                $carriers = $allCarriers->filter(
                    fn (Carrier $carrier) => $carrier->isPayOnDelivery() || $carrier->hasTariffs(),
                )->values();

                return [
                    'shop' => $shop,
                    'items' => $lines->values(),
                    'subtotal' => (int) $lines->sum(fn ($item) => $item->lineTotal()),
                    'weight' => (int) $lines->sum(fn ($item) => (int) ($item->product->weight_grams ?? 0) * (int) $item->quantity),
                    'carriers' => $carriers,
                    'allCarriers' => $allCarriers,
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

            $pick = (int) ($this->carrierPicks[(int) $group['shop']->id] ?? 0);
            $carrier = $group['carriers']->firstWhere('id', $pick);

            if ($carrier && ! $carrier->isPayOnDelivery()) {
                $sum += $carrier->costFor((int) $group['subtotal'], (int) $group['weight'], $this->isInProvince($carrier));
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

            $pick = (int) ($this->carrierPicks[(int) $group['shop']->id] ?? 0);
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

    /**
     * The shop whose products the applied code discounts — seller codes are
     * scoped to their OWN shop only, so the checkout labels it explicitly
     * (the discount never touches the other shops' products).
     */
    public function getDiscountShopProperty(): ?Shop
    {
        if (! $this->discountApplied) {
            return null;
        }

        $discount = Discount::findActive(trim($this->discountCode));

        return $discount?->shop_id
            ? Shop::query()->find($discount->shop_id)
            : null;
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

    /** Provinces/cities of the inline «افزودن آدرس» form. */
    public function getNewProvincesProperty(): Collection
    {
        return Province::query()->orderBy('ordering')->orderBy('id')->get(['id', 'name']);
    }

    public function getNewCitiesProperty(): Collection
    {
        return $this->newProvinceId > 0
            ? City::query()->where('province_id', $this->newProvinceId)->orderBy('ordering')->orderBy('id')->get(['id', 'name'])
            : collect();
    }

    public function render()
    {
        return view('livewire.customer.checkout-page', [
            'items' => $this->items,
            'validItems' => $this->validItems,
            'shopGroups' => $this->shopGroups,
            'total' => $this->total,
            'units' => $this->units,
            'shippingCost' => $this->shippingCost,
            'hasPayOnDelivery' => $this->hasPayOnDelivery,
            'payable' => $this->payable,
            'liveDiscount' => $this->liveDiscount,
            'gateways' => $this->gateways,
            'addresses' => $this->addresses,
            'selectedAddress' => $this->selectedAddress,
            'newProvinces' => $this->newProvinces,
            'newCities' => $this->newCities,
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

        // A seller's code is evaluated against ONLY that seller's cart lines —
        // when the shop has no items (or the include/exclude scope filters
        // everything out) the discount is worth nothing and must not ride
        // onto the order.
        $groupLines = $discount
            ? ($this->shopGroups->first(fn ($g) => (int) $g['shop']->id === (int) $discount->shop_id)['items'] ?? collect())
            : collect();

        [$ok, $amount] = DiscountEvaluator::evaluate($discount, $this->customer(), $groupLines);

        return $ok ? [$discount, $amount] : [null, 0];
    }
}
