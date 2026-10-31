<?php

namespace App\Http\Middleware;

use App\Support\PanelContext;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Login pages only: already-authenticated users of the current panel are
 * bounced straight to the panel home instead of seeing the login form.
 */
class RedirectIfPanelAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        $panel = PanelContext::current();
        $guard = $panel->guard();

        if ($guard !== null && Auth::guard($guard)->check()) {
            return redirect()->route($panel->homeRoute());
        }

        return $next($request);
    }
}
