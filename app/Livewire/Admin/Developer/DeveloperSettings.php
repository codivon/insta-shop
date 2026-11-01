<?php

namespace App\Livewire\Admin\Developer;

use App\Livewire\Admin\Concerns\GuardsPermissions;
use App\Models\Setting;
use App\Support\MigrationInspector;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Admin — Developer settings («تنظیمات برنامه‌نویس»), tabbed:
 *
 *  1. Cron job      — is the scheduler alive? (heartbeat setting), the
 *                     exact cPanel commands with detected paths, and the
 *                     shared-hosting "two cron jobs" queue mode toggle.
 *  2. Maintenance   — close the site for visitors with a secret bypass URL.
 *  3. Updates       — compare the installed version against the latest
 *                     GitHub release of the project.
 *  4. Database      — pending migrations (tables + fields, statically
 *                     parsed) with a one-click "run" button. The whole
 *                     tab disappears when everything is migrated.
 */
#[Layout('layouts.admin-slot')]
#[Title('تنظیمات برنامه‌نویس')]
class DeveloperSettings extends Component
{
    use GuardsPermissions;

    protected static string $permission = 'admin.developer.settings';

    /* ── Cron ── */
    public bool $sharedHostingCronEnabled = false;

    /* ── Maintenance ── */
    public bool $maintenanceEnabled = false;

    public string $maintenanceSecret = '';

    public string $maintenanceTitle = '';

    public string $maintenanceDescription = '';

    /* ── Updater ── */
    public bool $checking = false;

    public ?array $updateInfo = null;

    /* ── Migrations ── */
    public bool $runningMigrations = false;

    public function mount(): void
    {
        $this->sharedHostingCronEnabled = (bool) Setting::get('developer.queue_shared_hosting');
        $this->maintenanceEnabled       = (bool) Setting::get('developer.maintenance.enabled');
        $this->maintenanceSecret        = (string) Setting::get('developer.maintenance.secret', '');
        $this->maintenanceTitle         = (string) Setting::get('developer.maintenance.title', 'در حال بروزرسانی هستیم!');
        $this->maintenanceDescription   = (string) Setting::get('developer.maintenance.description', 'در حال بروزرسانی وبسایت هستیم لطفاً دقایقی بعد مراجعه کنید. از صبر و شکیبایی شما متشکریم.');

        if ($this->maintenanceSecret === '') {
            $this->maintenanceSecret = strtoupper(bin2hex(random_bytes(6)));
        }

        $this->updateInfo = $this->cachedUpdateInfo();
    }

    /* ══════════════════════════ CRON ══════════════════════════ */

    /** Raw last heartbeat stamp or null. */
    public function cronLastRun(): ?string
    {
        if (! \App\Support\SchemaReady::check('settings')) {
            return null;
        }

        return Setting::get('developer.cron_last_run');
    }

    /** Cron is active while the heartbeat is younger than the grace period. */
    public function cronActive(): bool
    {
        $last = $this->cronLastRun();

        if (! $last) {
            return false;
        }

        $ts = strtotime($last);

        return $ts !== false && (time() - $ts) <= (int) config('instashop.cron_heartbeat_seconds', 130);
    }

    /** Real paths detected on THIS server (host must adapt them if needed). */
    public function cronCommands(): array
    {
        $php  = trim((string) \PHP_BINARY) ?: 'php';
        $base = base_path();

        $schedule = "* * * * * cd {$base} && {$php} artisan schedule:run >> /dev/null 2>&1";
        $queue    = "* * * * * cd {$base} && {$php} artisan queue:work --tries=3 --sleep=3 --stop-when-empty";

        return [$schedule, $queue];
    }

    public function saveCron(): void
    {
        Setting::set('developer.queue_shared_hosting', $this->sharedHostingCronEnabled ? '1' : '0', 'boolean');

        session()->flash('admin-developer-flash', 'تنظیمات کرون جاب ذخیره شد.');
    }

    /* ═══════════════════════ MAINTENANCE ═══════════════════════ */

    public function regenerateSecret(): void
    {
        $this->maintenanceSecret = strtoupper(bin2hex(random_bytes(6)));
    }

    public function enableMaintenance(): void
    {
        $this->validate([
            'maintenanceSecret'        => ['required', 'alpha_num', 'min:6', 'max:32'],
            'maintenanceTitle'         => ['required', 'min:3', 'max:120'],
            'maintenanceDescription'   => ['nullable', 'max:1000'],
        ], [
            'maintenanceSecret.required'      => 'کد مخفی را وارد کنید.',
            'maintenanceTitle.required'       => 'عنوان صفحه بروزرسانی را وارد کنید.',
        ]);

        Setting::setMany([
            'developer.maintenance.secret'      => $this->maintenanceSecret,
            'developer.maintenance.title'       => $this->maintenanceTitle,
            'developer.maintenance.description' => $this->maintenanceDescription,
            'developer.maintenance.enabled'     => ['value' => '1', 'type' => 'boolean'],
        ]);

        $this->maintenanceEnabled = true;

        Cache::forget('instashop.settings');

        session()->flash('admin-developer-flash', 'حالت بروزرسانی فعال شد — سایت برای بازدیدکنندگان بسته است.');
    }

    public function disableMaintenance(): void
    {
        Setting::set('developer.maintenance.enabled', '0', 'boolean');
        Cache::forget('instashop.settings');

        $this->maintenanceEnabled = false;

        session()->flash('admin-developer-flash', 'حالت بروزرسانی غیرفعال شد — سایت دوباره در دسترس است.');
    }

    /* ═════════════════════════ UPDATER ═════════════════════════ */

    public function checkUpdate(): void
    {
        $this->checking = true;

        try {
            $this->updateInfo = $this->fetchLatestRelease(force: true);
        } catch (\Throwable $e) {
            $this->updateInfo = ['error' => 'اتصال به GitHub برقرار نشد. '.$e->getMessage()];
        }

        $this->checking = false;
    }

    public function currentVersion(): string
    {
        return (string) config('instashop.version', '0.0.0');
    }

    protected function cachedUpdateInfo(): ?array
    {
        try {
            return $this->fetchLatestRelease(force: false);
        } catch (\Throwable) {
            return ['error' => 'بررسی نسخه جدید ممکن نشد (اتصال به GitHub).'];
        }
    }

    /**
     * @return array{tag: string, name: string, url: string, notes: string, newer: bool}|array{error: string}
     */
    protected function fetchLatestRelease(bool $force): array
    {
        $repo     = (string) config('instashop.update_repo', 'codivon/insta-shop');
        $cacheKey = 'instashop.latest-release';

        $data = $force
            ? null
            : Cache::get($cacheKey);

        if (! is_array($data)) {
            $response = Http::timeout(10)
                ->withUserAgent('InstaShop-Updater')
                ->accept('application/vnd.github+json')
                ->get("https://api.github.com/repos/{$repo}/releases/latest");

            if (! $response->successful()) {
                if ($force) {
                    return ['error' => 'دریافت اطلاعات نسخه از GitHub ناموفق بود. (کد '.$response->status().')'];
                }

                return ['error' => 'بررسی نسخه جدید ممکن نشد (اتصال به GitHub).'];
            }

            $data = [
                'tag'   => (string) $response->json('tag_name', ''),
                'name'  => (string) $response->json('name', ''),
                'url'   => (string) $response->json('html_url', ''),
                'notes' => mb_substr((string) $response->json('body', ''), 0, 1200),
            ];

            Cache::put($cacheKey, $data, now()->addMinutes((int) config('instashop.update_cache_minutes', 60)));
        }

        $latest = ltrim($data['tag'], 'vV');

        $data['newer'] = $latest !== '' && version_compare($latest, (string) config('instashop.version'), '>');

        return $data;
    }

    /* ════════════════════════ MIGRATIONS ════════════════════════ */

    public function pendingReport(): array
    {
        return MigrationInspector::pendingReport();
    }

    public function hasPendingMigrations(): bool
    {
        return MigrationInspector::hasPending();
    }

    public function runMigrations(): void
    {
        $this->runningMigrations = true;

        [$ok, $output] = MigrationInspector::runMigrations();

        $this->runningMigrations = false;

        if ($ok) {
            session()->flash('admin-developer-flash', 'مایگریشن‌ها با موفقیت اجرا شدند.');
        } else {
            session()->flash('admin-developer-error', 'اجرای مایگریشن‌ها ناموفق بود: '.$output);
        }
    }

    public function render()
    {
        return view('livewire.admin.developer.developer-settings', [
            'cronActive'     => $this->cronActive(),
            'cronLastRun'    => $this->cronLastRun(),
            'cronCommands'   => $this->cronCommands(),
            'pendingMig'     => $this->pendingReport(),
            'hasPendingMig'  => $this->hasPendingMigrations(),
        ])->title('تنظیمات برنامه‌نویس')
            ->layout('layouts.admin-slot', ['breadcrumb' => 'مدیریت کل — برنامه‌نویس — تنظیمات برنامه‌نویس']);
    }
}
