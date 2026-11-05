<?php

namespace App\Support;

/**
 * The deployed CODE version — read straight from the config/app.php FILE.
 *
 * Never use config('app.version') to detect a fresh deployment: on shared
 * hosts with a cached config (bootstrap/cache/config.php) that value stays
 * frozen at whatever was current the last time «artisan config:cache» ran,
 * so it can silently equal the deployed-version marker and hide a fresh
 * git pull — exactly the reported «I cleared the caches and the two admin
 * settings pages are STILL broken» state. Reading the file bypasses the
 * config cache entirely. Consumed by RefreshDeploymentCache and by the
 * «نسخهٔ دیپلوی» stamps in the admin footer / settings hub.
 */
class Version
{
    /**
     * The version string stamped in config/app.php, e.g. «0.17.18».
     * Falls back to the (possibly cached) runtime config only when the
     * file itself is unreadable.
     */
    public static function code(): string
    {
        try {
            $source = (string) @file_get_contents(config_path('app.php'));

            if ($source !== '' && preg_match("/['\"]version['\"]\s*=>\s*['\"]([^'\"]+)['\"]/", $source, $m) === 1) {
                return $m[1];
            }
        } catch (\Throwable) {
            // fall through to the runtime config
        }

        return (string) config('app.version');
    }
}
