<?php

namespace App\Support;

use App\Models\PushSubscription;
use App\Models\Setting;
use Minishlink\WebPush\MessageSentReport;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\VAPID;
use Minishlink\WebPush\WebPush;
use Throwable;

/**
 * WebPusher — real Web Push (VAPID) so admin notifications arrive on the
 * phone/desktop even when the browser is closed.
 *
 *  • VAPID keys are generated once and stored in the settings table;
 *    they can be regenerated from تنظیمات → نوتیفیکیشن‌ها.
 *  • The browser side subscribes through /sw.js (service worker) and the
 *    subscription endpoint lands in push_subscriptions.
 *  • broadcast() sends the payload to every device; stale endpoints
 *    (404/410) are removed automatically.
 *
 * Everything is fail-safe: a push failure can never break the request.
 */
final class WebPusher
{
    /**
     * VAPID keys from settings (generated on first use).
     *
     * @return array{publicKey: string, privateKey: string}
     */
    public static function keys(): array
    {
        $public = (string) Setting::get('notifications.vapid_public', '');
        $private = (string) Setting::get('notifications.vapid_private', '');

        if ($public === '' || $private === '') {
            $generated = VAPID::createVapidKeys();

            Setting::setMany([
                'notifications.vapid_public' => ['value' => $generated['publicKey'], 'type' => 'text'],
                'notifications.vapid_private' => ['value' => $generated['privateKey'], 'type' => 'text'],
            ]);

            return $generated;
        }

        return ['publicKey' => $public, 'privateKey' => $private];
    }

    /** Delete + regenerate the VAPID pair (devices must re-subscribe). */
    public static function regenerateKeys(): void
    {
        $generated = VAPID::createVapidKeys();

        Setting::setMany([
            'notifications.vapid_public' => ['value' => $generated['publicKey'], 'type' => 'text'],
            'notifications.vapid_private' => ['value' => $generated['privateKey'], 'type' => 'text'],
        ]);
    }

    /** Public key for the browser subscribe call. */
    public static function publicKey(): string
    {
        try {
            return self::keys()['publicKey'];
        } catch (Throwable) {
            return '';
        }
    }

    /**
     * Send a push to every subscribed device of ONE panel audience.
     *
     * @param  string|null  $audience  'admin' | 'seller' | null (legacy = every device)
     * @param  int|null  $sellerId  when audience='seller': only this seller's devices
     * @return int number of deliveries attempted successfully
     */
    public static function broadcast(string $title, string $body = '', ?string $url = null, ?string $audience = null, ?int $sellerId = null): int
    {
        try {
            if (! SchemaReady::check('push_subscriptions')) {
                return 0;
            }

            $subscriptions = PushSubscription::query()
                ->when($audience !== null, fn ($q) => $q->where('audience', $audience))
                ->when($audience === PushSubscription::AUDIENCE_SELLER && $sellerId !== null, fn ($q) => $q->where('seller_id', $sellerId))
                ->get();

            if ($subscriptions->isEmpty()) {
                return 0;
            }

            $keys = self::keys();

            $webPush = new WebPush([
                'VAPID' => [
                    'subject' => 'mailto:support@opashop.ir',
                    'publicKey' => $keys['publicKey'],
                    'privateKey' => $keys['privateKey'],
                ],
                'TTL' => 3600,
                'urgency' => 'high',
                'batchSize' => 50,
            ]);

            $payload = json_encode([
                'title' => $title,
                'body' => $body,
                'url' => $url,
            ], JSON_UNESCAPED_UNICODE) ?: '{}';

            $sent = 0;

            /** @var PushSubscription $sub */
            foreach ($subscriptions as $sub) {
                try {
                    $webPush->queueNotification(
                        Subscription::create([
                            'endpoint' => $sub->endpoint,
                            'keys' => ['p256dh' => $sub->p256dh, 'auth' => $sub->auth],
                            'clientEncoding' => 'aes128gcm',
                        ]),
                        $payload,
                    );
                } catch (Throwable) {
                    // malformed subscription — drop it
                    $sub->delete();
                }
            }

            /** @var MessageSentReport $report */
            foreach ($webPush->flush() as $report) {
                $endpoint = (string) $report->getEndpoint();

                $row = PushSubscription::query()
                    ->whereRaw('LOWER(SHA1(?)) = endpoint_hash', [$endpoint])
                    ->first();

                if ($report->isSuccess()) {
                    $sent++;

                    $row?->forceFill(['last_used_at' => now()])->save();
                } elseif (in_array($report->getResponse()?->getStatusCode(), [404, 410], true)) {
                    // subscription expired/removed by the browser — clean up
                    $row?->delete();
                }
            }

            return $sent;
        } catch (Throwable) {
            return 0;
        }
    }
}
