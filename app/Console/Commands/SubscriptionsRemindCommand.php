<?php

namespace App\Console\Commands;

use App\Models\SellerSubscription;
use App\Support\Fmt;
use App\Support\JDate;
use App\Support\SellerNotifier;
use App\Support\Sms\SmsTemplates;
use Illuminate\Console\Command;

/**
 * v0.23.0 — «subscriptions:remind» — DAILY expiry countdown.
 *
 * While an ACTIVE subscription is within its last 7 days, the seller gets
 * one bell/push notification + one SMS EVERY DAY with the remaining days
 * («۷ روز تا پایان اشتراک» … «امروز آخرین روز اشتراک»). The reminded_at
 * stamp holds the LAST send date — a message goes out only when the day
 * changed, so a duplicated cron run never double-sends and the countdown
 * reaches the seller every single day until the end.
 *
 * The cPanel cron already runs `php artisan schedule:run` every minute
 * (developer settings panel shows its heartbeat) — this command is
 * scheduled daily at 10:00 in routes/console.php.
 */
class SubscriptionsRemindCommand extends Command
{
    /** The countdown starts this many days before the end. */
    private const WINDOW_DAYS = 7;

    protected $signature = 'subscriptions:remind';

    protected $description = 'یادآوری روزانهٔ پایان اشتراک (از ۷ روز مانده تا روز آخر) به فروشندگان + پیامک';

    public function handle(): int
    {
        $today = now()->startOfDay();

        $rows = SellerSubscription::query()
            ->where('ends_at', '>', now())
            ->where('ends_at', '<=', $today->copy()->addDays(self::WINDOW_DAYS)->endOfDay())
            // one message per DAY: first-ever reminder has no stamp yet,
            // afterwards remind when the stamp is older than today
            ->where(fn ($q) => $q->whereNull('reminded_at')->orWhere('reminded_at', '<', $today))
            ->with(['seller:id,phone,instagram_username', 'plan:id,title'])
            ->get();

        $sent = 0;

        foreach ($rows as $row) {
            // Stamp FIRST under the row-lock semantics of a plain update —
            // a duplicated cron run re-reads reminded_at and skips.
            $claimed = SellerSubscription::query()
                ->whereKey($row->id)
                ->where(fn ($q) => $q->whereNull('reminded_at')->orWhere('reminded_at', '<', $today))
                ->update(['reminded_at' => now()]);

            if ($claimed === 0) {
                continue; // another worker got this day
            }

            $days = $row->daysLeft();

            SellerNotifier::notify(
                $row->seller,
                'subscription.status',
                $days > 0
                    ? Fmt::toFa($days).' روز تا پایان اشتراک «'.$row->title().'» ⏳'
                    : 'امروز آخرین روز اشتراک «'.$row->title().'» ⏳',
                'برای ادامهٔ مزایا و نرخ ویژه، از صفحهٔ «اشتراک و ارتقا» پلن را تمدید کنید.',
                route('seller.subscription'),
                'crown',
                $row,
            );

            // پیامک — daily countdown SMS (template center editable, the
            // log driver keeps LOCAL runs harmless). Never throws.
            if ($row->seller?->phone) {
                try {
                    SmsTemplates::send('subscription.reminder', $row->seller->phone, [
                        'days' => $days > 0 ? Fmt::toFa($days) : '۰',
                        'plan' => $row->title(),
                        'date' => (string) JDate::date($row->ends_at),
                    ]);
                } catch (\Throwable $e) {
                    logger()->warning('subscription reminder SMS failed: '.$e->getMessage());
                }
            }

            $sent++;
        }

        if ($sent > 0) {
            $this->info("Reminders sent: {$sent}");
        } else {
            $this->info('No subscriptions need a reminder.');
        }

        return self::SUCCESS;
    }
}
