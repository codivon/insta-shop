<?php

use App\Http\Middleware\AuthenticatePanel;
use App\Http\Middleware\ApplySecuritySettings;
use App\Http\Middleware\CheckMaintenanceMode;
use App\Http\Middleware\RedirectIfPanelAuthenticated;
use App\Http\Middleware\ResolvePanel;
use App\Http\Middleware\EnsureAdminPermission;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use InstaShop\Installer\Http\Middleware\HandleInstallerAccess;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Resolve the current panel (and its isolated session config)
        // BEFORE StartSession / EncryptCookies run.
        $middleware->prepend(ResolvePanel::class);

        // Installer guard must run BEFORE ResolvePanel: pre-install there is
        // no DB (no panel session tables), and every non-wizard route has to
        // funnel into /install. Registered by the instashop/installer package.
        $middleware->prepend(HandleInstallerAccess::class);

        // Panel-scoped auth middleware (each panel guards with its own guard)
        $middleware->alias([
            'panel.auth' => AuthenticatePanel::class,
            'panel.guest' => RedirectIfPanelAuthenticated::class,
            'admin.permission' => EnsureAdminPermission::class,
        ]);

        // Optional maintenance mode (developer settings) — appended to the
        // web GROUP (not the global stack) so the session is already loaded:
        // the ?secret= bypass must persist in the session.
        // ApplySecuritySettings runs right after it: force-HTTPS redirect,
        // admin-panel IP allow-list and security response headers.
        $middleware->appendToGroup('web', [CheckMaintenanceMode::class, ApplySecuritySettings::class]);

        // Payment gateway callbacks post/redirect from OUTSIDE the app
        // (local test gateway form, bank gateways) — no CSRF token.
        $middleware->validateCsrfTokens(except: [
            'shop/payment/callback',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
