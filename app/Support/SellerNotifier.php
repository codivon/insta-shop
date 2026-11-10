<?php

namespace App\Support;

use App\Models\PushSubscription;
use App\Models\Seller;
use App\Models\SellerNotification;
use App\Models\Setting;
use App\Models\Shop;
use Illuminate\Database\Eloquent\Model;
use Throwable;

/**
 * SellerNotifier — the seller-panel twin of AdminNotifier.
 *
 * One call drops a notification for ONE seller (bell + «نوتیفیکیشن‌ها»
 * page) and optionally fans it out as a Web Push to that seller's
 * registered devices — so it reaches their phone/desktop even when the
 * browser is closed. Sellers only ever see notifications about THEIR
 * shop (orders containing their products, their wallet, their products…).
 *
 * Event types (per-event switch lives in the admin
 * تنظیمات → نوتیفیکیشن‌ها tab, prefix notifications.seller_events.*):
 *
 *   order.new        → سفارش جدید شامل محصولات فروشگاه
 *   order.paid       → پرداخت شد — آماده پردازش/ارسال
 *   order.status     → تغییر وضعیت سفارش
 *   wallet.deposit   → واریز به کیف پول
 *   wallet.withdraw  → وضعیت درخواست برداشت
 *   product.status   → تغییر وضعیت محصول
 *   shop.status      → وضعیت فروشگاه
 *   system.misc      → پیام‌های سیستمی
 */
final class SellerNotifier
{
    /** Registry shown in the admin settings tab: key => [label fa, icon]. */
    public const EVENTS = [
        'order.new' => ['سفارش جدید شامل محصولات شما', 'bag'],
        'order.paid' => ['پرداخت سفارش موفق شد', 'circle-check'],
        'order.status' => ['تغییر وضعیت سفارش', 'truck'],
        'order.canceled' => ['لغو سهم فروشگاه از سفارش', 'circle-x'],
        'wallet.deposit' => ['واریز به کیف پول', 'wallet'],
        'wallet.withdraw' => ['وضعیت درخواست برداشت', 'wallet'],
        'product.status' => ['تغییر وضعیت محصول', 'package'],
        'shop.status' => ['وضعیت فروشگاه', 'store'],
        'story.status' => ['وضعیت استوری فروشگاه', 'zap'],
        'subscription.status' => ['وضعیت اشتراک فروشگاه (فعال‌سازی، یادآوری پایان، لغو)', 'crown'],
        'ticket.reply' => ['پاسخ پشتیبانی به تیکت شما', 'headset'],
        'system.misc' => ['پیام سیستمی', 'bell'],
    ];

    /**
     * Create a seller notification (respects the per-event toggle) and
     * push it to that seller's devices when push is enabled. Never throws.
     */
    public static function notify(
        Seller|int|null $seller,
        string $type,
        string $title,
        string $body = '',
        ?string $link = null,
        string $icon = 'bell',
        mixed $subject = null,
    ): ?SellerNotification {
        try {
            $sellerId = $seller instanceof Seller ? $seller->id : (int) $seller;

            if ($sellerId <= 0) {
                return null;
            }

            if (! SchemaReady::check('seller_notifications')) {
                return null;
            }

            // Per-event switch (default ON for every known event).
            if (! (bool) Setting::get('notifications.seller_events.'.self::normalize($type), true)) {
                return null;
            }

            $notification = SellerNotification::query()->create([
                'seller_id' => $sellerId,
                'type' => self::normalize($type),
                'title' => mb_substr($title, 0, 290),
                'body' => mb_substr($body, 0, 990),
                'icon' => $icon,
                'link' => $link,
                'subject_type' => $subject instanceof Model ? $subject->getMorphClass() : null,
                'subject_id' => $subject instanceof Model ? $subject->getKey() : null,
                'created_at' => now(),
            ]);

            // Web Push — best effort, never blocks the request flow.
            if ((bool) Setting::get('notifications.push_enabled', false)) {
                WebPusher::broadcast($title, $body, $link, PushSubscription::AUDIENCE_SELLER, $sellerId);
            }

            return $notification;
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * Fan a notification out to EVERY seller that owns a shop with items
     * in the given order (used for order.new / order.paid).
     *
     * @param  array<int, int>  $shopIds
     */
    public static function toShops(array $shopIds, string $type, string $title, string $body = '', ?string $link = null, string $icon = 'bell', mixed $subject = null): void
    {
        $sellerIds = Shop::query()
            ->whereIn('id', array_values(array_unique(array_map('intval', $shopIds))))
            ->whereNotNull('seller_id')
            ->pluck('seller_id')
            ->unique();

        foreach ($sellerIds as $sellerId) {
            self::notify((int) $sellerId, $type, $title, $body, $link, $icon, $subject);
        }
    }

    protected static function normalize(string $type): string
    {
        return isset(self::EVENTS[$type]) ? $type : 'system.misc';
    }
}
