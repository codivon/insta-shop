<?php

namespace App\Livewire\Customer;

use App\Livewire\Concerns\NotifiesOnValidation;
use App\Models\City;
use App\Models\CustomerAddress;
use App\Models\Province;
use App\Support\Fmt;
use App\Support\Phone;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * «آدرس‌های من» — the customer address book (batch 73).
 *
 * Fields per the spec: عنوان، گیرنده، شماره همراه، استان/شهر، نشانی،
 * کد پستی، پلاک، واحد. Exactly ONE default address («پیش‌فرض») —
 * when the book holds a single row it becomes default automatically.
 * Checkout opens the same modal flow via «تغییر آدرس».
 */
#[Layout('layouts.customer-slot')]
class AddressesPage extends Component
{
    use NotifiesOnValidation;

    /* ══════════ modal state ══════════ */

    public bool $showModal = false;

    public ?int $editId = null;

    /* ══════════ form ══════════ */

    public string $title = '';

    public string $recipient_name = '';

    public string $phone = '';

    public int $provinceId = 0;

    public int $cityId = 0;

    public string $address = '';

    public string $postal_code = '';

    public string $plaque = '';

    public string $unit = '';

    public bool $is_default = false;

    /** Delete confirmation holds the id of the address to remove. */
    public ?int $confirmDeleteId = null;

    public function mount(): void
    {
        if (! auth('customer')->check()) {
            $this->redirect(route('shop.login'), navigate: true);
        }
    }

    /* ══════════ cascades ══════════ */

    public function updatedProvinceId(): void
    {
        $this->cityId = 0;
    }

    /* ══════════ digit normalizers ══════════ */

    public function updatedPhone($value): void
    {
        $this->phone = Fmt::toLatin((string) $value);
    }

    public function updatedPostalCode($value): void
    {
        $this->postal_code = Fmt::toLatin((string) $value);
    }

    public function updatedPlaque($value): void
    {
        $this->plaque = Fmt::toLatin((string) $value);
    }

    public function updatedUnit($value): void
    {
        $this->unit = Fmt::toLatin((string) $value);
    }

    /* ══════════ modal lifecycle ══════════ */

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->resetErrorBag();
        $this->showModal = true;
    }

    public function openEditModal(int $id): void
    {
        $address = $this->findAddress($id);

        if (! $address) {
            return;
        }

        $this->resetForm();
        $this->resetErrorBag();

        $this->editId         = $address->id;
        $this->title          = (string) ($address->title ?? '');
        $this->recipient_name = (string) ($address->recipient_name ?? '');
        $this->phone          = Phone::toLocal($address->phone);
        $this->provinceId     = (int) ($address->province_id ?? 0);
        $this->cityId         = (int) ($address->city_id ?? 0);
        $this->address        = (string) $address->address;
        $this->postal_code    = (string) ($address->postal_code ?? '');
        $this->plaque         = (string) ($address->plaque ?? '');
        $this->unit           = (string) ($address->unit ?? '');
        $this->is_default     = (bool) $address->is_default;

        $this->showModal = true;
    }

    public function closeAddModal(): void
    {
        $this->showModal = false;
        $this->editId = null;
    }

    private function resetForm(): void
    {
        $this->editId         = null;
        $this->title          = '';
        $this->recipient_name = '';
        $this->phone          = '';
        $this->provinceId     = 0;
        $this->cityId         = 0;
        $this->address        = '';
        $this->postal_code    = '';
        $this->plaque         = '';
        $this->unit           = '';
        $this->is_default     = $this->addresses->isEmpty(); // first address becomes default automatically
    }

    /* ══════════ persistence ══════════ */

    public function saveAddress(): void
    {
        $this->validateWithToast(fn () => $this->validate($this->rules(), $this->messages()));

        $customer = auth('customer')->user();

        if (! $customer) {
            return;
        }

        $province = $this->provinceId > 0 ? Province::find($this->provinceId) : null;
        $city     = $this->cityId > 0 ? City::find($this->cityId) : null;

        $address = $this->editId
            ? $this->findAddress($this->editId)
            : new CustomerAddress(['customer_id' => $customer->id]);

        if (! $address) {
            return;
        }

        $address->fill([
            'title'          => trim($this->title) !== '' ? trim($this->title) : null,
            'recipient_name' => trim($this->recipient_name) !== '' ? trim($this->recipient_name) : null,
            'phone'          => $this->phone !== '' ? Phone::normalize($this->phone) : null,
            'province_id'    => $province?->id,
            'city_id'        => $city?->id,
            'province_name'  => $province?->name,
            'city_name'      => $city?->name,
            'address'        => trim($this->address),
            'postal_code'    => $this->postal_code !== '' ? $this->postal_code : null,
            'plaque'         => trim($this->plaque) !== '' ? trim($this->plaque) : null,
            'unit'           => trim($this->unit) !== '' ? trim($this->unit) : null,
            'is_default'     => $this->is_default,
        ]);

        $address->save();

        $this->promoteDefault($customer->id, $this->is_default ? $address->id : null);

        $wasEditing = $this->editId !== null;

        $this->closeAddModal();

        $this->dispatch('addresses-toast', message: $wasEditing
            ? 'آدرس به‌روزرسانی شد. ✅'
            : 'آدرس جدید ذخیره شد. ✅');
    }

    /** When exactly one address exists it is the default by definition. */
    private function promoteDefault(int $customerId, ?int $defaultId): void
    {
        $query = CustomerAddress::query()->where('customer_id', $customerId);

        if ($defaultId !== null) {
            $query->whereKeyNot($defaultId)->update(['is_default' => false]);

            return;
        }

        // no explicit default requested — keep exactly one when possible
        $count = (clone $query)->count();

        if ($count <= 1) {
            $query->update(['is_default' => true]);

            return;
        }

        if (! $query->clone()->where('is_default', true)->exists()) {
            $first = $query->clone()->orderByDesc('id')->first();

            $first?->forceFill(['is_default' => true])->save();
        }
    }

    public function makeDefault(int $id): void
    {
        $customer = auth('customer')->user();
        $address  = $this->findAddress($id);

        if (! $customer || ! $address) {
            return;
        }

        CustomerAddress::query()->where('customer_id', $customer->id)->update(['is_default' => false]);
        $address->forceFill(['is_default' => true])->save();

        $this->dispatch('addresses-toast', message: 'این آدرس پیش‌فرض شد. ✅');
    }

    public function askDelete(int $id): void
    {
        $this->confirmDeleteId = $id;
    }

    public function deleteAddress(): void
    {
        $address = $this->findAddress((int) $this->confirmDeleteId);

        $this->confirmDeleteId = null;

        if (! $address) {
            return;
        }

        $wasDefault = $address->is_default;
        $address->delete();

        if ($wasDefault) {
            $next = CustomerAddress::query()
                ->where('customer_id', auth('customer')->id())
                ->orderByDesc('id')
                ->first();

            $next?->forceFill(['is_default' => true])->save();
        }

        $this->dispatch('addresses-toast', message: 'آدرس حذف شد.');
    }

    private function findAddress(int $id): ?CustomerAddress
    {
        return CustomerAddress::query()
            ->where('customer_id', (int) auth('customer')->id())
            ->find($id);
    }

    /* ══════════ rules ══════════ */

    protected function rules(): array
    {
        return [
            'title'          => ['nullable', 'string', 'max:60'],
            'recipient_name' => ['required', 'string', 'min:3', 'max:80'],
            'phone'          => ['required', 'string', 'min:10', 'max:14'],
            'provinceId'     => ['required', 'integer', 'min:1', 'exists:provinces,id'],
            'cityId'         => ['required', 'integer', 'min:1', Rule::exists('cities', 'id')->where('province_id', $this->provinceId)],
            'address'        => ['required', 'string', 'min:10', 'max:500'],
            'postal_code'    => ['nullable', 'string', 'regex:/^\d{10}$/'],
            'plaque'         => ['nullable', 'string', 'max:20'],
            'unit'           => ['nullable', 'string', 'max:20'],
            'is_default'     => ['boolean'],
        ];
    }

    protected function messages(): array
    {
        return [
            'recipient_name.required' => 'نام گیرنده را وارد کنید.',
            'recipient_name.min'      => 'نام گیرنده باید حداقل ۳ حرف باشد.',
            'phone.required'          => 'شماره همراه را وارد کنید.',
            'phone.min'               => 'شماره همراه معتبر نیست (مثلاً ۰۹۱۲۳۴۵۶۷۸۹).',
            'provinceId.required'     => 'استان را انتخاب کنید.',
            'provinceId.min'          => 'استان را انتخاب کنید.',
            'cityId.required'         => 'شهر را انتخاب کنید.',
            'cityId.min'              => 'شهر را انتخاب کنید.',
            'cityId.exists'           => 'شهر انتخابی با استان هم‌خوانی ندارد.',
            'address.required'        => 'نشانی کامل را وارد کنید.',
            'address.min'             => 'نشانی باید حداقل ۱۰ حرف باشد.',
            'postal_code.regex'       => 'کد پستی باید ۱۰ رقم باشد.',
        ];
    }

    /* ══════════ computed data ══════════ */

    public function getAddressesProperty(): Collection
    {
        $customerId = (int) auth('customer')->id();

        return $customerId > 0
            ? CustomerAddress::query()->forCustomer($customerId)->get()
            : collect();
    }

    public function getProvincesProperty(): Collection
    {
        return Province::query()->orderBy('ordering')->orderBy('id')->get(['id', 'name']);
    }

    public function getCitiesProperty(): Collection
    {
        return $this->provinceId > 0
            ? City::query()->where('province_id', $this->provinceId)->orderBy('ordering')->orderBy('id')->get(['id', 'name'])
            : collect();
    }

    public function render()
    {
        return view('livewire.customer.addresses-page', [
            'addresses' => $this->addresses,
            'provinces' => $this->provinces,
            'cities'    => $this->cities,
        ])->title('آدرس‌های من');
    }
}
