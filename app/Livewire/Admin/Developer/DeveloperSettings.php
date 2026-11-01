<?php

namespace App\Livewire\Admin\Developer;

use App\Livewire\Admin\Concerns\GuardsPermissions;
use App\Models\Setting;
use App\Support\MigrationInspector;
use Illuminate\Support\Facades\Artisan;
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
 *  4. Other         — «تنظیمات دیگر»: development mode (show detailed
 *                     errors), cache clearing tools, and the pending
 *                     migrations section (tables + fields, statically
 *                     parsed) with a one-click "run" button that also
 *                     applies the seeders. The migrations part disappears
 *                     when everything is migrated.
 *  5. Security      — «امنیت»: force HTTPS, admin panel IP allow-list,
 *                     admin login lockout, session lifetime and security
 *                     response headers.
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

    /* ── Other: dev mode / cache ── */
    public bool $devMode = false;

    public bool $clearingCache = false;

    /* ── Migrations ── */
    public bool $runningMigrations = false;

    /* ── Security ── */
    public bool $forceHttps = false;

    public bool $securityHeaders = false;

    public string $adminIpWhitelist = '';

    public int $loginMaxAttempts = 5;

    public int $loginLockoutMinutes = 10;

    public int $sessionLifetime = 120;

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

        /* ── Other ── */
        $this->devMode = (bool) Setting::get('developer.dev_mode');

        /* ── Security ── */
        $this->forceHttps         = (bool) Setting::get('security.force_https');
        $this->securityHeaders    = (bool) Setting::get('security.headers_enabled');
        $this->adminIpWhitelist   = (string) Setting::get('security.admin_ip_whitelist', '');
        $this->loginMaxAttempts   = max(3, (int) Setting::get('security.login_max_attempts', 5));
        $this->loginLockoutMinutes = max(1, (int) Setting::get('security.login_lockout_minutes', 10));
        $this->sessionLifetime    = (int) Setting::get('security.session_lifetime', (int) config('session.lifetime', 120));
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

    /* ══════════════════════ OTHER — DEV MODE ══════════════════════ */

    public function toggleDevMode(): void
    {
        // $this->devMode already holds the NEW value (wire:model synced it
        // before this change-handler ran) — persist it as-is.
        Setting::set('developer.dev_mode', $this->devMode ? '1' : '0', 'boolean');

        // Take effect on this request too.
        config(['app.debug' => $this->devMode]);

        session()->flash('admin-developer-flash', $this->devMode
            ? 'حالت توسعه‌ای فعال شد — از این پس جزئیات کامل خطاها نمایش داده می‌شود. پس از رفع مشکل آن را خاموش کنید.'
            : 'حالت توسعه‌ای غیرفعال شد — خطاها به‌صورت کلی نمایش داده می‌شوند.');
    }

    /* ══════════════════════ OTHER — CACHE ══════════════════════ */

    /**
     * Clear server-side caches. $which: app|config|view|route|all.
     */
    public function clearCache(string $which = 'all'): void
    {
        $this->clearingCache = true;

        $which = in_array($which, ['app', 'config', 'view', 'route', 'all'], true) ? $which : 'all';

        $commands = match ($which) {
            'app'    => ['cache:clear'],
            'config' => ['config:clear'],
            'view'   => ['view:clear'],
            'route'  => ['route:clear'],
            default  => ['cache:clear', 'config:clear', 'view:clear', 'route:clear'],
        };

        $labels = [
            'cache:clear'  => 'کش برنامه',
            'config:clear' => 'کش پیکربندی',
            'view:clear'   => 'کش نماها',
            'route:clear'  => 'کش مسیرها',
        ];

        $done = [];

        try {
            @set_time_limit(120);

            foreach ($commands as $command) {
                Artisan::call($command);
                $done[] = $labels[$command];
            }
        } catch (\Throwable $e) {
            $this->clearingCache = false;

            session()->flash('admin-developer-error', 'پاک‌سازی کش ناموفق بود: '.$e->getMessage());

            return;
        }

        // Settings cache is always refreshed too.
        Cache::forget('instashop.settings');

        $this->clearingCache = false;

        session()->flash('admin-developer-flash', 'پاک‌سازی انجام شد: '.implode('، ', $done).'.');
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
            session()->flash('admin-developer-flash', 'مایگریشن‌ها و سیدرهای جدید با موفقیت اجرا شدند.');
        } else {
            session()->flash('admin-developer-error', 'اجرای مایگریشن‌ها ناموفق بود: '.$output);
        }
    }

    /* ═════════════════════════ SECURITY ═════════════════════════ */

    /** Current visitor IP — offered as a one-click addition to the allow-list. */
    public function currentIp(): string
    {
        return (string) request()->ip();
    }

    /** One-click: put my current IP into the allow-list textarea. */
    public function addMyIp(): void
    {
        $ip = $this->currentIp();

        if ($ip === '') {
            return;
        }

        $lines = array_values(array_filter(array_map('trim', preg_split('/[\r\n]+/', $this->adminIpWhitelist) ?: []), fn ($l) => $l !== ''));

        if (! in_array($ip, $lines, true)) {
            $lines[] = $ip;
        }

        $this->adminIpWhitelist = implode("\n", $lines);
    }

    public function saveSecurity(): void
    {
        $this->validate([
            'adminIpWhitelist'    => ['nullable', 'string', 'max:2000', 'regex:/^[\d\.\*\r\n\s,;]+$/'],
            'loginMaxAttempts'    => ['required', 'integer', 'min:3', 'max:10'],
            'loginLockoutMinutes' => ['required', 'integer', 'min:1', 'max:120'],
            'sessionLifetime'     => ['required', 'integer', 'in:30,60,120,240,480,720,1440'],
        ], [
            'adminIpWhitelist.regex'    => 'هر خط فقط می‌تواند یک آی‌پی معتبر یا الگوی با ستاره باشد (مثال: 5.190.*.*).',
            'loginMaxAttempts.required' => 'حداکثر تلاش ورود را وارد کنید.',
            'sessionLifetime.in'        => 'مدت اعتبار ورود نامعتبر است.',
        ]);

        $list = trim((string) $this->adminIpWhitelist);

        // Fail-safe: never let the admin lock HIMSELF out of the panel.
        if ($list !== '') {
            $allowed = collect(preg_split('/[\r\n,;]+/', $list) ?: [])
                ->map(fn ($l) => trim($l))
                ->filter(fn ($l) => $l !== '');

            $myIp = $this->currentIp();

            if ($myIp !== '' && ! $allowed->contains(fn ($pattern) => $pattern === $myIp || \Illuminate\Support\Str::is($pattern, $myIp))) {
                session()->flash('admin-developer-error', 'ذخیره نشد! آی‌پی فعلی شما ('.$myIp.') در لیست مجاز نیست — ابتدا آن را با دکمه «افزودن IP من» اضافه کنید وگرنه خودتان از پنل قفل می‌شوید.');

                return;
            }
        }

        Setting::setMany([
            'security.force_https'         => ['value' => $this->forceHttps ? '1' : '0', 'type' => 'boolean'],
            'security.headers_enabled'     => ['value' => $this->securityHeaders ? '1' : '0', 'type' => 'boolean'],
            'security.admin_ip_whitelist'  => ['value' => $list, 'type' => 'text'],
            'security.login_max_attempts'  => ['value' => (string) $this->loginMaxAttempts, 'type' => 'integer'],
            'security.login_lockout_minutes' => ['value' => (string) $this->loginLockoutMinutes, 'type' => 'integer'],
            'security.session_lifetime'    => ['value' => (string) $this->sessionLifetime, 'type' => 'integer'],
        ]);

        // Apply immediately to the running request as well.
        config(['session.lifetime' => $this->sessionLifetime]);

        session()->flash('admin-developer-flash', 'تنظیمات امنیت ذخیره و اعمال شد.');
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
