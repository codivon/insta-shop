<?php

namespace App\Livewire\Seller;

use App\Models\Carrier;
use App\Models\City;
use App\Models\Province;
use App\Support\Fmt;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * «حمل و نقل» — seller shipping methods (روش‌های ارسال).
 *
 * The create/edit modal follows the requested sample layout: three tabs
 * (اطلاعات کلی / مناطق و هزینه‌ها / بازه زمانی ارسال) with the exact same
 * fields. Provinces & cities come from the seeded location tables and are
 * managed ONLY from the admin panel.
 */
#[Layout('layouts.seller-slot', ['hideTabbar' => true])]
class ShippingPage extends Component
{
    use WithFileUploads;

    /* ══════════ modal state ══════════ */

    /** Sample parity: the modal opens/closes with showCreateModal; when
     *  editId is set the same form saves the existing carrier instead. */
    public bool $showCreateModal = false;

    public ?int $editId = null;

    /* ══════════ form fields (same names as the sample) ══════════ */

    public string $createTitle = '';

    /** Newly picked image (TemporaryUploadedFile). */
    public $createImage = null;

    /** Keep/replace/remove the saved image while editing. */
    public bool $removeImage = false;

    public bool $createIsActive = true;

    public string $createWaitingTime = '';

    public string $createMaxPackageWeight = '';

    public string $createMinPackageWeight = '';

    public string $createDescription = '';

    public int $createProvinceId = 0;

    public int $createCityId = 0;

    public bool $createCarrigeForward = false;

    public string $createFreeShippingWeight = '';

    public string $createFreeShippingPrice = '';

    public string $createExtraCost = '';

    public string $createCoveredCities = Carrier::COVERED_ALL;

    /** Selected city ids when covered_cities = select_city. */
    public array $createCoveredCityIds = [];

    /** Province filter of the covered-city picker. */
    public int $createZoneProvinceId = 0;

    public string $createDeliveryTimeType = Carrier::TIME_DEFAULT;

    public int $createStartDaysAfterOrder = 1;

    public string $createDefaultDeliveryRange = '';

    public function mount(): void
    {
        $this->shop(); // ensure the shop exists
    }

    protected function shop()
    {
        return once(fn () => \App\Models\Shop::ensureForSeller(auth('seller')->user()));
    }

    /* ══════════ modal lifecycle ══════════ */

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->resetErrorBag();
        $this->showCreateModal = true;

        // reset the Alpine tab back to «اطلاعات کلی»
        $this->dispatch('shipping-modal-open');
    }

    public function openEditModal(int $id): void
    {
        $carrier = $this->findCarrier($id);

        if (! $carrier) {
            return;
        }

        $this->resetForm();
        $this->resetErrorBag();

        $this->editId                   = $carrier->id;
        $this->createTitle              = (string) $carrier->title;
        $this->createIsActive           = (bool) $carrier->is_active;
        $this->createWaitingTime        = (string) ($carrier->waiting_time ?? '');
        $this->createMaxPackageWeight   = $carrier->max_package_weight ? (string) $carrier->max_package_weight : '';
        $this->createMinPackageWeight   = $carrier->min_package_weight ? (string) $carrier->min_package_weight : '';
        $this->createDescription        = (string) ($carrier->description ?? '');
        $this->createProvinceId         = (int) ($carrier->origin_province_id ?? 0);
        $this->createCityId             = (int) ($carrier->origin_city_id ?? 0);
        $this->createCarrigeForward     = (bool) $carrier->carriage_forward;
        $this->createFreeShippingWeight = $carrier->free_shipping_weight ? (string) $carrier->free_shipping_weight : '';
        $this->createFreeShippingPrice  = $carrier->free_shipping_price ? (string) $carrier->free_shipping_price : '';
        $this->createExtraCost          = (string) ($carrier->extra_cost ?: 0);
        $this->createCoveredCities      = $carrier->covered_cities;
        $this->createCoveredCityIds     = $carrier->cities()->pluck('city_id')->map(fn ($v) => (int) $v)->all();
        $this->createDeliveryTimeType   = $carrier->delivery_time_type;
        $this->createStartDaysAfterOrder = (int) $carrier->start_days_after_order;
        $this->createDefaultDeliveryRange = (string) ($carrier->default_delivery_range ?? '');

        $this->showCreateModal = true;

        $this->dispatch('shipping-modal-open');
    }

    public function closeCreateModal(): void
    {
        $this->showCreateModal = false;
        $this->editId = null;
    }

    private function resetForm(): void
    {
        $this->editId                    = null;
        $this->createTitle               = '';
        $this->createImage               = null;
        $this->removeImage               = false;
        $this->createIsActive            = true;
        $this->createWaitingTime         = '';
        $this->createMaxPackageWeight    = '';
        $this->createMinPackageWeight    = '';
        $this->createDescription         = '';
        $this->createProvinceId          = 0;
        $this->createCityId              = 0;
        $this->createCarrigeForward      = false;
        $this->createFreeShippingWeight  = '';
        $this->createFreeShippingPrice   = '';
        $this->createExtraCost           = '';
        $this->createCoveredCities       = Carrier::COVERED_ALL;
        $this->createCoveredCityIds      = [];
        $this->createZoneProvinceId      = 0;
        $this->createDeliveryTimeType    = Carrier::TIME_DEFAULT;
        $this->createStartDaysAfterOrder = 1;
        $this->createDefaultDeliveryRange = '';
    }

    /* ══════════ cascades ══════════ */

    public function updatedCreateProvinceId(): void
    {
        $this->createCityId = 0;
    }

    public function updatedCreateCoveredCities(): void
    {
        if ($this->createCoveredCities === Carrier::COVERED_ALL) {
            $this->createCoveredCityIds = [];
        }
    }

    /* ══════════ input normalizers (Persian digits → latin) ══════════ */

    public function updatedCreateMaxPackageWeight($value): void
    {
        $this->createMaxPackageWeight = Fmt::toLatin((string) $value);
    }

    public function updatedCreateMinPackageWeight($value): void
    {
        $this->createMinPackageWeight = Fmt::toLatin((string) $value);
    }

    public function updatedCreateFreeShippingWeight($value): void
    {
        $this->createFreeShippingWeight = Fmt::toLatin((string) $value);
    }

    public function updatedCreateFreeShippingPrice($value): void
    {
        $this->createFreeShippingPrice = Fmt::toLatin((string) $value);
    }

    public function updatedCreateExtraCost($value): void
    {
        $this->createExtraCost = Fmt::toLatin((string) $value);
    }

    /* ══════════ persistence ══════════ */

    /** wire:submit of the modal — creates when editId is null, updates otherwise. */
    public function createCarrier(): void
    {
        $this->validate($this->rules(), $this->messages());

        $carrier = $this->editId
            ? $this->findCarrier($this->editId)
            : new Carrier(['shop_id' => $this->shop()->id]);

        if (! $carrier) {
            $this->closeCreateModal();

            return;
        }

        $carrier->fill([
            'title'                  => trim($this->createTitle),
            'is_active'              => $this->createIsActive,
            'waiting_time'           => trim($this->createWaitingTime) !== '' ? trim($this->createWaitingTime) : null,
            'max_package_weight'     => $this->createMaxPackageWeight !== '' ? (int) $this->createMaxPackageWeight : null,
            'min_package_weight'     => $this->createMinPackageWeight !== '' ? (int) $this->createMinPackageWeight : null,
            'description'            => trim($this->createDescription) !== '' ? trim($this->createDescription) : null,
            'origin_province_id'     => $this->createProvinceId > 0 ? $this->createProvinceId : null,
            'origin_city_id'         => $this->createCityId > 0 ? $this->createCityId : null,
            'carriage_forward'       => $this->createCarrigeForward,
            'free_shipping_weight'   => $this->createFreeShippingWeight !== '' ? (int) $this->createFreeShippingWeight : null,
            'free_shipping_price'    => $this->createFreeShippingPrice !== '' ? (int) $this->createFreeShippingPrice : null,
            'extra_cost'             => $this->createExtraCost !== '' ? (int) $this->createExtraCost : 0,
            'covered_cities'         => $this->createCoveredCities,
            'delivery_time_type'     => $this->createDeliveryTimeType,
            'start_days_after_order' => (int) $this->createStartDaysAfterOrder,
            'default_delivery_range' => trim($this->createDefaultDeliveryRange) !== '' ? trim($this->createDefaultDeliveryRange) : null,
        ]);

        if (! $carrier->exists) {
            $carrier->sort = (int) Carrier::query()->where('shop_id', $this->shop()->id)->max('sort') + 1;
        }

        $carrier->save();

        /* ── image ── */
        if ($this->createImage) {
            if ($carrier->image_path) {
                \Storage::disk('public')->delete($carrier->image_path);
            }

            $carrier->update(['image_path' => $this->createImage->store('carriers', 'public')]);
        } elseif ($this->removeImage && $carrier->image_path) {
            \Storage::disk('public')->delete($carrier->image_path);
            $carrier->update(['image_path' => null]);
        }

        /* ── covered cities pivot ── */
        $carrier->cities()->sync(
            $this->createCoveredCities === Carrier::COVERED_SELECT_CITY ? array_map('intval', $this->createCoveredCityIds) : []
        );

        $this->closeCreateModal();

        $this->dispatch('shipping-toast', message: $this->editId
            ? 'روش ارسال به‌روزرسانی شد. ✅'
            : 'روش ارسال جدید ایجاد شد. ✅');
    }

    public function toggleCarrier(int $id): void
    {
        $carrier = $this->findCarrier($id);

        if ($carrier) {
            $carrier->update(['is_active' => ! $carrier->is_active]);
        }
    }

    public function deleteCarrier(int $id): void
    {
        $carrier = $this->findCarrier($id);

        if (! $carrier) {
            return;
        }

        if ($carrier->image_path) {
            \Storage::disk('public')->delete($carrier->image_path);
        }

        $carrier->cities()->detach();
        $carrier->delete();

        $this->dispatch('shipping-toast', message: 'روش ارسال حذف شد.');
    }

    private function findCarrier(int $id): ?Carrier
    {
        return Carrier::query()->where('shop_id', $this->shop()->id)->find($id);
    }

    /* ══════════ rules ══════════ */

    protected function rules(): array
    {
        return [
            'createTitle'              => ['required', 'string', 'min:2', 'max:120'],
            'createImage'              => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:1024'],
            'createIsActive'           => ['boolean'],
            'createWaitingTime'        => ['nullable', 'string', 'max:120'],
            'createMaxPackageWeight'   => ['nullable', 'integer', 'min:0', 'max:10000000'],
            'createMinPackageWeight'   => ['nullable', 'integer', 'min:0', 'max:10000000'],
            'createDescription'        => ['nullable', 'string', 'max:2000'],
            'createProvinceId'         => ['required', 'integer', 'exists:provinces,id'],
            // unselected select → int 0 — must NOT hit «exists»
            'createCityId'             => [Rule::excludeIf(fn () => $this->createCityId === 0), 'integer', 'exists:cities,id'],
            'createCarrigeForward'     => ['boolean'],
            'createFreeShippingWeight' => ['nullable', 'integer', 'min:0', 'max:10000000'],
            'createFreeShippingPrice'  => ['nullable', 'integer', 'min:0', 'max:999999999999'],
            'createExtraCost'          => ['nullable', 'integer', 'min:0', 'max:999999999999'],
            'createCoveredCities'      => ['required', 'in:'.Carrier::COVERED_ALL.','.Carrier::COVERED_SELECT_CITY],
            'createCoveredCityIds'     => ['exclude_unless:createCoveredCities,'.Carrier::COVERED_SELECT_CITY, 'required', 'array', 'min:1'],
            'createCoveredCityIds.*'   => ['integer', 'exists:cities,id'],
            'createDeliveryTimeType'   => ['required', 'in:'.Carrier::TIME_DEFAULT.','.Carrier::TIME_USER_SELECT],
            'createStartDaysAfterOrder'=> ['required', 'integer', 'min:1', 'max:11'],
            'createDefaultDeliveryRange'=> ['exclude_unless:createDeliveryTimeType,'.Carrier::TIME_DEFAULT, 'required', 'string', 'min:3', 'max:150'],
        ];
    }

    protected function messages(): array
    {
        return [
            'createTitle.required'   => 'عنوان روش ارسال را وارد کنید.',
            'createTitle.min'        => 'عنوان حداقل ۲ کاراکتر است.',
            'createImage.image'      => 'فایل انتخاب‌شده تصویر نیست.',
            'createImage.max'        => 'حجم تصویر حداکثر ۱ مگابایت است.',
            'createProvinceId.required' => 'استان فروشگاه (مبدأ ارسال) را انتخاب کنید.',
            'createCoveredCityIds.required' => 'حداقل یک شهر تحت پوشش انتخاب کنید.',
            'createDefaultDeliveryRange.required' => 'متن بازه زمانی ارسال پیش‌فرض را وارد کنید.',
        ];
    }

    /* ══════════ computed data ══════════ */

    public function getCarriersProperty(): Collection
    {
        return Carrier::query()
            ->where('shop_id', $this->shop()->id)
            ->with(['cities:id,province_id,name', 'originProvince:id,name', 'originCity:id,name'])
            ->orderBy('sort')
            ->orderBy('id')
            ->get();
    }

    public function getProvincesProperty(): Collection
    {
        return Province::query()->orderBy('ordering')->orderBy('id')->get(['id', 'name']);
    }

    /** Cities of the origin province (شهر فروشگاه select). */
    public function getOriginCitiesProperty(): Collection
    {
        return $this->createProvinceId > 0
            ? City::query()->where('province_id', $this->createProvinceId)->orderBy('ordering')->orderBy('id')->get(['id', 'name'])
            : collect();
    }

    /** Cities of the zone-picker province filter (covered-city checkboxes). */
    public function getZoneCitiesProperty(): Collection
    {
        return $this->createZoneProvinceId > 0
            ? City::query()->where('province_id', $this->createZoneProvinceId)->orderBy('ordering')->orderBy('id')->get(['id', 'name'])
            : collect();
    }

    /** Names of the currently covered cities (chips inside the modal). */
    public function getCoveredCitiesNamesProperty(): Collection
    {
        if ($this->createCoveredCityIds === []) {
            return collect();
        }

        return City::query()->whereIn('id', $this->createCoveredCityIds)->get(['id', 'name']);
    }

    /** Province names of the covered cities (grouped chips header). */
    public function getCoveredProvincesLabelProperty(): string
    {
        if ($this->createCoveredCityIds === []) {
            return '';
        }

        $names = City::query()
            ->whereIn('cities.id', $this->createCoveredCityIds)
            ->join('provinces', 'provinces.id', '=', 'cities.province_id')
            ->pluck('provinces.name')
            ->unique()
            ->values();

        return $names->implode('، ');
    }

    public function render()
    {
        return view('livewire.seller.shipping-page', [
            'carriers'      => $this->carriers,
            'provinces'     => $this->provinces,
            'originCities'  => $this->originCities,
            'zoneCities'    => $this->zoneCities,
            'coveredNames'  => $this->coveredCitiesNames,
            'coveredProvinces' => $this->coveredProvincesLabel,
        ])->title('حمل و نقل');
    }
}
