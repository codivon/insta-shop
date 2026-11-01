<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use App\Support\PanelContext;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use InstaShop\Installer\Support\InstallerState;
use Symfony\Component\HttpFoundation\Response;

/**
 * Runtime enforcement of the Developer Settings → «امنیت» tab:
 *
 *  1. Force HTTPS      — redirects every GET/HEAD request that arrives
 *                        over plain HTTP to its https URL (never on
 *                        localhost/127.0.0.1 so local development and
 *                        the sandbox keep working).
 *  2. Admin IP gate    — when security.admin_ip_whitelist is non-empty,
 *                        the admin panel (and its Livewire calls) answer
 *                        only to allow-listed IPs (exact match or
 *                        wildcard patterns like 5.190.*.*).
 *  3. Security headers — X-Frame-Options, X-Content-Type-Options,
 *                        Referrer-Policy, Permissions-Policy (+ HSTS
 *                        while force-HTTPS is on).
 *
 * Appended to the `web` group AFTER the session middleware, mirroring
 * CheckMaintenanceMode. Every DB read is guarded so pre-install requests
 * (installer, health check) can never be blocked by our own settings.
 */
class ApplySecuritySettings
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! InstallerState::isInstalled()) {
            return $next($request);
        }

        try {
            /* ── 1) Force HTTPS ── */
            if ($this->bool('security.force_https')
                && ! $this->requestIsSecure($request)
                && $this->isRedirectable($request)) {
                return redirect()->to('https://'.$request->getHost().$request->getRequestUri(), 302);
            }

            /* ── 2) Admin panel IP allow-list ── */
            if ($this->isAdminRequest($request)) {
                $list = trim((string) Setting::get('security.admin_ip_whitelist', ''));

                if ($list !== '' && ! $this->ipAllowed((string) $request->ip(), $list)) {
                    return response()->view('errors.ip-403', ['ip' => (string) $request->ip()], 403);
                }
            }
        } catch (\Throwable) {
            // Settings table not ready yet — never block on our own account.
            return $next($request);
        }

        /** @var Response $response */
        $response = $next($request);

        /* ── 3) Security response headers ── */
        try {
            if ($this->bool('security.headers_enabled')) {
                $headers = $response->headers;

                $headers->set('X-Frame-Options', 'SAMEORIGIN');
                $headers->set('X-Content-Type-Options', 'nosniff');
                $headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
                $headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

                if ($this->bool('security.force_https') && $this->requestIsSecure($request)) {
                    $headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
                }
            }
        } catch (\Throwable) {
            // headers are best-effort
        }

        return $response;
    }

    /* ----------------------------- internals ----------------------------- */

    protected function bool(string $key): bool
    {
        return (bool) Setting::get($key);
    }

    /** Secure already, or the proxy in front of us already upgraded to https. */
    protected function requestIsSecure(Request $request): bool
    {
        if ($request->isSecure()) {
            return true;
        }

        return strtolower((string) $request->header('X-Forwarded-Proto', '')) === 'https';
    }

    /** Only browser-navigable, non-local requests get the https redirect. */
    protected function isRedirectable(Request $request): bool
    {
        if (! in_array($request->getMethod(), ['GET', 'HEAD'], true)) {
            return false;
        }

        $host = strtolower((string) $request->getHost());

        return ! in_array($host, ['localhost', '127.0.0.1', '::1'], true);
    }

    /** Mirrors CheckMaintenanceMode's admin detection (panel ctx or header). */
    protected function isAdminRequest(Request $request): bool
    {
        $panel = null;

        try {
            $panel = app(PanelContext::class)->key();
        } catch (\Throwable) {
            $panel = $request->header('X-Instashop-Panel');
        }

        return $panel === 'admin' || $request->header('X-Instashop-Panel') === 'admin';
    }

    /** One entry per line (also accepts , or ;) — exact IP or wildcard pattern. */
    protected function ipAllowed(string $ip, string $list): bool
    {
        $ip = trim($ip);

        if ($ip === '') {
            return false;
        }

        foreach (preg_split('/[\r\n,;]+/', $list) ?: [] as $line) {
            $pattern = trim($line);

            if ($pattern === '') {
                continue;
            }

            if ($pattern === $ip || Str::is($pattern, $ip)) {
                return true;
            }
        }

        return false;
    }
}
