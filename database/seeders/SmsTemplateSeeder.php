<?php

namespace Database\Seeders;

use App\Models\SmsTemplate;
use Illuminate\Database\Seeder;

/**
 * The 20 default SMS templates («مرکز پیامک») — exactly the event set of
 * the reference design. Idempotent: existing rows are never overwritten,
 * so admin edits survive re-seeding; only missing keys are inserted.
 */
class SmsTemplateSeeder extends Seeder
{
    /** Shared sample values used by the live preview. */
    protected const APP = 'کافه آنلاین';

    public function run(): void
    {
        $app = self::APP;

        $templates = [

            /* ─────────────── سفارش‌ها (orders) ─────────────── */

            [
                'key' => 'order.queued', 'title' => 'رزرو سفارش به صف بررسی', 'group' => 'orders',
                'body' => "سفارش {order_number} شما در صف بررسی پذیرش قرار گرفت و پس از آن به کارشناسان {$app} منتقل شد؛ نتیجه از طریق پیامک اعلام می‌شود.",
                'variables' => ['order_number'],
                'sample' => ['order_number' => 'CN050615-9441'],
            ],
            [
                'key' => 'order.accepted', 'title' => 'پذیرش سفارش توسط فروشنده', 'group' => 'orders',
                'body' => "درخواست {order_number} شما توسط {accepted_by} پذیرش شد؛ برای شروع کار، پرداخت را انجام دهید. {$app}",
                'variables' => ['accepted_by', 'order_number'],
                'sample' => ['order_number' => 'CN050615-9441', 'accepted_by' => 'کافه ستاره'],
            ],
            [
                'key' => 'order.payment_reminder', 'title' => 'یادآوری پرداخت سفارش پرداخت‌نشده', 'group' => 'orders',
                'body' => "سفارش {order_number} شما در {app_name} هنوز پرداخت نشده است.\nتا پایان مهلت می‌توانید از همین لینک پرداخت را انجام دهید:\n{link}",
                'variables' => ['order_number', 'app_name', 'link'],
                'sample' => ['order_number' => 'CN050615-9441', 'app_name' => $app, 'link' => 'caffeinet.ir/pay/TX9931'],
            ],
            [
                'key' => 'order.in_progress', 'title' => 'شروع انجام سفارش', 'group' => 'orders',
                'body' => "کار روی سفارش {order_number} توسط {operator} آغاز شد.\nاپ {app_name}",
                'variables' => ['order_number', 'operator', 'app_name'],
                'sample' => ['order_number' => 'CN050615-9441', 'operator' => 'مریم احمدی', 'app_name' => $app],
            ],
            [
                'key' => 'order.needs_info', 'title' => 'نیاز به اطلاعات تکمیلی', 'group' => 'orders',
                'body' => "برای ادامهٔ سفارش {order_number} به اطلاعات تکمیلی نیاز است؛ لطفاً از طریق گفتگوی سفارش در اپ {app_name} آن را ارسال کنید.",
                'variables' => ['order_number', 'app_name'],
                'sample' => ['order_number' => 'CN050615-9441', 'app_name' => $app],
            ],
            [
                'key' => 'order.completed', 'title' => 'تکمیل و تسویه سفارش', 'group' => 'orders',
                'body' => "سفارش {order_number} تکمیل و تسویه شد. از اعتماد شما به {app_name} سپاسگزاریم.",
                'variables' => ['order_number', 'app_name'],
                'sample' => ['order_number' => 'CN050615-9441', 'app_name' => $app],
            ],
            [
                'key' => 'order.delivered', 'title' => 'تحویل سفارش', 'group' => 'orders',
                'body' => "سفارش {order_number} شما آماده و تحویل شد. برای مشاهده نتیجه به اپ مراجعه کنید.\n{$app}",
                'variables' => ['order_number'],
                'sample' => ['order_number' => 'CN050615-9441'],
            ],
            [
                'key' => 'order.delivery_pin', 'title' => 'کد تأیید تحویل سفارش', 'group' => 'orders',
                'body' => "سفارش {order_number} شما تحویل شد. کد تأیید تحویل شما: {pin}\nپس از دریافت، در اپ {app_name} آن را تأیید کنید.",
                'variables' => ['pin', 'app_name'],
                'sample' => ['order_number' => 'CN050615-9441', 'pin' => '4831', 'app_name' => $app],
            ],
            [
                'key' => 'order.cancelled', 'title' => 'لغو سفارش', 'group' => 'orders',
                'body' => "سفارش {order_number} شما لغو شد؛ علت: {reason}.\nجهت پیگیری با پشتیبانی در تماس باشید.\n{$app}",
                'variables' => ['order_number', 'reason'],
                'sample' => ['order_number' => 'CN050615-9441', 'reason' => 'انصراف فروشنده'],
            ],
            [
                'key' => 'order.booking_reminder', 'title' => 'یادآوری نوبت سفارش', 'group' => 'orders',
                'body' => "یادآوری نوبت: سفارش {order_number} شما برای زمان {time} ثبت شده است؛ لطفاً چند دقیقه قبل در اپ آنلاین باشید.\n{$app}",
                'variables' => ['order_number', 'time'],
                'sample' => ['order_number' => 'CN050615-9441', 'time' => 'پنجشنبه ۱۴:۳۰'],
            ],
            [
                'key' => 'order.transferred', 'title' => 'انتقال سفارش به شما', 'group' => 'orders',
                'body' => "سفارش {order_number} از طرف مدیریت به شما ({role_name}) واگذار شد — {$app}",
                'variables' => ['order_number', 'role_name'],
                'sample' => ['order_number' => 'CN050615-9441', 'role_name' => 'مدیر فروشگاه'],
            ],

            /* ─────────────── ورود و احراز (auth) ─────────────── */

            [
                'key' => 'otp_login', 'title' => 'کد ورود (OTP)', 'group' => 'auth',
                'body' => "کد ورود شما به {$app}: {code}\nاعتبار: {minutes} دقیقه",
                'variables' => ['code', 'minutes'],
                'sample' => ['code' => '384612', 'minutes' => '5'],
            ],

            /* ─────────────── کیف پول (wallet) ─────────────── */

            [
                'key' => 'order.paid', 'title' => 'پرداخت موفق سفارش', 'group' => 'wallet',
                'body' => "{app_name}\nسفارش {order_number} با موفقیت پرداخت شد و در صف انجام قرار گرفت.\nکد پیگیری: {trace}",
                'variables' => ['app_name', 'order_number', 'trace'],
                'sample' => ['app_name' => $app, 'order_number' => 'CN050615-9441', 'trace' => 'TR-889921'],
            ],
            [
                'key' => 'order.refunded', 'title' => 'بازگشت وجه به کیف پول', 'group' => 'wallet',
                'body' => "مبلغ سفارش {order_number} ({amount} تومان) به کیف پول شما در {app_name} برگشت داده شد.",
                'variables' => ['order_number', 'amount', 'app_name'],
                'sample' => ['order_number' => 'CN050615-9441', 'amount' => '۲۵۰٬۰۰۰', 'app_name' => $app],
            ],
            [
                'key' => 'wallet.charged', 'title' => 'واریز به کیف پول', 'group' => 'wallet',
                'body' => "مبلغ {amount} تومان به کیف پول شما در {app_name} واریز شد.\nموجودی فعلی: {balance} تومان.",
                'variables' => ['amount', 'app_name', 'balance'],
                'sample' => ['amount' => '۲۵۰٬۰۰۰', 'app_name' => $app, 'balance' => '۵۲۰٬۰۰۰'],
            ],
            [
                'key' => 'notify.salary_paid', 'title' => 'واریز حقوق به کیف پول', 'group' => 'wallet',
                'body' => "واریز حقوق: مبلغ {amount} تومان به کیف پول شما واریز شد ({context}) — {$app}",
                'variables' => ['amount', 'context'],
                'sample' => ['amount' => '۱۲٬۵۰۰٬۰۰۰', 'context' => 'اسفند ۱۴۰۵'],
            ],
            [
                'key' => 'notify.unaccepted_request', 'title' => 'درخواست برداشت (یادآوری مدیر)', 'group' => 'wallet',
                'body' => "{customer} عزیز، درخواست برداشت {order_number} پس از {minutes} دقیقه هنوز بررسی نشده — به‌زودی رسیدگی می‌شود.\n{$app}",
                'variables' => ['customer', 'order_number', 'minutes'],
                'sample' => ['customer' => 'کاربر', 'order_number' => 'WD-2231', 'minutes' => '15'],
            ],

            /* ─────────────── اعلان‌ها (notify) ─────────────── */

            [
                'key' => 'birthday.greeting', 'title' => 'پیام تبریک تولد مشتری', 'group' => 'notify',
                'body' => "{name} عزیز، تولدت مبارکا 🎂\nبه رسم همیشگی، مبلغ {amount} تومان به‌عنوان هدیه به کیف پول شما در {app_name} اضافه شد.\nسال پر از خوشی آرزو می‌کنیم!",
                'variables' => ['name', 'amount', 'app_name'],
                'sample' => ['name' => 'سارا محمدی', 'amount' => '۱۰۰٬۰۰۰', 'app_name' => $app],
            ],

            /* ─────────────── پشتیبانی (tickets) ─────────────── */

            [
                'key' => 'ticket.replied', 'title' => 'پاسخ پشتیبانی به تیکت', 'group' => 'tickets',
                'body' => "به تیکت {ticket_number} شما در {app_name} پاسخ داده شد؛ برای مشاهده به پیام‌ها مراجعه کنید.",
                'variables' => ['ticket_number', 'app_name'],
                'sample' => ['ticket_number' => 'TKT-1024', 'app_name' => $app],
            ],

            /* ─────────────── سیستم (system) ─────────────── */

            [
                'key' => 'test', 'title' => 'پیام آزمایشی (تنظیمات)', 'group' => 'system',
                'body' => "پیامک آزمایشی {app_name} — تنظیمات پیامک با موفقیت ذخیره شد.",
                'variables' => ['app_name'],
                'sample' => ['app_name' => $app],
            ],
        ];

        foreach ($templates as $t) {
            SmsTemplate::query()->firstOrCreate(
                ['key' => $t['key']],
                [
                    'title'       => $t['title'],
                    'group'       => $t['group'],
                    'body'        => $t['body'],
                    'variables'   => $t['variables'],
                    'sample_data' => $t['sample'] + ['app_name' => $app],
                    'enabled'     => true,
                ],
            );
        }
    }
}
