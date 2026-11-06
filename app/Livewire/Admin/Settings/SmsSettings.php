<?php

namespace App\Livewire\Admin\Settings;

use App\Livewire\Admin\Concerns\GuardsPermissions;
use App\Livewire\Concerns\NotifiesOnValidation;
use App\Models\Setting;
use App\Models\SmsLog;
use App\Support\ActivityLogger;
use App\Support\Phone;
use App\Support\Sms\SmsManager;
use App\Support\Sms\SmsMessage;
use App\Support\Sms\SmsTemplates;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Admin — SMS settings: choose provider, fill its credentials (stored in
 * the settings table) and fire a test message. Recent sms_logs below.
 */
#[Layout('layouts.admin-slot')]
class SmsSettings extends Component
{
    use GuardsPermissions;
    use NotifiesOnValidation;

    protected static string $permission = 'admin.settings.sms';

    public string $driver = 'log';

    /** @var array<string, string> keyed "driver.field" */
    public array $fields = [];

    public string $testPhone = '';

    public string $testText = '';

    public function mount(): void
    {
        $this->driver = (string) Setting::get('sms.driver', 'log');

        // Pre-fill every driver's fields from settings (nested: fields[driver][field]).
        foreach ($this->driverList() as $d) {
            foreach (array_keys($d['fields']) as $field) {
                $this->fields[$d['name']][$field] = (string) Setting::get("sms.{$d['name']}.{$field}", '');
            }
        }
    }

    /** All registered drivers as plain arrays for the blade view. */
    public function driverList(): Collection
    {
        return collect(app(SmsManager::class)->drivers())
            ->map(fn ($driver) => [
                'name' => $driver->name(),
                'label' => $driver->label(),
                'fields' => $driver->settingsFields(),
                'is_active' => $driver->name() === Setting::get('sms.driver', 'log'),
            ]);
    }

    public function save(): void
    {
        $drivers = $this->driverList();

        if (! $drivers->pluck('name')->contains($this->driver)) {
            $this->addError('driver', 'پروایدر نامعتبر است.');

            return;
        }

        $pairs = ['sms.driver' => $this->driver];

        $activeFields = $drivers->firstWhere('name', $this->driver)['fields'] ?? [];

        foreach (array_keys($activeFields) as $field) {
            $pairs["sms.{$this->driver}.{$field}"] = trim((string) ($this->fields[$this->driver][$field] ?? ''));
        }

        $snapshot = ActivityLogger::settingsSnapshot(array_keys($pairs));

        Setting::setMany($pairs);

        ActivityLogger::logSettingsChanged(
            'تنظیمات پیامک ویرایش شد (پروایدر فعال: '.($drivers->firstWhere('name', $this->driver)['label'] ?? $this->driver).')',
            $snapshot,
            $pairs,
        );

        session()->flash('admin-sms-flash', 'تنظیمات پیامک ذخیره شد؛ پروایدر فعال اکنون «'.($drivers->firstWhere('name', $this->driver)['label'] ?? $this->driver).'» است.');
    }

    public function sendTest(): void
    {
        $this->validateWithToast(fn () => $this->validate([
            'testPhone' => ['required'],
            'testText' => ['required', 'min:2', 'max:200'],
        ], [
            'testPhone.required' => 'شماره موبایل تست را وارد کنید.',
            'testText.required' => 'متن پیام تست را وارد کنید.',
        ]));

        $normalized = Phone::normalize($this->testPhone);

        if (! $normalized || ! Phone::isValid($normalized)) {
            $this->addError('testPhone', 'شماره موبایل معتبر نیست. نمونه: 09123456789');

            return;
        }

        $manager = app(SmsManager::class);
        $label = $this->driverList()->firstWhere('name', $this->driver)['label'] ?? $this->driver;

        // Pattern-based providers (کاوه‌نگار، آی‌پی‌پنل، فراز، sms.ir) can
        // only deliver through a registered panel pattern — a raw free-text
        // test would always fail. Instead the real otp_login template is
        // sent (with a throwaway code) so the test exercises the exact
        // production path: credentials + pattern code + variables.
        $isPattern = $this->driver !== 'log' && $manager->has($this->driver) && $manager->driver($this->driver)->patternBased();

        if ($isPattern) {
            $message = SmsTemplates::make(
                'otp_login',
                $normalized,
                ['code' => (string) random_int(100000, 999999), 'minutes' => 5],
                'admin',
            );
        } else {
            $message = SmsMessage::make($normalized)
                ->text($this->testText)
                ->panel('admin');
        }

        // Send through the currently SELECTED driver (not necessarily the
        // saved active one) so the admin can test before saving.
        $result = $manager->send($message, $this->driver);

        if ($result->ok) {
            session()->flash('admin-sms-flash', $isPattern
                ? 'پیام تست پترنی (قالب ورود OTP) با موفقیت از طریق «'.$label.'» ارسال شد.'
                : 'پیام تست با موفقیت از طریق «'.$label.'» ارسال شد.');
            $this->reset('testText');
        } else {
            session()->flash('admin-sms-error', 'ارسال تست ناموفق بود: '.($result->error ?: 'خطای ناشناخته'));
        }
    }

    public function getLogsProperty(): Collection
    {
        return SmsLog::query()->latest('id')->limit(12)->get();
    }

    public function render()
    {
        return view('livewire.admin.settings.sms-settings', [
            'drivers' => $this->driverList(),
            'logs' => $this->logs,
        ])->title('تنظیمات پیامک')
            ->layout('layouts.admin-slot', ['breadcrumb' => 'مدیریت کل — تنظیمات پیامک']);
    }
}
