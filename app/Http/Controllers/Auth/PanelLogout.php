<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Support\PanelContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

/**
 * Shared logout for all three panels. The panel is detected from the URI
 * prefix by ResolvePanel, so the correct guard + session is torn down.
 */
class PanelLogout extends Controller
{
    public function __invoke(): RedirectResponse
    {
        $panel = PanelContext::current();
        $guard = $panel->guard() ?? 'customer';

        Auth::guard($guard)->logout();

        session()->invalidate();
        session()->regenerateToken();

        return redirect()->route($panel->loginRoute());
    }
}
