<?php

namespace App\Http\Middleware;

use App\Support\PanelContext;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Guards a route with the CURRENT PANEL's own auth guard (from PanelContext,
 * set by ResolvePanel). Unauthenticated visitors are sent to that panel's
 * login page. Never mixes guards across panels.
 */
class AuthenticatePanel
{
    public function handle(Request $request, Closure $next): Response
    {
        $panel = PanelContext::current();
        $guard = $panel->guard();

        if ($guard === null || Auth::guard($guard)->check()) {
            return $next($request);
        }

        return redirect()
            ->route($panel->loginRoute())
            ->with('auth-intent', $request->fullUrl());
    }
}
