<?php

namespace App\Livewire\Seller;

use App\Livewire\Concerns\NotifiesOnValidation;
use App\Models\SellerBnplSetting;
use App\Models\Setting;
use App\Models\Shop;
use App\Support\Fmt;
use App\Support\ImagePayload;
use App\Support\Payments\PaymentService;
use App\Support\Phone;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.seller-slot')]
class ShopEdit extends Component
{
    use NotifiesOnValidation;
    use WithFileUploads;

    public string $name = '';

    /** Seller's own display name (نام و نام خانوادگی) — moved here from the profile page. */
    public string $fullName = '';

    public string $instagramUsername = '';

    public string $about = '';

    public string $phone = '';

    public string $address = '';

    public string $status = 'active';

    /** Seller-approved display of remaining stock on shop/product pages. */
    public bool $showStock = false;

    /** Newly picked uploads (wire:model bindings — persisted instantly in the updated hooks). */
    public $logo = null;

    public $banner = null;

    /**
     * v0.30.0 — خرید اقساطی (درگاه‌های اقساطی): the seller's opt-in state
     * per BNPL gateway — keyed by the gateway slug (dots would nest the
     * wire bindings, slugs are dot-free).
     *
     * @var array<string, bool> gateway → فعال‌سازی
     */
    public array $bnplEnabled = [];

    /** @var array<string, bool> gateway → نمایش بج اقساط در تک‌صفحهٔ محصول */
    public array $bnplShowProduct = [];

    /** @var array<string, string> gateway → none|percent|fixed */
    public array $bnplFeeType = [];

    /** @var array<string, string> gateway → raw value (latin digits) */
    public array $bnplFeeValue = [];

    public function mount(): void
    {
        $shop = $this->shop();
        $seller = auth('seller')->user();

        $this->name = $shop->name;
        $this->fullName = (string) ($seller->full_name ?? '');
        $this->instagramUsername = (string) ($seller->instagram_username ?? '');
        $this->about = (string) $shop->about;
        $this->phone = $shop->phone ? Phone::toLocal($shop->phone) : '';
        $this->address = (string) $shop->address;
        $this->status = $shop->status;
        $this->showStock = (bool) $shop->show_stock;

        $this->loadBnplSettings((int) $seller->id);
    }

    /**
     * v0.30.0 — hydrate the installment opt-ins of this seller. Only the
     * gateways the ADMIN turned on are offered (the seller can never
     * enable one the platform has switched off).
     */
    protected function loadBnplSettings(int $sellerId): void
    {
        foreach (array_keys(PaymentService::availableBnplGateways()) as $gateway) {
            $setting = SellerBnplSetting::forSeller($gateway, $sellerId);

            $this->bnplEnabled[$gateway] = (bool) $setting?->enabled;
            $this->bnplShowProduct[$gateway] = (bool) $setting?->show_on_product;
            $this->bnplFeeType[$gateway] = $setting?->fee_type ?? 'none';
            $this->bnplFeeValue[$gateway] = $setting && $setting->fee_value > 0
                ? Fmt::toLatin((string) $setting->fee_value)
                : '';
        }
    }

    /** v0.30.0 — persist the installment opt-ins (called inside save()). */
    protected function saveBnplSettings(int $sellerId): void
    {
        foreach (array_keys(PaymentService::availableBnplGateways()) as $gateway) {
            $enabled = (bool) ($this->bnplEnabled[$gateway] ?? false);
            $feeType = (string) ($this->bnplFeeType[$gateway] ?? 'none');

            if (! in_array($feeType, SellerBnplSetting::FEE_TYPES, true)) {
                $feeType = 'none';
            }

            // the fee only exists when the seller actually opted in
            if (! $enabled) {
                $feeType = 'none';
            }

            $value = Fmt::toLatin((string) ($this->bnplFeeValue[$gateway] ?? ''));
            $value = (int) max(0, (int) preg_replace('/[^0-9]/', '', $value) ?: 0);

            if ($feeType === 'none') {
                $value = 0;
            }

            SellerBnplSetting::query()->updateOrCreate(
                ['seller_id' => $sellerId, 'gateway' => $gateway],
                [
                    'enabled' => $enabled,
                    'show_on_product' => $enabled && (bool) ($this->bnplShowProduct[$gateway] ?? false),
                    'fee_type' => $feeType,
                    'fee_value' => $value,
                ],
            );
        }
    }

    protected function shop(): Shop
    {
        return once(fn () => Shop::ensureForSeller(auth('seller')->user()));
    }

    public function updatedPhone(string $value): void
    {
        $this->phone = Fmt::toLatin($value);
    }

    /**
     * Logo/banner save the moment the seller picks a file — instant
     * feedback («آپلود شد») instead of a silent pick that only lands
     * after the full form save (the source of the "آپلود نمی‌شود" reports).
     */
    public function updatedLogo($value): void
    {
        $this->persistImage('logo', 'logo_path', 2048, 'لوگو');
    }

    public function updatedBanner($value): void
    {
        $this->persistImage('banner', 'banner_path', 4096, 'بنر');
    }

    protected function persistImage(string $property, string $column, int $maxKb, string $label): void
    {
        $file = $this->{$property};

        if (! $file) {
            return;
        }

        $this->resetErrorBag([$property]);

        // size/mime rules here so the seller sees the problem immediately
        $validator = Validator::make([$property => $file], [
            $property => ['image', 'mimes:jpg,jpeg,png,webp', "max:{$maxKb}", ImagePayload::rule()],
        ], [
            "{$property}.image" => "فایل {$label} تصویر نیست.",
            "{$property}.mimes" => "فرمت {$label} باید JPG، PNG یا WebP باشد.",
            "{$property}.max" => 'حجم '.$label.' حداکثر '.(int) ($maxKb / 1024).' مگابایت است.',
        ]);

        if ($validator->fails()) {
            $this->setErrorBag($validator->getMessageBag());
            $this->{$property} = null; // drop the bad pick

            return;
        }

        $shop = $this->shop();

        if ($shop->{$column}) {
            Storage::disk('public')->delete($shop->{$column});
        }

        $shop->update([$column => $file->store('shops/'.$shop->id, 'public')]);

        // release the temp upload; the shop now owns the real file
        $this->{$property} = null;

        $this->dispatch('shop-image-saved', label: $label);
    }

    public function removeLogo(): void
    {
        $shop = $this->shop();

        if ($shop->logo_path) {
            Storage::disk('public')->delete($shop->logo_path);
            $shop->update(['logo_path' => null]);
        }
    }

    public function removeBanner(): void
    {
        $shop = $this->shop();

        if ($shop->banner_path) {
            Storage::disk('public')->delete($shop->banner_path);
            $shop->update(['banner_path' => null]);
        }
    }

    public function save(): void
    {
        $this->validateWithToast(fn () => $this->validate([
            'name' => ['required', 'string', 'min:2', 'max:120'],
            'fullName' => ['nullable', 'string', 'min:3', 'max:60'],
            'about' => ['nullable', 'string', 'max:2000'],
            'phone' => ['nullable', 'string', function (string $attribute, mixed $value, \Closure $fail) {
                if ($value === '' || $value === null) {
                    return;
                }

                $normalized = Phone::normalize((string) $value);

                if (! Phone::isValid($normalized)) {
                    $fail('شماره تماس را صحیح وارد کنید (مثال: ۰۹۱۲۳۴۵۶۷۸۹).');
                }
            }],
            'address' => ['nullable', 'string', 'max:300'],
            'status' => ['required', 'in:active,paused'],
            'showStock' => ['boolean'],
        ], [
            'name.required' => 'نام فروشگاه را وارد کنید.',
            'name.min' => 'نام فروشگاه حداقل ۲ کاراکتر است.',
            'fullName.min' => 'نام و نام خانوادگی باید حداقل ۳ حرف باشد.',
            'fullName.max' => 'نام و نام خانوادگی حداکثر ۶۰ حرف است.',
        ]));

        // SECURITY (v0.20.0): an ADMIN pause is enforcement — the seller
        // cannot re-activate their own shop while the admin marker is set
        // (the marker is cleared only when an admin re-activates the shop).
        $adminPaused = (bool) Setting::get('shops.admin_pause.'.$this->shop()->id);

        if ($this->status === 'active' && $adminPaused) {
            $this->status = 'paused';
        }

        $this->shop()->update([
            'name' => trim($this->name),
            'about' => trim($this->about) ?: null,
            'phone' => $this->phone !== '' ? Phone::normalize($this->phone) : null,
            'address' => trim($this->address) ?: null,
            'status' => $this->status,
            'show_stock' => $this->showStock,
        ]);

        // v0.30.0 — the installment opt-ins live on the seller
        $this->saveBnplSettings((int) auth('seller')->user()->id);

        if ($this->status === 'paused' && $adminPaused) {
            $this->dispatch('panel-toast', message: 'فعال‌سازی فروشگاه توسط پشتیبانی متوقف شده است — برای رفع توقف با پشتیبانی تماس بگیرید.', type: 'error');
        }

        // seller's own display name lives on the seller, not the shop
        auth('seller')->user()->update([
            'full_name' => trim($this->fullName) !== '' ? trim($this->fullName) : null,
        ]);

        // NOTE: the Instagram handle is intentionally NOT editable here —
        // it doubles as the account handle and the public store link/slug.

        session()->flash('shop-flash', 'اطلاعات فروشگاه ذخیره شد.');

        $this->redirect(route('seller.shop'), navigate: true);
    }

    public function render()
    {
        return view('livewire.seller.shop-edit', [
            'shop' => $this->shop,
            'bnplGateways' => PaymentService::availableBnplGateways(),
        ])->title('ویرایش فروشگاه');
    }

    // ── Computed ─────────────────────────────────────────────────────

    public function getShopProperty(): Shop
    {
        return $this->shop();
    }
}
