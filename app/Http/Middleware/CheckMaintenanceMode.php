<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use App\Support\PanelContext;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use InstaShop\Installer\Support\InstallerState;
use Symfony\Component\HttpFoundation\Response;

/**
 * Optional "site under maintenance" mode (developer settings).
 *
 * When developer.maintenance.enabled is on, every request from the
 * landing / seller / customer panels gets a styled 503 page. Bypasses:
 *  - the admin panel itself (and its Livewire calls),
 *  - requests carrying the secret (?secret=...) — the secret is stored
 *    in the session so subsequent navigation keeps working,
 *  - logged-in admins.
 */
class CheckMaintenanceMode
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! InstallerState::isInstalled() || ! $this->isEnabled()) {
            return $next($request);
        }

        // Secret round-trip: first hit stores the bypass in the session.
        $secret = (string) Setting::get('developer.maintenance.secret', '');

        if ($secret !== '' && hash_equals($secret, (string) $request->query('secret', ''))) {
            session(['maintenance-bypass' => true]);
        }

        if (session('maintenance-bypass')) {
            return $next($request);
        }

        // The admin panel always stays open; its Livewire calls too.
        $panel = null;

        try {
            $panel = app(PanelContext::class)->key();
        } catch (\Throwable) {
            $panel = $request->header('X-Instashop-Panel');
        }

        if ($panel === 'admin' || $request->header('X-Instashop-Panel') === 'admin') {
            return $next($request);
        }

        if (Auth::guard('admin')->check()) {
            return $next($request);
        }

        return response()->view('errors.maintenance503', [
            'title'       => (string) Setting::get('developer.maintenance.title', 'در حال بروزرسانی هستیم!'),
            'description' => (string) Setting::get('developer.maintenance.description', 'به‌زودی برمی‌گردیم. لطفاً چند دقیقه‌ی دیگر مراجعه کنید.'),
        ], 503)->header('Retry-After', 300);
    }

    protected function isEnabled(): bool
    {
        try {
            return (bool) Setting::get('developer.maintenance.enabled');
        } catch (\Throwable) {
            return false; // DB not ready — never block on our own account
        }
    }
}
