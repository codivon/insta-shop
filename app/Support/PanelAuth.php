<?php

namespace App\Support;

use App\Models\Seller;
use Illuminate\Auth\SessionGuard;
use Illuminate\Support\Facades\DB;

/**
 * Cross-panel identity helper.
 *
 * The three panels run on fully isolated sessions (own cookie + table) —
 * by design. But a seller who is logged into THEIR panel and then browses
 * the public storefront (/shop/… runs on the customer panel session) must
 * still be recognized: the «بازگشت به پنل فروشندگی» floating button and
 * the owner shortcut on their own shop page depend on it.
 *
 * This resolver reads the SELLER panel's session cookie from the request
 * and looks the session up in the seller_sessions table directly — the
 * same mechanism a normal guard would use, just across panel boundaries.
 */
class PanelAuth
{
    /** Memoized per-request result. */
    private static ?Seller $resolved = null;

    private static bool $resolvedRun = false;

    public static function sellerAcrossPanels(): ?Seller
    {
        if (self::$resolvedRun) {
            return self::$resolved;
        }

        self::$resolvedRun = true;

        // When already inside the seller panel, the normal guard is enough.
        $seller = auth('seller')->user();

        if ($seller instanceof Seller) {
            return self::$resolved = $seller;
        }

        $cookie = (string) config('panels.panels.seller.session.cookie', 'instashop_seller_session');
        $sessionId = (string) request()->cookie($cookie, '');

        if ($sessionId === '' || ! preg_match('/^[a-zA-Z0-9_-]{10,120}$/', $sessionId)) {
            return self::$resolved = null;
        }

        $table = (string) config('panels.panels.seller.session.table', 'seller_sessions');
        $row = DB::table($table)->where('id', $sessionId)->first();

        if (! $row) {
            return self::$resolved = null;
        }

        $payload = json_decode((string) base64_decode((string) $row->payload), true);

        if (! is_array($payload)) {
            return self::$resolved = null;
        }

        $loginKey = 'login_seller_'.sha1(SessionGuard::class);
        $sellerId = (int) ($payload[$loginKey] ?? 0);

        return self::$resolved = $sellerId > 0 ? Seller::query()->find($sellerId) : null;
    }
}
