<?php

namespace App\Http\Middleware;

use App\Support\Version;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\View\Compilers\Compiler;
use Symfony\Component\HttpFoundation\Response;

/**
 * Self-healing deploy guard for shared hosts (no SSH deploy pipeline).
 *
 * The host only does "git pull" — nobody runs "php artisan optimize:clear"
 * afterwards, so stale compiled views/config/routes (plus a stubborn OPcache
 * with validate_timestamps=0) kept serving PRE-RESTRUCTURE pages long after
 * the code was updated.
 *
 * v0.17.18 hardening — the v0.17.17 check compared config('app.version')
 * against the marker, but on a host with a cached config
 * (bootstrap/cache/config.php) that value stays frozen at the OLD version,
 * matched the OLD marker, and the refresh silently NEVER ran (the reported
 * «cleared the caches and it is still broken»). The version now comes from
 * Version::code() — read straight from the config/app.php FILE, immune to
 * the config cache.
 *
 * On the first web request after a version bump this middleware:
 *   1. clears every Laravel cache (view, config, route, events, compiled),
 *   2. resets OPcache for the web SAPI — opcache_reset() is per-SAPI, so a
 *      CLI reset never reaches php-fpm, but this runs INSIDE a web request,
 *   3. rotates the compiled-views directory PER RELEASE — with
 *      validate_timestamps=0 AND a blocked opcache_reset() the stale
 *      bytecode of a re-written view wins forever (same file path); files
 *      compiled for THIS release live under a path OPcache has never seen
 *      (deterministic hash of the version → stable across requests),
 *   4. emits «X-LiteSpeed-Purge: *» — a server-level page cache (LSCache on
 *      cPanel) answers cached HTML WITHOUT running PHP, so this response
 *      header is the only way to flush it at the moment new code activates,
 *   5. writes a marker in storage/framework so the heavy path runs only
 *      once per deployment.
 *
 * The active version is rendered in the admin footer and the settings hub
 * («نسخهٔ دیپلوی») — if it does not show the latest release tag after a pull,
 * the web root is not the pulled directory or the pull never happened.
 */
class RefreshDeploymentCache
{
    public function handle(Request $request, Closure $next): Response
    {
        $version = Version::code();
        $marker = storage_path('framework/deployed-version');
        $refreshed = false;

        if ($version !== '' && (! is_file($marker) || trim((string) file_get_contents($marker)) !== $version)) {
            try {
                // Point the Blade compiler at this release's directory BEFORE
                // optimize:clear so view:clear always sees an existing path.
                self::useVersionedViews($version);

                Artisan::call('optimize:clear');

                // Reset the WEB server's OPcache (same SAPI as this request).
                // Silently skipped when OPcache is absent or restrict_api is on.
                if (function_exists('opcache_reset')) {
                    @opcache_reset();
                }

                self::pruneStaleViews($version);

                @file_put_contents($marker, $version."\n");

                Log::info('Deployment caches refreshed for v'.$version);
                $refreshed = true;
            } catch (\Throwable $e) {
                // Never let the guard break the request — the next one retries.
                Log::warning('Deployment cache refresh failed: '.$e->getMessage());
            }
        } else {
            // Stable across requests: same release → same compiled-views dir.
            self::useVersionedViews($version);
        }

        $response = $next($request);

        if ($refreshed) {
            // Flush server-level page caches (LiteSpeed LSCache) exactly once,
            // at the moment the fresh code activates, and make sure THIS
            // response is not stored anywhere either.
            $response->headers->set('X-LiteSpeed-Purge', '*');
            $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        }

        return $response;
    }

    /**
     * Point the Blade compiler at a version-stamped directory. A stale
     * OPcache (validate_timestamps=0 + a blocked opcache_reset) keeps
     * serving the OLD bytecode of a re-written view because the file path
     * never changes — giving every release its own directory makes that
     * impossible. The name is deterministic (hash of the version), so every
     * request of the same deployment compiles into the same directory.
     */
    protected static function useVersionedViews(string $version): void
    {
        if ($version === '') {
            return;
        }

        try {
            // The default compiled directory must always exist — boot-time
            // realpath() of it feeds config('view.compiled') (see pruneStaleViews).
            if (! is_dir(storage_path('framework/views'))) {
                @mkdir(storage_path('framework/views'), 0755, true);
            }

            $versioned = storage_path('framework/views-'.substr(sha1('instashop|views|'.$version), 0, 10));

            if (! is_dir($versioned)) {
                @mkdir($versioned, 0755, true);
            }

            if (! is_dir($versioned)) {
                return;
            }

            config(['view.compiled' => $versioned]);

            // The view engines froze the compiled path in their singletons at
            // BOOT (before any middleware runs) — the config override alone
            // is not enough. Point both compilers at the release directory
            // in place, so this release's views compile into paths the stale
            // OPcache has never seen.
            foreach (['compiler', 'blade.compiler'] as $abstract) {
                if (! app()->bound($abstract)) {
                    continue;
                }

                try {
                    $property = new \ReflectionProperty(
                        Compiler::class, 'cachePath',
                    );
                    $property->setAccessible(true);
                    $property->setValue(app($abstract), $versioned);
                } catch (\ReflectionException) {
                    // Engine internals changed — the config override still applies.
                }
            }
        } catch (\Throwable) {
            // Never break the request — worst case the default path is used.
        }
    }

    /**
     * Remove compiled views of PREVIOUS releases (and the legacy
     * framework/views directory the pre-0.17.18 releases used) — runs once,
     * during a deployment activation, so old bytecode can never be picked
     * up again and disk space stays bounded.
     */
    protected static function pruneStaleViews(string $version): void
    {
        try {
            $current = storage_path('framework/views-'.substr(sha1('instashop|views|'.$version), 0, 10));

            foreach (glob(storage_path('framework/views-*'), GLOB_ONLYDIR) ?: [] as $old) {
                if ($old !== $current) {
                    @File::deleteDirectory($old);
                }
            }

            // The legacy default directory must KEEP EXISTING (just emptied):
            // the view config resolves it with realpath() during BOOT — and
            // realpath() of a missing directory is false, which makes the
            // compiler throw «Please provide a valid cache path» inside
            // Livewire's provider boot, before any middleware can run.
            $legacy = storage_path('framework/views');

            if (is_dir($legacy)) {
                foreach (glob($legacy.'/*') ?: [] as $entry) {
                    if (is_dir($entry)) {
                        @File::deleteDirectory($entry);
                    } else {
                        @unlink($entry);
                    }
                }
            } else {
                @mkdir($legacy, 0755, true);
            }
        } catch (\Throwable) {
            // Housekeeping only — never fail the deployment on it.
        }
    }
}
