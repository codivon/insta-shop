<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Self-healing deploy guard for shared hosts (no SSH deploy pipeline).
 *
 * The host only does "git pull" — nobody runs "php artisan optimize:clear"
 * afterwards, so stale compiled views/config/routes (plus a stubborn OPcache
 * with validate_timestamps=0) kept serving PRE-RESTRUCTURE pages long after
 * the code was updated. The reported «settings pages completely broken on
 * mobile» was exactly this frankenstein state: fresh public/build assets
 * served together with old views.
 *
 * On the first web request after a version bump (config/app.php → 'version'),
 * this middleware:
 *   1. clears every Laravel cache (view, config, route, events, compiled),
 *   2. resets OPcache for the web SAPI — opcache_reset() is per-SAPI, so a
 *      CLI reset never reaches php-fpm, but this runs INSIDE a web request,
 *   3. writes a marker in storage/framework so the heavy path runs only once
 *      per deployment.
 *
 * The active version is rendered in the admin footer and the settings hub
 * («نسخهٔ دیپلوی») — if it does not show the latest release tag after a pull,
 * the web root is not the pulled directory or a server-level page cache
 * (LiteSpeed) is still on.
 */
class RefreshDeploymentCache
{
    public function handle(Request $request, Closure $next): Response
    {
        $version = (string) config('app.version');
        $marker = storage_path('framework/deployed-version');

        if ($version !== '' && (! is_file($marker) || trim((string) file_get_contents($marker)) !== $version)) {
            try {
                Artisan::call('optimize:clear');

                // Reset the WEB server's OPcache (same SAPI as this request).
                // Silently skipped when OPcache is absent or restrict_api is on.
                if (function_exists('opcache_reset')) {
                    @opcache_reset();
                }

                @file_put_contents($marker, $version."\n");

                Log::info('Deployment caches refreshed for v'.$version);
            } catch (\Throwable $e) {
                // Never let the guard break the request — the next one retries.
                Log::warning('Deployment cache refresh failed: '.$e->getMessage());
            }
        }

        return $next($request);
    }
}
