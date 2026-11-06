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
 *
 * SECURITY (v0.21.0): a session is re-checked against the account's CURRENT
 * status on every request — admin blocks/suspends used to leave the existing
 * session fully alive for the whole panel lifetime (customer 30 days /
 * seller 7 days) because the status was only read at OTP login.
 */
class AuthenticatePanel
{
    public function handle(Request $request, Closure $next): Response
    {
        $panel = PanelContext::current();
        $guard = $panel->guard();

        if ($guard !== null && ! Auth::guard($guard)->check()) {
            return redirect()
                ->route($panel->loginRoute())
                ->with('auth-intent', $request->fullUrl());
        }

        if ($guard !== null && $this->accountDisqualified($guard)) {
            Auth::guard($guard)->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route($panel->loginRoute())
                ->with('auth-intent', $request->fullUrl());
        }

        return $next($request);
    }

    /** Blocked customers / suspended sellers lose live sessions immediately. */
    private function accountDisqualified(string $guard): bool
    {
        $user = Auth::guard($guard)->user();

        if ($user === null) {
            return false;
        }

        return match ($guard) {
            'customer' => (string) $user->status === 'blocked',
            'seller' => (string) $user->status !== 'approved',
            default => false,
        };
    }
}
