<?php

namespace InstaShop\Installer\Support;

use Throwable;

/**
 * Single source of truth for the install lifecycle:
 *
 *   not installed   →  no .env (or no install.lock)  →  wizard is open,
 *                      every other route funnels into /install
 *   installed       →  .env + storage/app/install.lock both exist
 *                      →  wizard is locked, app runs normally
 *
 * The provisional key is a stable, random APP_KEY used while .env does not
 * exist yet (the web-group EncryptCookies middleware needs *some* key to
 * boot). The SAME key is later written into .env, so sessions and CSRF
 * tokens survive the transition and the wizard never breaks mid-install.
 */
class InstallerState
{
    public static function envPath(): string
    {
        return base_path('.env');
    }

    public static function lockPath(): string
    {
        return storage_path('app/install.lock');
    }

    public static function keyPath(): string
    {
        return storage_path('app/installer.key');
    }

    public static function envExists(): bool
    {
        // A zero-byte .env (some hosts create a blank one) counts as absent —
        // the wizard must run fresh and overwrite it.
        return is_file(static::envPath()) && (filesize(static::envPath()) ?: 0) > 0;
    }

    public static function isInstalled(): bool
    {
        return static::envExists() && is_file(static::lockPath());
    }

    /**
     * Stable throwaway APP_KEY for the pre-install phase.
     * Stored once in storage/app (never committed), reused across requests,
     * and finally embedded into .env by writeEnv().
     */
    public static function provisionalKey(): string
    {
        $path = static::keyPath();

        if (! is_file($path)) {
            @mkdir(dirname($path), 0755, true);
            @file_put_contents($path, 'base64:'.base64_encode(random_bytes(32)));
        }

        return (string) file_get_contents($path);
    }

    /**
     * Render the .env stub with the collected database credentials and write
     * it to the project root. Stale cached config/routes are removed so the
     * next request boots with the fresh values.
     */
    public static function writeEnv(array $db, string $appUrl): void
    {
        $stub = (string) file_get_contents(__DIR__.'/../../resources/stubs/env.stub');

        $quote = static fn (?string $v): string => "'".str_replace(['\\', "'"], ['\\\\', "\\'"], (string) $v)."'";

        $env = strtr($stub, [
            '%%APP_KEY%%' => static::provisionalKey(),
            '%%APP_URL%%' => rtrim($appUrl, '/'),
            '%%DB_HOST%%' => $db['db_host'],
            '%%DB_PORT%%' => (string) $db['db_port'],
            '%%DB_DATABASE%%' => $db['db_database'],
            '%%DB_USERNAME%%' => $quote($db['db_username']),
            '%%DB_PASSWORD%%' => $quote($db['db_password'] ?? ''),
        ]);

        @file_put_contents(static::envPath(), $env);

        // Cached config/routes would shadow the fresh .env — drop them.
        @unlink(base_path('bootstrap/cache/config.php'));

        foreach (glob(base_path('bootstrap/cache/routes-*.php')) ?: [] as $file) {
            @unlink($file);
        }
    }

    public static function markInstalled(): void
    {
        @mkdir(dirname(static::lockPath()), 0755, true);

        if (! is_file(static::lockPath())) {
            @file_put_contents(static::lockPath(), date('c'));
        }

        // The provisional key now lives inside .env — remove the copy.
        @unlink(static::keyPath());
    }

    /** Best-effort `php artisan storage:link` equivalent (no shell needed). */
    public static function ensureStorageLink(): bool
    {
        $link = public_path('storage');
        $target = storage_path('app/public');

        if (is_dir($link) || is_link($link)) {
            return true;
        }

        if (! is_dir($target)) {
            @mkdir($target, 0755, true);
        }

        // symlink() is a very common entry in disable_functions on shared
        // hosting — it must never be a fatal error. When unavailable, the
        // app serves /storage/{path} through StorageFallbackController.
        if (! function_exists('symlink')) {
            return false;
        }

        try {
            return @symlink($target, $link) || is_dir($link);
        } catch (Throwable) {
            return false;
        }
    }
}
