<?php

namespace Database\Seeders;

use App\Models\SmsTemplate;
use App\Support\Sms\SmsTemplates;
use Illuminate\Database\Seeder;

/**
 * Default SMS templates for InstaShop — only the events the shop actually
 * fires / needs:
 *
 *   - otp_login        → کد ورود یک‌بارمصرف (OtpService — همه پنل‌ها)
 *   - order.paid       → پرداخت موفق سفارش (Order::markPaid)
 *   - order.cancelled  → لغو سفارش
 *
 * Idempotent + self-pruning: existing rows are never overwritten (admin
 * edits survive re-seeding), missing keys are inserted and keys that no
 * longer belong to this project are DELETED.
 */
class SmsTemplateSeeder extends Seeder
{
    /** Sample values used by the live preview. */
    protected const APP = 'InstaShop';

    public function run(): void
    {
        $app = self::APP;

        $templates = [

            /* ─────────────── ورود و احراز (auth) ─────────────── */

            [
                'key' => 'otp_login', 'title' => 'کد ورود (OTP)', 'group' => 'auth',
                'body' => "کد ورود شما به {$app}: {code}\nاعتبار: {minutes} دقیقه",
                'variables' => ['code', 'minutes'],
                'sample' => ['code' => '384612', 'minutes' => '5'],
            ],

            /* ─────────────── سفارش‌ها (orders) ─────────────── */

            [
                'key' => 'order.paid', 'title' => 'پرداخت موفق سفارش', 'group' => 'orders',
                'body' => "{$app}\nسفارش {order_number} با موفقیت پرداخت شد و در حال پردازش است.\nکد پیگیری: {trace}",
                'variables' => ['order_number', 'trace'],
                'sample' => ['order_number' => 'ORD-1042', 'trace' => 'TR-889921'],
            ],
            [
                'key' => 'order.cancelled', 'title' => 'لغو سفارش', 'group' => 'orders',
                'body' => "سفارش {order_number} شما در {$app} لغو شد؛ علت: {reason}.\nجهت پیگیری با پشتیبانی در تماس باشید.",
                'variables' => ['order_number', 'reason'],
                'sample' => ['order_number' => 'ORD-1042', 'reason' => 'انصراف فروشنده'],
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

        // Prune templates that don't belong to this project (leftovers from
        // the old reference set — booking/wallet/tickets/birthday/…).
        $keep = array_column($templates, 'key');

        SmsTemplate::query()
            ->whereNotIn('key', $keep)
            ->get()
            ->each(function (SmsTemplate $tpl): void {
                SmsTemplates::flush($tpl->key);
                $tpl->delete();
            });
    }
}
