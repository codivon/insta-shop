<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PushSubscription;
use Illuminate\Http\Request;
use Throwable;

/**
 * Stores / removes the browser's Web Push subscription for the admin.
 * The JS side (notifications bell) calls this after pushManager.subscribe().
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

        try {
            PushSubscription::updateOrCreate(
                ['endpoint_hash' => hash('sha1', $data['endpoint'])],
                [
                    'endpoint'     => $data['endpoint'],
                    'p256dh'       => $data['keys']['p256dh'],
                    'auth'         => $data['keys']['auth'],
                    'user_agent'   => mb_substr((string) $request->userAgent(), 0, 490),
                    'device_label' => $data['device_label'] ?? '',
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
}
