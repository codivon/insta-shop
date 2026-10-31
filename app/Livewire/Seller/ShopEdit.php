<?php

namespace App\Livewire\Seller;

use App\Models\Seller;
use App\Models\Shop;
use App\Support\Fmt;
use App\Support\Phone;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.seller-slot')]
class ShopEdit extends Component
{
    use WithFileUploads;

    public string $name = '';

    public string $instagramUsername = '';

    public string $about = '';

    public string $phone = '';

    public string $address = '';

    public string $status = 'active';

    /** Newly picked replacements */
    public $logo = null;

    public $banner = null;

    public function mount(): void
    {
        $shop   = $this->shop();
        $seller = auth('seller')->user();

        $this->name              = $shop->name;
        $this->instagramUsername = (string) ($seller->instagram_username ?? '');
        $this->about             = (string) $shop->about;
        $this->phone             = $shop->phone ? Phone::toLocal($shop->phone) : '';
        $this->address           = (string) $shop->address;
        $this->status            = $shop->status;
    }

    protected function shop(): Shop
    {
        return once(fn () => Shop::ensureForSeller(auth('seller')->user()));
    }

    public function updatedPhone(string $value): void
    {
        $this->phone = Fmt::toLatin($value);
    }

    public function updatedInstagramUsername(string $value): void
    {
        $this->instagramUsername = ltrim(strtolower(trim($value)), '@');
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
        $sellerId = auth('seller')->id();

        $this->validate([
            'name'    => ['required', 'string', 'min:2', 'max:120'],
            'about'   => ['nullable', 'string', 'max:2000'],
            'phone'   => ['nullable', 'string', function (string $attribute, mixed $value, \Closure $fail) {
                if ($value === '' || $value === null) {
                    return;
                }

                $normalized = Phone::normalize((string) $value);

                if (! Phone::isValid($normalized)) {
                    $fail('شماره تماس را صحیح وارد کنید (مثال: ۰۹۱۲۳۴۵۶۷۸۹).');
                }
            }],
            'address'          => ['nullable', 'string', 'max:300'],
            'status'           => ['required', 'in:active,paused'],
            'logo'             => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'banner'           => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'instagramUsername' => [
                'nullable', 'string', 'min:2', 'max:80', 'regex:/^[a-zA-Z0-9._]+$/u',
                Rule::unique('sellers', 'instagram_username')->ignore($sellerId),
            ],
        ], [
            'name.required'    => 'نام فروشگاه را وارد کنید.',
            'name.min'         => 'نام فروشگاه حداقل ۲ کاراکتر است.',
            'instagramUsername.regex'  => 'شناسه اینستاگرام فقط می‌تواند حروف انگلیسی، عدد، نقطه و زیرخط باشد.',
            'instagramUsername.unique' => 'این شناسه اینستاگرام قبلاً استفاده شده است.',
            'logo.image'       => 'فایل لوگو تصویر نیست.',
            'logo.max'         => 'حجم لوگو حداکثر ۲ مگابایت است.',
            'banner.max'       => 'حجم بنر حداکثر ۴ مگابایت است.',
        ]);

        $shop = $this->shop();

        $shop->update([
            'name'               => trim($this->name),
            'about'              => trim($this->about) ?: null,
            'phone'              => $this->phone !== '' ? Phone::normalize($this->phone) : null,
            'address'            => trim($this->address) ?: null,
            'status'             => $this->status,
            'instagram_username' => $this->instagramUsername !== '' ? $this->instagramUsername : null,
        ]);

        // Keep the seller's Instagram handle in sync (it doubles as the account handle).
        if ($this->instagramUsername !== '' && auth('seller')->user()->instagram_username !== $this->instagramUsername) {
            Seller::query()->whereKey($sellerId)->update(['instagram_username' => $this->instagramUsername]);
        }

        if ($this->logo) {
            if ($shop->logo_path) {
                Storage::disk('public')->delete($shop->logo_path);
            }
            $shop->update(['logo_path' => $this->logo->store('shops/'.$shop->id, 'public')]);
        }

        if ($this->banner) {
            if ($shop->banner_path) {
                Storage::disk('public')->delete($shop->banner_path);
            }
            $shop->update(['banner_path' => $this->banner->store('shops/'.$shop->id, 'public')]);
        }

        session()->flash('shop-flash', 'اطلاعات فروشگاه ذخیره شد.');

        $this->redirect(route('seller.shop'), navigate: true);
    }

    public function render()
    {
        return view('livewire.seller.shop-edit')
            ->title('ویرایش فروشگاه');
    }

    // ── Computed ─────────────────────────────────────────────────────

    public function getShopProperty(): Shop
    {
        return $this->shop();
    }
}
