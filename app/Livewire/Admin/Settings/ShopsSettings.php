<?php

namespace App\Livewire\Admin\Settings;

use App\Livewire\Admin\Concerns\GuardsPermissions;
use App\Livewire\Concerns\NotifiesOnValidation;
use App\Models\Setting;
use App\Support\ActivityLogger;
use Livewire\Component;

/**
 * Admin — «تنظیمات فروشگاه‌ها» (batch 79):
 *   • تایید خودکار فروشنده — new sellers skip the pending queue;
 *   • کمیسیون سراسری — percent (٪ of each shop's goods total) or fixed
 *     (تومان flat per shop per order); sellers may override it personally
 *     from their own edit modal (SellersIndex).
 * Stored as settings group "shops": auto_approve_seller / commission_type /
 * commission_value. Default commission = صفر (percent 0).
 */
class ShopsSettings extends Component
{
    use GuardsPermissions;
    use NotifiesOnValidation;

    protected static string $permission = 'admin.settings.shops';

    public bool $autoApprove = false;

    public string $commissionType = 'percent'; // percent | fixed

    public string $commissionValue = '0';

    public function mount(): void
    {
        $this->autoApprove = (bool) Setting::get('shops.auto_approve_seller', false);
        $this->commissionType = (string) Setting::get('shops.commission_type', 'percent');
        $this->commissionType = in_array($this->commissionType, ['percent', 'fixed'], true) ? $this->commissionType : 'percent';
        $this->commissionValue = (string) (int) Setting::get('shops.commission_value', 0);
    }

    public function updatedCommissionType(): void
    {
        $this->commissionValue = '0';
    }

    protected function rules(): array
    {
        return [
            'autoApprove' => ['boolean'],
            'commissionType' => ['required', 'in:percent,fixed'],
            'commissionValue' => [
                'required',
                'integer',
                'min:0',
                $this->commissionType === 'percent' ? 'max:100' : 'max:999999999999',
            ],
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
            'shops.commission_type' => $this->commissionType,
            'shops.commission_value' => (int) $this->commissionValue,
        ];

        $snapshot = ActivityLogger::settingsSnapshot(array_keys($pairs));

        Setting::set('shops.auto_approve_seller', $this->autoApprove, 'boolean');
        Setting::set('shops.commission_type', $this->commissionType, 'text');
        Setting::set('shops.commission_value', (int) $this->commissionValue, 'number');

        ActivityLogger::logSettingsChanged('تنظیمات فروشگاه‌ها (تایید خودکار و کمیسیون) ویرایش شد', $snapshot, $pairs);

        $this->dispatch('panel-toast', message: 'تنظیمات فروشگاه‌ها ذخیره شد. ✅', type: 'success');
    }

    public function render()
    {
        return view('livewire.admin.settings.shops-settings');
    }
}
