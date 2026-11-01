<?php

namespace App\Livewire\Admin\Settings;

use App\Livewire\Admin\Concerns\GuardsPermissions;
use App\Models\Setting;
use App\Support\Branding;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Admin — General settings: site title, logo & favicon (with upload
 * progress), meta description and store contact info. Everything lands
 * in the settings table (groups: theme / branding / seo / store).
 */
#[Layout('layouts.admin-slot')]
#[Title('تنظیمات عمومی')]
class GeneralSettings extends Component
{
    use WithFileUploads;
    use GuardsPermissions;

    protected static string $permission = 'admin.settings.general';

    public string $appName = '';

    public string $metaDescription = '';

    public string $storePhone = '';

    public string $storeInstagram = '';

    /** Livewire temporary uploads */
    public $logo;

    public $favicon;

    public function mount(): void
    {
        $this->appName         = (string) Setting::get('theme.app_name', 'InstaShop');
        $this->metaDescription = (string) Setting::get('seo.meta_description', '');
        $this->storePhone      = (string) Setting::get('store.phone', '');
        $this->storeInstagram  = (string) Setting::get('store.instagram', '');
    }

    public function rules(): array
    {
        return [
            'appName'         => ['required', 'min:2', 'max:60'],
            'metaDescription' => ['nullable', 'max:300'],
            'storePhone'      => ['nullable', 'max:20'],
            'storeInstagram'  => ['nullable', 'max:60', 'regex:/^[A-Za-z0-9._]+$/'],
            'logo'            => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp,svg', 'max:2048'],
            'favicon'         => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp,svg,ico', 'max:1024'],
        ];
    }

    protected $messages = [
        'appName.required' => 'عنوان سایت را وارد کنید.',
        'storeInstagram.regex' => 'آیدی اینستاگرام فقط می‌تواند شامل حروف انگلیسی، عدد، نقطه و آندرلاین باشد.',
        'logo.image'  => 'لوگو باید یک تصویر باشد.',
        'logo.max'    => 'حجم لوگو حداکثر ۲ مگابایت است.',
        'favicon.image' => 'فاوآیکون باید یک تصویر باشد.',
        'favicon.max' => 'حجم فاوآیکون حداکثر ۱ مگابایت است.',
    ];

    public function updatedLogo(): void
    {
        $this->validateOnly('logo');
    }

    public function updatedFavicon(): void
    {
        $this->validateOnly('favicon');
    }

    public function save(): void
    {
        $this->validate();

        Setting::setMany([
            'theme.app_name' => $this->appName,
            'seo.meta_description' => $this->metaDescription,
            'store.phone' => $this->storePhone,
            'store.instagram' => $this->storeInstagram,
        ]);

        if ($this->logo) {
            Setting::set('branding.logo', Branding::store($this->logo, 'logo'));
            $this->reset('logo');
        }

        if ($this->favicon) {
            Setting::set('branding.favicon', Branding::store($this->favicon, 'favicon'));
            $this->reset('favicon');
        }

        session()->flash('admin-general-flash', 'تنظیمات عمومی با موفقیت ذخیره شد.');
    }

    public function removeLogo(): void
    {
        Branding::delete('logo');
        $this->reset('logo');
        session()->flash('admin-general-flash', 'لوگو حذف شد.');
    }

    public function removeFavicon(): void
    {
        Branding::delete('favicon');
        $this->reset('favicon');
        session()->flash('admin-general-flash', 'فاوآیکون حذف شد.');
    }

    public function render()
    {
        return view('livewire.admin.settings.general-settings')
            ->title('تنظیمات عمومی')
            ->layout('layouts.admin-slot', ['breadcrumb' => 'مدیریت کل — تنظیمات — تنظیمات عمومی']);
    }
}
