<?php

namespace App\Livewire\Admin\Settings;

use App\Livewire\Admin\Concerns\GuardsPermissions;
use App\Livewire\Concerns\NotifiesOnValidation;
use App\Models\Setting;
use App\Support\ActivityLogger;
use Livewire\Component;

/**
 * Admin — «تنظیمات استوری» tab of the settings hub:
 *   • سقف استوری فعال هر فروشگاه — how many RUNNING stories one shop may
 *     keep at the same time (seller panel enforces it on create);
 *   • حالت تایید — «خودکار» (story goes live immediately) or «دستی»
 *     (every new story waits in the admin stories page as «در انتظار تأیید»).
 * Stored as settings group "stories": max_active (number) / moderation
 * (auto|manual).
 */
class StoriesSettings extends Component
{
    use GuardsPermissions;
    use NotifiesOnValidation;

    protected static string $permission = 'admin.settings.stories';

    public string $maxActive = '3';

    public string $moderation = 'auto'; // auto | manual

    public function mount(): void
    {
        $this->maxActive = (string) max(1, (int) Setting::get('stories.max_active', 3));
        $this->moderation = (string) Setting::get('stories.moderation', 'auto');
        $this->moderation = in_array($this->moderation, ['auto', 'manual'], true) ? $this->moderation : 'auto';
    }

    protected function rules(): array
    {
        return [
            'maxActive' => ['required', 'integer', 'min:1', 'max:99'],
            'moderation' => ['required', 'in:auto,manual'],
        ];
    }

    protected function messages(): array
    {
        return [
            'maxActive.required' => 'سقف استوری فعال را وارد کنید.',
            'maxActive.integer' => 'سقف استوری فعال باید عدد باشد.',
            'maxActive.min' => 'سقف استوری فعال حداقل ۱ است.',
            'maxActive.max' => 'سقف استوری فعال حداکثر ۹۹ است.',
            'moderation.required' => 'حالت تایید استوری را انتخاب کنید.',
            'moderation.in' => 'حالت تایید نامعتبر است.',
        ];
    }

    public function save(): void
    {
        $this->validateWithToast(fn () => $this->validate($this->rules(), $this->messages()));

        $pairs = [
            'stories.max_active' => (int) $this->maxActive,
            'stories.moderation' => $this->moderation,
        ];

        $snapshot = ActivityLogger::settingsSnapshot(array_keys($pairs));

        Setting::set('stories.max_active', (int) $this->maxActive, 'number');
        Setting::set('stories.moderation', $this->moderation, 'text');

        ActivityLogger::logSettingsChanged('تنظیمات استوری ویرایش شد', $snapshot, $pairs);

        $this->dispatch('panel-toast', message: 'تنظیمات استوری ذخیره شد. ✅', type: 'success');
    }
}
