<?php

use App\Http\Middleware\AuthenticatePanel;
use App\Http\Middleware\RedirectIfPanelAuthenticated;
use App\Http\Middleware\ResolvePanel;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

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

        // Panel-scoped auth middleware (each panel guards with its own guard)
        $middleware->alias([
            'panel.auth' => AuthenticatePanel::class,
            'panel.guest' => RedirectIfPanelAuthenticated::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
