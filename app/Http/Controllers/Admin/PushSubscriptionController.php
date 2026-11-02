<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PushSubscription;
use App\Models\Seller;
use Illuminate\Http\Request;
use Throwable;

/**
 * Stores / removes the browser's Web Push subscription for the current
 * panel user. The JS side (notifications bell / page) calls this after
 * pushManager.subscribe().
 *
 * Guard-aware: on the seller panel the device is stored scoped to the
 * logged-in seller (audience=seller) so a seller never receives another
 * panel's pushes; on the admin panel devices stay global (audience=admin).
 */
class PushSubscriptionController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'endpoint'     => ['required', 'url', 'max:1000'],
            'keys.p256dh'  => ['required', 'string', 'max:400'],
            'keys.auth'    => ['required', 'string', 'max:400'],
            'device_label' => ['nullable', 'string', 'max:110'],
        ]);

        [$audience, $sellerId] = $this->audience();

        try {
            PushSubscription::updateOrCreate(
                ['endpoint_hash' => hash('sha1', $data['endpoint'])],
                [
                    'endpoint'     => $data['endpoint'],
                    'p256dh'       => $data['keys']['p256dh'],
                    'auth'         => $data['keys']['auth'],
                    'user_agent'   => mb_substr((string) $request->userAgent(), 0, 490),
                    'device_label' => $data['device_label'] ?? '',
                    'audience'     => $audience,
                    'seller_id'    => $sellerId,
                    'created_at'   => now(),
                ],
            );
        } catch (Throwable $e) {
            return response()->json(['ok' => false, 'error' => $e->getMessage()], 500);
        }

        return response()->json(['ok' => true]);
    }

    public function destroy(Request $request)
    {
        $data = $request->validate([
            'endpoint' => ['required', 'url', 'max:1000'],
        ]);

        PushSubscription::query()
            ->where('endpoint_hash', hash('sha1', $data['endpoint']))
            ->delete();

        return response()->json(['ok' => true]);
    }

    /**
     * Which panel is the visitor on? Admin session wins (a user could in
     * theory be logged into both); otherwise a seller session scopes the
     * device to that seller.
     *
     * @return array{0: string, 1: int|null} [audience, sellerId]
     */
    protected function audience(): array
    {
        if (auth('admin')->check()) {
            return [PushSubscription::AUDIENCE_ADMIN, null];
        }

        if (auth('seller')->check()) {
            $sellerId = (int) auth('seller')->id();

            // belt & braces: only real sellers get a scoped subscription
            if ($sellerId > 0 && Seller::query()->whereKey($sellerId)->exists()) {
                return [PushSubscription::AUDIENCE_SELLER, $sellerId];
            }
        }

        return [PushSubscription::AUDIENCE_ADMIN, null];
    }
}
