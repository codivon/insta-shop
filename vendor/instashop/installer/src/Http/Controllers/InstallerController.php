<?php

namespace InstaShop\Installer\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Artisan;
use InstaShop\Installer\Support\InstallerState;
use PDO;
use Throwable;

/**
 * The whole wizard lives in one page (installer::wizard) and talks to the
 * small JSON API below. Everything runs in-process (Artisan::call, PDO,
 * file_put_contents) — NO shell/exec/proc_open, so it works on shared
 * hosting where those functions are disabled.
 */
class InstallerController extends Controller
{
    /* ══════════════════════ Wizard page ══════════════════════ */

    public function welcome()
    {
        $requiredRows = [];
        foreach (config('installer.requirements.required', []) as $label => $ext) {
            $requiredRows[] = ['label' => $label, 'ok' => extension_loaded($ext)];
        }
        $requiredRows[] = [
            'label' => 'نسخه PHP (حداقل '.config('installer.php_min', '8.3').')',
            'ok' => version_compare(PHP_VERSION, (string) config('installer.php_min', '8.3'), '>='),
        ];

        $optionalRows = [];
        foreach (config('installer.requirements.optional', []) as $label => $ext) {
            $optionalRows[] = ['label' => $label, 'ok' => extension_loaded($ext)];
        }

        $permissionRows = [];
        foreach (config('installer.permissions', []) as $label => $path) {
            $permissionRows[] = ['label' => $label, 'path' => $path, 'ok' => is_writable($path)];
        }

        $ready = ! in_array(false, array_column($requiredRows, 'ok'), true)
            && ! in_array(false, array_column($permissionRows, 'ok'), true);

        $css = $this->assetPath('installer.css');
        $js = $this->assetPath('installer.js');

        return view('installer::wizard', [
            'required' => $requiredRows,
            'optional' => $optionalRows,
            'permissions' => $permissionRows,
            'ready' => $ready,
            'resume' => InstallerState::envExists(),
            'migrationCount' => count($this->migrationFiles()),
            'seederCount' => count($this->seederClasses()),
            'phpVersion' => PHP_VERSION,
            'laravelVersion' => app()->version(),
            'assetVersion' => substr(md5(md5_file($css).md5_file($js)), 0, 10),
        ]);
    }

    /* ═════════════ Static assets (no publishing needed) ══════ */

    public function asset(string $file)
    {
        $types = [
            'installer.css' => 'text/css; charset=utf-8',
            'installer.js' => 'application/javascript; charset=utf-8',
        ];

        abort_unless(isset($types[$file]) && is_file($this->assetPath($file)), 404);

        return response((string) file_get_contents($this->assetPath($file)), 200, [
            'Content-Type' => $types[$file],
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    /* ═══════ Step 3: DB credentials → test → write .env ══════ */

    public function saveDatabase(Request $request): JsonResponse
    {
        $data = $request->validate([
            'db_host' => ['required', 'string', 'max:255', 'regex:/^[A-Za-z0-9\.\-_]+$/'],
            'db_port' => ['required', 'integer', 'between:1,65535'],
            'db_database' => ['required', 'string', 'max:64', 'regex:/^[A-Za-z0-9_\-]+$/'],
            'db_username' => ['required', 'string', 'max:64', 'regex:/^[A-Za-z0-9_\-]+$/'],
            'db_password' => ['nullable', 'string', 'max:255'],
        ], [
            'required' => 'فیلد :attribute الزامی است.',
            'regex' => 'مقدار :attribute معتبر نیست.',
            'between' => 'مقدار :attribute باید بین ۱ تا ۶۵۵۳۵ باشد.',
            'max' => 'مقدار :attribute بیش از حد طولانی است.',
            'integer' => 'مقدار :attribute باید عدد باشد.',
        ], [
            'db_host' => 'میزبان دیتابیس',
            'db_port' => 'پورت',
            'db_database' => 'نام دیتابیس',
            'db_username' => 'نام کاربری',
            'db_password' => 'رمز عبور',
        ]);

        if ($error = $this->testMySqlConnection($data)) {
            return response()->json(['ok' => false, 'message' => $error], 422);
        }

        InstallerState::writeEnv($data, $request->getSchemeAndHttpHost());

        return response()->json([
            'ok' => true,
            'message' => 'اتصال موفق بود؛ فایل .env ساخته و کلید برنامه تولید شد.',
        ]);
    }

    /**
     * Probe the connection with raw PDO (independent from Laravel config,
     * since .env does not exist yet). Returns a friendly Persian message
     * or null on success.
     */
    protected function testMySqlConnection(array $d): ?string
    {
        try {
            new PDO(
                "mysql:host={$d['db_host']};port={$d['db_port']};dbname={$d['db_database']};charset=utf8mb4",
                $d['db_username'],
                (string) ($d['db_password'] ?? ''),
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_TIMEOUT => 6],
            );

            return null;
        } catch (Throwable $e) {
            $m = $e->getMessage();

            return match (true) {
                str_contains($m, '1045') || stripos($m, 'Access denied') !== false => 'نام کاربری یا رمز عبور دیتابیس اشتباه است.',
                str_contains($m, '1049') || stripos($m, 'Unknown database') !== false => 'دیتابیسی با این نام پیدا نشد؛ ابتدا آن را در هاست (cPanel) بسازید.',
                str_contains($m, '2002') || str_contains($m, '2003')
                    || stripos($m, 'Connection refused') !== false
                    || stripos($m, 'timed out') !== false => 'اتصال به سرور MySQL برقرار نشد؛ میزبان و پورت را بررسی کنید.',
                default => 'خطا در اتصال به دیتابیس: '.$m,
            };
        }
    }

    /* ══ Steps 4: batched runners — ONE item per AJAX request ══
     *
     * Each request migrates/seed-exactly-one item and reports back, so the
     * pipeline survives any number of tables without PHP timeouts and every
     * failed item can be retried in place (the frontend resumes from the
     * same index). Artisan::call runs in-process — no exec() needed.
     */

    public function runMigrations(Request $request): JsonResponse
    {
        return $this->runBatch('migrations', (int) $request->input('index', 0));
    }

    public function runSeeders(Request $request): JsonResponse
    {
        return $this->runBatch('seeders', (int) $request->input('index', 0));
    }

    protected function runBatch(string $kind, int $index): JsonResponse
    {
        $items = $kind === 'migrations' ? $this->migrationFiles() : $this->seederClasses();
        $total = count($items);

        if ($index < 0 || $index >= $total) {
            return response()->json(['ok' => true, 'finished' => true, 'total' => $total]);
        }

        $label = $kind === 'migrations'
            ? 'database/migrations/'.basename($items[$index])
            : $items[$index];

        try {
            if ($kind === 'migrations') {
                Artisan::call('migrate', [
                    '--path' => 'database/migrations/'.basename($items[$index]),
                    '--force' => true,
                    '--no-interaction' => true,
                ]);
            } else {
                Artisan::call('db:seed', [
                    '--class' => $items[$index],
                    '--force' => true,
                ]);
            }

            return response()->json([
                'ok' => true,
                'finished' => false,
                'total' => $total,
                'message' => $label,
                'detail' => trim(Artisan::output()),
            ]);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'ok' => false,
                'message' => 'اجرای «'.$label.'» ناموفق بود.',
                'detail' => $e->getMessage(),
            ], 500);
        }
    }

    /** @return string[] absolute paths of migration files, sorted */
    protected function migrationFiles(): array
    {
        $files = glob(database_path('migrations').'/*.php') ?: [];
        sort($files);

        return $files;
    }

    /**
     * @return string[] fully-qualified seeder classes, sorted.
     *                  The aggregator (DatabaseSeeder) is excluded because each real
     *                  seeder is run individually (running the aggregator too would
     *                  seed everything twice).
     */
    protected function seederClasses(): array
    {
        $excluded = config('installer.excluded_seeders', ['DatabaseSeeder']);
        $classes = [];

        foreach (glob(database_path('seeders').'/*.php') ?: [] as $file) {
            $class = basename($file, '.php');

            if (in_array($class, $excluded, true)) {
                continue;
            }

            $classes[] = 'Database\\Seeders\\'.$class;
        }

        sort($classes);

        return $classes;
    }

    /* ═════════════ Step 5: lock + storage link + cleanup ═════ */

    public function finish(): JsonResponse
    {
        InstallerState::markInstalled();

        try {
            $storageLinked = InstallerState::ensureStorageLink();
        } catch (Throwable) {
            // symlink() may be disabled on shared hosting — never block
            // completion; the app streams /storage/* via the fallback route.
            $storageLinked = false;
        }

        try {
            Artisan::call('config:clear');
            Artisan::call('route:clear');
            Artisan::call('view:clear');
        } catch (Throwable) {
            // cosmetic only — never block completion
        }

        // If no admin account exists yet, the wizard hands the owner over to
        // the first-run admin registration page (/install/admin-setup).
        $needsAdmin = false;

        try {
            $needsAdmin = \App\Models\Admin::count() === 0;
        } catch (Throwable) {
            // DB hiccup → default to the plain "enter app" CTA
        }

        return response()->json([
            'ok' => true,
            'app_url' => (string) config('app.url'),
            'db_name' => (string) config('database.connections.mysql.database'),
            'storage_linked' => $storageLinked,
            'needs_admin' => $needsAdmin,
        ]);
    }

    /* ══════════════════════ helpers ══════════════════════════ */

    protected function assetPath(string $file): string
    {
        return realpath(__DIR__.'/../../../resources/assets/'.$file) ?: '';
    }
}
