<?php

namespace InstaShop\Installer\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use InstaShop\Installer\Http\Controllers\AdminSetupController;
use InstaShop\Installer\Support\InstallerState;
use Symfony\Component\HttpFoundation\Response;

/**
 * Global installer guard (prepended BEFORE ResolvePanel — see bootstrap/app.php):
 *
 *  - App not installed → every route except /install (and the /up health
 *    probe) is funneled into the wizard. This covers landing, admin, seller,
 *    customer panels AND Livewire endpoints — no per-route wiring needed.
 *  - App installed → /install is locked (redirects home) so the wizard can
 *    never be re-run by an attacker or a curious user. The single exception
 *    is /install/admin-setup — the first-run admin registration — which
 *    stays reachable ONLY while the admins table is still empty; once the
 *    first admin exists it, too, redirects home.
 */
class HandleInstallerAccess
{
    /** Cookie that mirrors the server-side installer session token. */
    private const SESSION_COOKIE = 'instashop_install';

    public function handle(Request $request, Closure $next): Response
    {
        $installed = InstallerState::isInstalled();

        if ($installed && $request->is('install', 'install/*')) {
            // First-run admin setup stays open until an admin exists.
            if ($request->is('install/admin-setup') && ! AdminSetupController::adminExists()) {
                return $next($request);
            }

            return redirect()->to('/');
        }

        if (! $installed && ! $request->is('install', 'install/*') && ! $request->is('up')) {
            return redirect()->to('/install');
        }

        // ═══════════════════════════════════════════════════════════
        // SECURITY (v0.20.0) — installer session binding.
        // While the app is NOT installed, mutating installer requests
        // must come from the browser session that STARTED the wizard:
        // every GET of the wizard (re)issues the HttpOnly cookie, every
        // POST/PUT/PATCH/DELETE under /install/* must match it. This
        // closes the .env-rewrite window on a discoverable host.
        // ═══════════════════════════════════════════════════════════
        if ($request->is('install', 'install/*')) {
            if ($request->isMethod('GET')) {
                $token = InstallerState::ensureSessionToken();

                Cookie::queue(Cookie::make(
                    self::SESSION_COOKIE,
                    $token,
                    60 * 8,          // 8 hours — plenty for one install run
                    path: '/',
                    httpOnly: true,
                    sameSite: 'Lax',
                ));
            } elseif (! InstallerState::sessionTokenValid($request->cookie(self::SESSION_COOKIE))) {
                return response()->json([
                    'message' => 'نشست نصب معتبر نیست — صفحهٔ نصب را دوباره باز کنید.',
                ], 403);
            }
        }

        return $next($request);
    }
}
