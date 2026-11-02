<?php

namespace App\Support;

use App\Models\AdminNotification;
use App\Models\Setting;
use Throwable;

/**
 * AdminNotifier — one call from anywhere in the app drops a notification
 * for the admin panel (bell + «نوتیفیکیشن‌ها» page) and optionally fans
 * it out as a Web Push so it reaches the admin even when the browser
 * is closed.
 *
 * Event types (each can be switched on/off from
 * تنظیمات سیستم → «نوتیفیکیشن‌ها»):
 *
 *   order.new / order.paid / order.failed / order.status
 *   seller.new / seller.approved / seller.suspended
 *   customer.new
 *   product.new
 *   wallet.withdraw
 *   security.lockout
 *   system.maintenance / system.migration
 */
final class AdminNotifier
{
    /** Registry shown in the settings tab: key => [label fa, icon]. */
    public const EVENTS = [
        'order.new'       => ['سفارش جدید ثبت شد', 'bag'],
        'order.paid'      => ['پرداخت سفارش موفق شد', 'circle-check'],
        'order.failed'    => ['پرداخت سفارش ناموفق بود', 'alert'],
        'order.status'    => ['تغییر وضعیت سفارش', 'truck'],
        'seller.new'      => ['ثبت‌نام فروشنده جدید', 'store'],
        'seller.approved' => ['تایید فروشنده', 'check'],
        'seller.suspended'=> ['تعلیق فروشنده', 'circle-slash'],
        'customer.new'    => ['ثبت‌نام مشتری جدید', 'user'],
        'product.new'     => ['محصول جدید', 'package'],
        'wallet.withdraw' => ['درخواست برداشت کیف پول', 'wallet'],
        'security.lockout'=> ['قفل ورود به پنل (امنیت)', 'shield'],
        'system.maintenance' => ['حالت بروزرسانی سایت', 'alert'],
        'system.migration'   => ['اجرای مایگریشن دیتابیس', 'database'],
    ];

    /**
     * Create an admin notification (respects the per-event toggle) and
     * push it to all subscribed devices when push is enabled. Never throws.
     */
    public static function notify(
        string $type,
        string $title,
        string $body = '',
        ?string $link = null,
        string $icon = 'bell',
        mixed $subject = null,
    ): ?AdminNotification {
        try {
            if (! SchemaReady::check('admin_notifications')) {
                return null;
            }

            // Per-event switch (default ON for every known event).
            if (! (bool) Setting::get('notifications.events.'.self::normalize($type), true)) {
                return null;
            }

            $notification = AdminNotification::create([
                'type'         => self::normalize($type),
                'title'        => mb_substr($title, 0, 290),
                'body'         => mb_substr($body, 0, 990),
                'icon'         => $icon,
                'link'         => $link,
                'subject_type' => $subject instanceof \Illuminate\Database\Eloquent\Model ? $subject->getMorphClass() : null,
                'subject_id'   => $subject instanceof \Illuminate\Database\Eloquent\Model ? $subject->getKey() : null,
                'created_at'   => now(),
            ]);

            // Web Push — best effort, never blocks the request flow.
            if ((bool) Setting::get('notifications.push_enabled', false)) {
                WebPusher::broadcast($title, $body, $link);
            }

            return $notification;
        } catch (Throwable) {
            return null;
        }
    }

    protected static function normalize(string $type): string
    {
        return isset(self::EVENTS[$type]) ? $type : 'system.misc';
    }
}
