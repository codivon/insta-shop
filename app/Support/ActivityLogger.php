<?php

namespace App\Support;

use App\Models\ActivityLog;
use App\Models\Setting;
use Illuminate\Database\Eloquent\Model;
use Throwable;

/**
 * ActivityLogger — records «فعالیت‌های اخیر» (the admin audit trail).
 *
 * Every call stores WHO did it (admin / seller / customer / system, resolved
 * from the active panel session), WHAT happened (a persian sentence) and —
 * for edits — the before/after values, so the admin can see exactly what a
 * setting was before someone changed it.
 *
 * Design rule: logging must NEVER break the request. Everything is wrapped
 * and any failure is silently swallowed (the DB write is best-effort).
 *
 * Usage:
 *   ActivityLogger::log('settings.updated', 'تنظیمات عمومی ویرایش شد', subject: $model,
 *                       before: ['title' => 'old'], after: ['title' => 'new']);
 */
final class ActivityLogger
{
    /** @var array<string, string> persian labels for known setting keys (diff rows) */
    public const KEY_LABELS = [
        'app.site_title' => 'عنوان سایت',
        'app.site_description' => 'توضیح سایت',
        'app.support_phone' => 'تلفن پشتیبانی',
        'appearance.palette' => 'پالت رنگی',
        'appearance.primary_color' => 'رنگ اصلی',
        'appearance.default_mode' => 'حالت پیش‌فرض (روشن/تیره)',
        'appearance.logo' => 'لوگو',
        'appearance.favicon' => 'فاوآیکون',
        'sms.provider' => 'پروایدر پیامک',
        'sms.api_key' => 'کلید API پیامک',
        'sms.sender' => 'شماره فرستنده',
        'payment.gateway' => 'درگاه پرداخت',
        'payment.payping.split_enabled' => 'پرداخت تسهیمی پی‌پینگ (واریز مستقیم به فروشنده)',
        'payment.asanpardakht.split_enabled' => 'پرداخت تسهیمی آسان‌پرداخت (واریز مستقیم به فروشنده)',
        'payment.zarinpal.merchantId' => 'مرچنت زرین‌پال',
        'payment.zibal.merchantId' => 'مرچنت زیبال',
        'payment.novinopay.merchantId' => 'مرچنت نوین‌پی',
        'payment.payping.merchantId' => 'کد اختصاصی پی‌پینگ',
        'payment.sadad.key' => 'کلید سداد',
        'payment.sadad.merchantId' => 'مرچنت سداد',
        'payment.sadad.terminalId' => 'ترمینال سداد',
        'payment.behpardakht.terminalId' => 'ترمینال به‌پرداخت',
        'payment.behpardakht.username' => 'نام کاربری به‌پرداخت',
        'payment.behpardakht.password' => 'رمز عبور به‌پرداخت',
        'payment.parsian.merchantId' => 'مرچنت پارسیان',
        'payment.irankish.terminalId' => 'ترمینال ایران‌کیش',
        'payment.irankish.password' => 'رمز عبور ایران‌کیش',
        'payment.irankish.acceptorId' => 'شناسه پذیرنده ایران‌کیش',
        'payment.irankish.pubKey' => 'کلید عمومی ایران‌کیش',
        'payment.pasargad.userName' => 'نام کاربری پاسارگاد',
        'payment.pasargad.password' => 'رمز عبور پاسارگاد',
        'payment.pasargad.terminalCode' => 'کد ترمینال پاسارگاد',
        'payment.asanpardakht.username' => 'نام کاربری آسان‌پرداخت',
        'payment.asanpardakht.password' => 'رمز عبور آسان‌پرداخت',
        'payment.asanpardakht.merchantConfigID' => 'پیکربندی پذیرندهٔ آسان‌پرداخت',
        'payment.sepehr.terminalId' => 'ترمینال سپهر',
        'payment.sep.terminalId' => 'ترمینال سپ الکترونیک',
        'shops.auto_approve_seller' => 'تایید خودکار فروشنده',
        'shops.commission_type' => 'نوع کمیسیون',
        'shops.commission_value' => 'مقدار کمیسیون',
        'shops.subscription_trial_days' => 'دورهٔ آزمایشی اشتراک (روز)',
        'developer.queue_shared_hosting' => 'صف با دو کرون‌جاب',
        'developer.maintenance.enabled' => 'حالت بروزرسانی سایت',
        'developer.maintenance.secret' => 'کد مخفی بروزرسانی',
        'developer.maintenance.title' => 'عنوان صفحه بروزرسانی',
        'developer.maintenance.description' => 'توضیح صفحه بروزرسانی',
        'developer.dev_mode' => 'حالت توسعه‌ای',
        'security.force_https' => 'اجرای اجباری HTTPS',
        'security.headers_enabled' => 'هدرهای امنیتی',
        'security.bot_blocking' => 'مسدودسازی ربات‌ها',
        'security.bot_deny_list' => 'لیست ربات‌های مسدود',
        'security.admin_ip_whitelist' => 'لیست سفید آی‌پی پنل',
        'security.login_max_attempts' => 'حداکثر تلاش ورود',
        'security.login_lockout_minutes' => 'مدت قفل ورود (دقیقه)',
        'security.session_lifetime' => 'مدت اعتبار ورود (دقیقه)',
        'notifications.push_enabled' => 'نوتیفیکیشن پوش',
        'APP_ENV' => 'محیط برنامه',
        'APP_DEBUG' => 'حالت نمایش خطا',
    ];

    /** Persist one activity entry (never throws). */
    public static function log(
        string $action,
        string $title,
        ?Model $subject = null,
        array $before = [],
        array $after = [],
    ): void {
        try {
            if (! SchemaReady::check('activity_logs')) {
                return;
            }

            $actor = self::currentActor();

            ActivityLog::create([
                'actor_type' => $actor['type'],
                'actor_id' => $actor['id'],
                'actor_name' => $actor['name'],
                'action' => $action,
                'title' => mb_substr($title, 0, 490),
                'subject_type' => $subject ? $subject->getMorphClass() : null,
                'subject_id' => $subject?->getKey(),
                // Empty arrays stay NULL so the diff block can be skipped in UI.
                'before' => $before === [] ? null : $before,
                'after' => $after === [] ? null : $after,
                'ip' => self::requestIp(),
                'user_agent' => mb_substr((string) request()->userAgent(), 0, 490),
                'created_at' => now(),
            ]);
        } catch (Throwable) {
            // never break the request because of logging
        }
    }

    /** @return array{type: string, id: ?int, name: string} */
    public static function currentActor(): array
    {
        foreach (['admin', 'seller', 'customer'] as $guard) {
            try {
                $user = auth($guard)->user();
            } catch (Throwable) {
                $user = null;
            }

            if ($user) {
                $name = (string) ($user->name
                    ?? $user->instagram_username
                    ?? $user->phone
                    ?? $user->email
                    ?? '');

                return ['type' => $guard, 'id' => $user->getKey(), 'name' => $name ?: $guard];
            }
        }

        return ['type' => 'system', 'id' => null, 'name' => 'سیستم'];
    }

    /**
     * Capture the current values of the given setting keys — call BEFORE
     * Setting::setMany so the diff has the real "before".
     */
    public static function settingsSnapshot(array $keys): array
    {
        $out = [];

        try {
            foreach ($keys as $key) {
                $out[$key] = Setting::get($key);
            }
        } catch (Throwable) {
            // best effort
        }

        return $out;
    }

    /**
     * Log a settings change with a clean before/after diff of exactly the
     * keys that changed. Sensitive values (API keys, merchants, secrets)
     * are masked so they never land in the log.
     *
     * @param  array  $snapshot  result of settingsSnapshot() taken BEFORE the write
     * @param  array  $pairs  the exact pairs passed to Setting::setMany
     */
    public static function logSettingsChanged(string $title, array $snapshot, array $pairs): void
    {
        try {
            $before = [];
            $after = [];

            foreach ($pairs as $key => $value) {
                $newValue = is_array($value) ? ($value['value'] ?? null) : $value;
                $oldValue = $snapshot[$key] ?? null;

                if ((string) $oldValue === (string) $newValue) {
                    continue; // untouched — keep the log readable
                }

                if (self::isSensitiveKey($key)) {
                    $before[$key] = $oldValue === null || $oldValue === '' ? '(خالی)' : '••••••••';
                    $after[$key] = $newValue === null || $newValue === '' ? '(خالی)' : '••••••••';
                } else {
                    $before[$key] = $oldValue;
                    $after[$key] = $newValue;
                }
            }

            if ($after === []) {
                return;
            }

            self::log('settings.updated', $title, before: $before, after: $after);
        } catch (Throwable) {
            // logging must never break the save
        }
    }

    /** API keys / merchants / secrets must never appear in the log. */
    protected static function isSensitiveKey(string $key): bool
    {
        // .key$ → the Sadad OAuth/AES key، pubkey → the IranKish RSA key
        return (bool) preg_match('/(api_key|secret|merchant|token|password|mobile|username_key|pubkey|\.key$)/i', $key);
    }

    protected static function requestIp(): ?string
    {
        try {
            return request()->ip();
        } catch (Throwable) {
            return null;
        }
    }
}
