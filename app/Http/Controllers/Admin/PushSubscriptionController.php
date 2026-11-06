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
    /**
     * SECURITY (SSRF): the browser hands us an arbitrary URL — without an
     * allowlist an authenticated panel user could register an INTRANET
     * address (http://10.0.0.5:8080/..., cloud metadata, …) and make the
     * server POST to it on the next broadcast. Only the real Web Push
     * services are accepted.
     */
    protected const PUSH_ENDPOINT_HOSTS = [
        'fcm.googleapis.com',
        'push.googleapis.com',
        'updates.push.services.mozilla.com',
        'web.push.apple.com',
    ];

    public function store(Request $request)
    {
        $data = $request->validate([
            'endpoint' => ['required', 'url', 'max:1000'],
            'keys.p256dh' => ['required', 'string', 'max:400'],
            'keys.auth' => ['required', 'string', 'max:400'],
            'device_label' => ['nullable', 'string', 'max:110'],
        ]);

        if (! $this->endpointAllowed($data['endpoint'])) {
            return response()->json(['ok' => false, 'error' => 'اندپوینت پوش نامعتبر است.'], 422);
        }

        [$audience, $sellerId] = $this->audience();

        try {
            // Upsert by endpoint hash. created_at is only stamped on CREATE —
            // the panel now re-syncs an existing subscription on every page
            // load (state restore), which must not churn the first-seen date.
            $subscription = PushSubscription::query()->firstOrNew(
                ['endpoint_hash' => hash('sha1', $data['endpoint'])],
            );

            $subscription->endpoint = $data['endpoint'];
            $subscription->p256dh = $data['keys']['p256dh'];
            $subscription->auth = $data['keys']['auth'];
            $subscription->user_agent = mb_substr((string) $request->userAgent(), 0, 490);
            $subscription->device_label = $data['device_label'] ?? '';
            $subscription->audience = $audience;
            $subscription->seller_id = $sellerId;

            if (! $subscription->exists) {
                $subscription->created_at = now();
            }

            $subscription->save();
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

        // SECURITY: delete ONLY within the caller's own audience — a seller
        // must never be able to unregister another panel's (or an admin's)
        // device just by knowing the endpoint URL.
        [$audience, $sellerId] = $this->audience();

        PushSubscription::query()
            ->where('endpoint_hash', hash('sha1', $data['endpoint']))
            ->where('audience', $audience)
            ->when($sellerId !== null, fn ($q) => $q->where('seller_id', $sellerId))
            ->delete();

        return response()->json(['ok' => true]);
    }

    /** Scheme + host allowlist for browser push endpoints. */
    protected function endpointAllowed(string $endpoint): bool
    {
        $scheme = strtolower((string) parse_url($endpoint, PHP_URL_SCHEME));
        $host = strtolower((string) parse_url($endpoint, PHP_URL_HOST));

        return $scheme === 'https'
            && $host !== ''
            && in_array($host, self::PUSH_ENDPOINT_HOSTS, true);
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
