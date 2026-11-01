<?php

namespace InstaShop\Installer\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
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

        return $next($request);
    }
}
