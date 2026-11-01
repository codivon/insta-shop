<?php

namespace App\Http\Middleware;

use App\Models\Cart;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use InstaShop\Installer\Support\InstallerState;
use Symfony\Component\HttpFoundation\Response;

/**
 * Hands a guest cart over to its owner the moment the customer guard can
 * resolve a logged-in customer. Covers BOTH flows without any special
 * casing:
 *   - right after login (PhoneLogin) — the next customer-panel request
 *     merges the cart the visitor built while browsing as a guest;
 *   - an already-logged-in customer that still carries the cookie.
 *
 * The guest cart lives behind a plain shared domain cookie (NOT a panel
 * session), so it survives the storefront ⇄ customer-app panel switch.
 */
class MergeGuestCart
{
    public function handle(Request $request, Closure $next): Response
    {
        if (InstallerState::isInstalled()) {
            $token = Cart::guestTokenFromRequest();

            if ($token !== '' && ($customer = auth('customer')->user())) {
                Cart::mergeGuestIntoCustomer($token, $customer);

                // Merged → the cookie has no purpose anymore.
                Cookie::expire(Cart::GUEST_COOKIE);
            }
        }

        return $next($request);
    }
}
