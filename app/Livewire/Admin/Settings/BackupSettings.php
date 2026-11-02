<?php

namespace App\Livewire\Admin\Settings;

use App\Livewire\Admin\Concerns\GuardsPermissions;
use App\Livewire\Concerns\NotifiesOnValidation;
use App\Models\Setting;
use App\Support\ActivityLogger;
use App\Support\Backups\BackupMailer;
use App\Support\Backups\BackupManager;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * Admin settings — «پشتیبان‌گیری خودکار» tab (embedded in the hub).
 *
 *  • Schedule: فعال/غیرفعال + روزانه / هفتگی / ماهانه / بازهٔ دلخواه (روز)
 *    + ساعت اجرا + مدت نگهداری فایل‌های قدیمی (حذف خودکار).
 *  • Email: ارسال فایل پشتیبان (خودکار تکه‌تکه وقتی حجیم است) با SMTP
 *    اختصاصی که همین‌جا وارد می‌شود + دکمهٔ ارسال ایمیل آزمایشی.
 */
class BackupSettings extends Component
{
    use GuardsPermissions;
    use NotifiesOnValidation;

    protected static string $permission = 'admin.settings.backups';

    /* ── schedule ── */
    public bool $autoEnabled = false;

    public string $frequency = 'daily';       // daily | weekly | monthly | custom

    public string $intervalDays = '3';

    public string $runTime = '03:00';

    public string $retentionDays = '180';

    /* ── email ── */
    public bool $emailEnabled = false;

    public string $emailTo = '';

    public string $emailMaxMb = '10';

    /* ── smtp ── */
    public string $smtpHost = '';

    public string $smtpPort = '587';

    public string $smtpUsername = '';

    public string $smtpPassword = '';

    public string $smtpEncryption = 'tls';    // tls | ssl | none

    public string $smtpFromAddress = '';

    public string $smtpFromName = '';

    public function mount(): void
    {
        $this->autoEnabled = (bool) Setting::get('backups.auto_enabled', false);
        $this->frequency = (string) Setting::get('backups.frequency', 'daily');
        $this->intervalDays = (string) Setting::get('backups.interval_days', '3');
        $this->runTime = BackupManager::autoRunTime();
        $this->retentionDays = (string) Setting::get('backups.retention_days', '180');
        $this->emailEnabled = (bool) Setting::get('backups.email_enabled', false);
        $this->emailTo = (string) Setting::get('backups.email_to', '');
        $this->emailMaxMb = (string) Setting::get('backups.email_max_mb', '10');
        $this->smtpHost = (string) Setting::get('backups.smtp_host', '');
        $this->smtpPort = (string) Setting::get('backups.smtp_port', '587');
        $this->smtpUsername = (string) Setting::get('backups.smtp_username', '');
        $this->smtpPassword = (string) Setting::get('backups.smtp_password', '');
        $this->smtpEncryption = (string) Setting::get('backups.smtp_encryption', 'tls');
        $this->smtpFromAddress = (string) Setting::get('backups.smtp_from_address', '');
        $this->smtpFromName = (string) Setting::get('backups.smtp_from_name', '');
    }

    protected function rules(): array
    {
        return [
            'autoEnabled' => ['boolean'],
            'frequency' => ['required', 'in:daily,weekly,monthly,custom'],
            'intervalDays' => ['required_if:frequency,custom', 'nullable', 'integer', 'min:1', 'max:365'],
            'runTime' => ['required', 'date_format:H:i', 'regex:/^\d{2}:\d{2}$/'],
            'retentionDays' => ['required', 'integer', 'min:7', 'max:3650'],
            'emailEnabled' => ['boolean'],
            'emailTo' => ['required_if:emailEnabled,true', 'nullable', 'email', 'max:190'],
            'emailMaxMb' => ['required', 'integer', 'min:2', 'max:25'],
            'smtpHost' => ['required_if:emailEnabled,true', 'nullable', 'string', 'max:190'],
            'smtpPort' => ['required_if:emailEnabled,true', 'nullable', 'integer', 'min:1', 'max:65535'],
            'smtpUsername' => ['nullable', 'string', 'max:190'],
            'smtpPassword' => ['nullable', 'string', 'max:190'],
            'smtpEncryption' => ['required', 'in:tls,ssl,none'],
            'smtpFromAddress' => ['nullable', 'email', 'max:190'],
            'smtpFromName' => ['nullable', 'string', 'max:190'],
        ];
    }

    protected function messages(): array
    {
        return [
            'frequency.required' => 'دورهٔ پشتیبان‌گیری را انتخاب کنید.',
            'frequency.in' => 'دورهٔ پشتیبان‌گیری نامعتبر است.',
            'intervalDays.required_if' => 'برای بازهٔ دلخواه، تعداد روز را وارد کنید.',
            'intervalDays.integer' => 'تعداد روز باید عدد باشد.',
            'intervalDays.min' => 'حداقل فاصلهٔ پشتیبان‌گیری ۱ روز است.',
            'intervalDays.max' => 'حداکثر فاصلهٔ پشتیبان‌گیری ۳۶۵ روز است.',
            'runTime.required' => 'ساعت اجرا را وارد کنید.',
            'runTime.date_format' => 'ساعت اجرا را به شکل HH:MM وارد کنید.',
            'retentionDays.required' => 'مدت نگهداری پشتیبان‌ها را وارد کنید.',
            'retentionDays.integer' => 'مدت نگهداری باید عدد باشد.',
            'retentionDays.min' => 'حداقل مدت نگهداری ۷ روز است.',
            'retentionDays.max' => 'حداکثر مدت نگهداری ۳۶۵۰ روز است.',
            'emailTo.required_if' => 'آدرس ایمیل گیرندهٔ پشتیبان را وارد کنید.',
            'emailTo.email' => 'آدرس ایمیل گیرنده معتبر نیست.',
            'emailMaxMb.required' => 'حجم هر بخش ایمیل را وارد کنید.',
            'emailMaxMb.integer' => 'حجم هر بخش باید عدد باشد.',
            'emailMaxMb.min' => 'حداقل حجم هر بخش ۲ مگابایت است.',
            'emailMaxMb.max' => 'حداکثر حجم هر بخش ۲۵ مگابایت است.',
            'smtpHost.required_if' => 'برای ارسال ایمیل، میزبان SMTP را وارد کنید.',
            'smtpPort.required_if' => 'برای ارسال ایمیل، پورت SMTP را وارد کنید.',
            'smtpPort.integer' => 'پورت SMTP باید عدد باشد.',
            'smtpFromAddress.email' => 'آدرس فرستنده معتبر نیست.',
            'smtpEncryption.in' => 'نوع رمزنگاری نامعتبر است.',
        ];
    }

    /** Pairs passed to Setting::setMany (single source of truth). */
    private function settingPairs(): array
    {
        return [
            'backups.auto_enabled' => $this->autoEnabled ? '1' : '0',
            'backups.frequency' => $this->frequency,
            'backups.interval_days' => $this->frequency === 'custom' ? max(1, (int) $this->intervalDays) : (int) $this->intervalDays,
            'backups.run_time' => BackupManager::normalizeTime($this->runTime),
            'backups.retention_days' => max(7, (int) $this->retentionDays),
            'backups.email_enabled' => $this->emailEnabled ? '1' : '0',
            'backups.email_to' => trim($this->emailTo),
            'backups.email_max_mb' => max(2, (int) $this->emailMaxMb),
            'backups.smtp_host' => trim($this->smtpHost),
            'backups.smtp_port' => (int) $this->smtpPort,
            'backups.smtp_username' => trim($this->smtpUsername),
            'backups.smtp_password' => $this->smtpPassword,
            'backups.smtp_encryption' => $this->smtpEncryption,
            'backups.smtp_from_address' => trim($this->smtpFromAddress),
            'backups.smtp_from_name' => trim($this->smtpFromName),
        ];
    }

    private function snapshotKeys(): array
    {
        return array_keys($this->settingPairs());
    }

    public function save(): void
    {
        $before = ActivityLogger::settingsSnapshot($this->snapshotKeys());

        $this->validateWithToast(fn () => $this->validate($this->rules(), $this->messages()));

        Setting::setMany($this->settingPairs());

        ActivityLogger::logSettingsChanged('تنظیمات پشتیبان‌گیری ذخیره شد', $before, $this->settingPairs());

        session()->flash('admin-backups-flash', 'تنظیمات پشتیبان‌گیری ذخیره شد.');
    }

    public function sendTestEmail(): void
    {
        $this->validateWithToast(fn () => $this->validate($this->rules(), $this->messages()));

        // Persist first so the mailer picks up exactly what the admin sees.
        Setting::setMany($this->settingPairs());

        $to = trim($this->emailTo) !== '' ? trim($this->emailTo) : trim($this->smtpFromAddress);

        if ($to === '' || ! filter_var($to, FILTER_VALIDATE_EMAIL)) {
            $this->dispatch('panel-toast', message: 'ابتدا «آدرس ایمیل گیرنده» را وارد کنید.', type: 'error');

            return;
        }

        [$ok, $message] = BackupMailer::sendTest($to);

        $this->dispatch('panel-toast', message: $message.($ok ? ' ✅' : ' ❌'), type: $ok ? 'success' : 'error');
    }

    /** When the next automatic backup will fire (for the info strip). */
    #[Computed]
    public function nextRun(): ?Carbon
    {
        return BackupManager::nextAutoRunAt();
    }

    public function render()
    {
        return view('livewire.admin.settings.backup-settings', [
            'nextRun' => BackupManager::nextAutoRunAt(),
        ])->title('تنظیمات — پشتیبان‌گیری خودکار');
    }
}
