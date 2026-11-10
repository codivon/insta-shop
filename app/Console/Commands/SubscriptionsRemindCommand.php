<?php

namespace App\Console\Commands;

use App\Models\SellerSubscription;
use App\Support\Fmt;
use App\Support\SellerNotifier;
use Illuminate\Console\Command;

/**
 * v0.22.0 — «subscriptions:remind» — daily expiry reminder.
 *
 * Finds ACTIVE subscriptions ending within the next 3 days that have not
 * been reminded yet (reminded_at null) and sends the seller ONE push +
 * bell notification («۳ روز تا پایان اشتراک»). The stamp guarantees a
 * single message per subscription row, even if the cron runs twice.
 *
 * The cPanel cron already runs `php artisan schedule:run` every minute
 * (developer settings panel shows its heartbeat) — this command is
 * scheduled daily at 10:00 in routes/console.php.
 */
class SubscriptionsRemindCommand extends Command
{
    protected $signature = 'subscriptions:remind';

    protected $description = 'یادآوری یک‌بارِ پایان اشتراک (۳ روز مانده) به فروشندگان';

    public function handle(): int
    {
        $rows = SellerSubscription::query()
            ->where('ends_at', '>', now())
            ->where('ends_at', '<=', now()->addDays(3))
            ->whereNull('reminded_at')
            ->with(['seller:id,phone,instagram_username', 'plan:id,title'])
            ->get();

        $sent = 0;

        foreach ($rows as $row) {
            // Stamp FIRST under the row-lock semantics of a plain update —
            // a duplicated cron run re-reads reminded_at and skips.
            SellerSubscription::query()
                ->whereKey($row->id)
                ->whereNull('reminded_at')
                ->update(['reminded_at' => now()]);

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
