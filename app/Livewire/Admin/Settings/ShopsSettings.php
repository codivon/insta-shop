<?php

namespace App\Livewire\Admin\Settings;

use App\Livewire\Admin\Concerns\GuardsPermissions;
use App\Livewire\Concerns\NotifiesOnValidation;
use App\Models\Setting;
use App\Support\ActivityLogger;
use Livewire\Component;

/**
 * Admin — «تنظیمات فروشگاه‌ها» (batch 79 + v0.22.0 + v0.24.0 + v0.25.0):
 *   • تایید خودکار فروشنده — new sellers skip the pending queue;
 *   • اشتراک رایگان خودکار (v0.25.0 — client: «برای فروشنده ای که تازه
 *     ثبت نام میکنه، میخوام اشتراک رایگان خودکار براش فعال بشه و این
 *     مورد را در تنضیمات فروشندگان اضافه کن») — when ON, every new
 *     registration is granted the active free plan (price = 0) instantly;
 *   • کمیسیون سراسری — percent (٪ of each shop's goods total) or fixed
 *     (تومان flat per shop per order); sellers may override it personally
 *     from their own edit modal (SellersIndex);
 *   • نردبان — ONE master toggle (روشن/خاموش) ONLY (client: «در بخش
 *     نردبان فقط خاموش روشن باشد») — all bump PRICING lives in the
 *     dedicated «طرح‌های نردبان» page (BumpPlan rows), not here.
 *
 * RETIRED in v0.24.0: دورهٔ آزمایشی اشتراک (shops.subscription_trial_days)
 * — the client found it useless; no new trial is ever minted and the
 * setting is no longer written. Old trial rows in history keep rendering.
 *
 * Stored as settings group "shops": auto_approve_seller /
 * auto_free_subscription / commission_type / commission_value /
 * bump_enabled.
 * Default commission = صفر.
 */
class ShopsSettings extends Component
{
    use GuardsPermissions;
    use NotifiesOnValidation;

    protected static string $permission = 'admin.settings.shops';

    public bool $autoApprove = false;

    public bool $autoFreeSubscription = false;

    public string $commissionType = 'percent'; // percent | fixed

    public string $commissionValue = '0';

    public bool $bumpEnabled = false;

    public function mount(): void
    {
        $this->autoApprove = (bool) Setting::get('shops.auto_approve_seller', false);
        $this->autoFreeSubscription = (bool) Setting::get('shops.auto_free_subscription', false);
        $this->commissionType = (string) Setting::get('shops.commission_type', 'percent');
        $this->commissionType = in_array($this->commissionType, ['percent', 'fixed'], true) ? $this->commissionType : 'percent';
        $this->commissionValue = (string) (int) Setting::get('shops.commission_value', 0);
        $this->bumpEnabled = (bool) Setting::get('shops.bump_enabled', false);
    }

    public function updatedCommissionType(): void
    {
        $this->commissionValue = '0';
    }

    protected function rules(): array
    {
        return [
            'autoApprove' => ['boolean'],
            'autoFreeSubscription' => ['boolean'],
            'commissionType' => ['required', 'in:percent,fixed'],
            'commissionValue' => [
                'required',
                'integer',
                'min:0',
                $this->commissionType === 'percent' ? 'max:100' : 'max:999999999999',
            ],
            'bumpEnabled' => ['boolean'],
        ];
    }

    protected function messages(): array
    {
        return [
            'commissionValue.required' => 'مقدار کمیسیون را وارد کنید.',
            'commissionValue.integer' => 'مقدار کمیسیون باید عدد باشد.',
            'commissionValue.min' => 'کمیسیون نمی‌تواند منفی باشد.',
            'commissionValue.max' => 'درصد کمیسیون حداکثر ۱۰۰ است.',
        ];
    }

    public function save(): void
    {
        $this->validateWithToast(fn () => $this->validate($this->rules(), $this->messages()));

        $pairs = [
            'shops.auto_approve_seller' => $this->autoApprove ? '1' : '0',
            'shops.auto_free_subscription' => $this->autoFreeSubscription ? '1' : '0',
            'shops.commission_type' => $this->commissionType,
            'shops.commission_value' => (int) $this->commissionValue,
            'shops.bump_enabled' => $this->bumpEnabled ? '1' : '0',
        ];

        $snapshot = ActivityLogger::settingsSnapshot(array_keys($pairs));

        Setting::set('shops.auto_approve_seller', $this->autoApprove, 'boolean');
        Setting::set('shops.auto_free_subscription', $this->autoFreeSubscription, 'boolean');
        Setting::set('shops.commission_type', $this->commissionType, 'text');
        Setting::set('shops.commission_value', (int) $this->commissionValue, 'number');
        Setting::set('shops.bump_enabled', $this->bumpEnabled, 'boolean');

        ActivityLogger::logSettingsChanged('تنظیمات فروشگاه‌ها (تایید خودکار، اشتراک رایگان خودکار، کمیسیون سراسری و کلید نردبان) ویرایش شد', $snapshot, $pairs);

        $this->dispatch('panel-toast', message: 'تنظیمات فروشگاه‌ها ذخیره شد. ✅', type: 'success');
    }

    public function render()
    {
        return view('livewire.admin.settings.shops-settings');
    }
}
