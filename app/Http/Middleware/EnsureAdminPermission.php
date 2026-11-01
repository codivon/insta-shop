<?php

namespace App\Http\Middleware;

use App\Models\Admin;
use App\Support\AdminPermissions;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Page-level permission enforcement for the admin panel.
 *
 * The required permission is the matched route's name. Super admins
 * (is_super) always pass. Non-super admins need the permission granted
 * through one of their roles — and routes NOT registered in
 * config/admin-permissions.php fail closed (super only).
 */
class EnsureAdminPermission
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Admin|null $admin */
        $admin = auth('admin')->user();

        if ($admin === null) {
            return redirect()->route('admin.login');
        }

        if ($admin->isSuper()) {
            return $next($request);
        }

        $routeName = (string) $request->route()?->getName();

        // Logout is always allowed (no permission registered for it).
        if ($routeName === 'admin.logout') {
            return $next($request);
        }

        if ($routeName !== '' && $admin->hasPermission($routeName)) {
            return $next($request);
        }

        // The settings HUB («admin/settings») is one page whose sections
        // carry their own permissions — grant entry when the admin can open
        // at least ONE section inside it (tabs stay individually guarded).
        if ($routeName === 'admin.settings' && collect([
            'admin.settings.general',
            'admin.settings.appearance',
            'admin.settings.sms',
            'admin.settings.payment',
        ])->contains(fn (string $p) => $admin->hasPermission($p))) {
            return $next($request);
        }

        // The SMS template center is a standalone page reached from the
        // «پیامک و پرووایدر» section — the SMS section permission also
        // unlocks it (its dedicated permission is optional but honored).
        if ($routeName === 'admin.settings.sms.templates'
            && $admin->hasPermission('admin.settings.sms')) {
            return $next($request);
        }

        return response()->view('errors.admin-403', [
            'permission' => $routeName !== '' ? AdminPermissions::label($routeName) : null,
        ], 403);
    }
}
