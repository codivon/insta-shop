<?php

namespace App\Livewire\Seller;

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

    /** Newly picked uploads (wire:model bindings — persisted instantly in the updated hooks). */
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

    protected function shop(): \App\Models\Shop
    {
        return once(fn () => \App\Models\Shop::ensureForSeller(auth('seller')->user()));
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
        $validator = \Illuminate\Support\Facades\Validator::make([$property => $file], [
            $property => ['image', 'mimes:jpg,jpeg,png,webp', "max:{$maxKb}"],
        ], [
            "{$property}.image" => "فایل {$label} تصویر نیست.",
            "{$property}.mimes" => "فرمت {$label} باید JPG، PNG یا WebP باشد.",
            "{$property}.max"   => 'حجم '.$label.' حداکثر '.(int) ($maxKb / 1024).' مگابایت است.',
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
            'address' => ['nullable', 'string', 'max:300'],
            'status'  => ['required', 'in:active,paused'],
        ], [
            'name.required' => 'نام فروشگاه را وارد کنید.',
            'name.min'      => 'نام فروشگاه حداقل ۲ کاراکتر است.',
        ]);

        $this->shop()->update([
            'name'     => trim($this->name),
            'about'    => trim($this->about) ?: null,
            'phone'    => $this->phone !== '' ? Phone::normalize($this->phone) : null,
            'address'  => trim($this->address) ?: null,
            'status'   => $this->status,
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
        ])->title('ویرایش فروشگاه');
    }

    // ── Computed ─────────────────────────────────────────────────────

    public function getShopProperty(): \App\Models\Shop
    {
        return $this->shop();
    }
}
