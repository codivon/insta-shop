<?php

namespace App\Console\Commands;

use App\Models\BnplSettlement;
use App\Models\Setting;
use App\Support\AdminNotifier;
use App\Support\Fmt;
use App\Support\Payments\BnplSettlements;
use Illuminate\Console\Command;

/**
 * v0.30.0 — «bnpl:settle» — the DAILY settlement sweep of the BNPL
 * (installment) gateways (دیجی‌پی / اسنپ‌پی / ترب‌پی).
 *
 * These gateways settle the MERCHANT monthly, so InstaShop must mirror
 * that rhythm for its sellers: a paid BNPL order created PENDING
 * bnpl_settlements rows (rendered faded in the seller wallet with the
 * «بعد از تسویه … به کیف پول شما واریز می‌شود» promise). This command
 * deposits every pending settlement whose due_date has arrived — the due
 * date was computed at payment time as the NEXT occurrence of the
 * admin-configured settlement day (payment.{gateway}.settlement_day) of
 * that gateway — into the seller wallet with a TYPE_BNPL ledger entry.
 *
 * The admin can also deposit any pending row by hand (واریز دستی) from
 * the «تسویه اقساطی» admin section — both paths share
 * BnplSettlements::deposit() which is atomic + idempotent.
 *
 * Scheduled daily at 06:00 in routes/console.php (the cPanel cron runs
 * `php artisan schedule:run` every minute).
 */
class BnplSettleCommand extends Command
{
    protected $signature = 'bnpl:settle {--dry-run : فقط شمارش — هیچ واریزی انجام نمی‌شود}';

    protected $description = 'واریز خودکار تسویه‌های اقساطی سررسیدشده (دیجی‌پی/اسنپ‌پی/ترب‌پی) به کیف پول فروشندگان';

    public function handle(): int
    {
        $dueCount = BnplSettlement::query()
            ->where('status', 'pending')
            ->whereDate('due_date', '<=', today())
            ->count();

        $dueSum = (int) BnplSettlement::query()
            ->where('status', 'pending')
            ->whereDate('due_date', '<=', today())
            ->sum('amount');

        if ($dueCount === 0) {
            $this->line('هیچ تسویهٔ اقساطی سررسیدشده‌ای وجود ندارد.');

            return self::SUCCESS;
        }

        if ($this->option('dry-run')) {
            $this->line('در انتظار واریز: '.Fmt::fa($dueCount).' تسویه — مجموع '.Fmt::money($dueSum).' (dry-run)');

            return self::SUCCESS;
        }

        [$deposited, $amount] = BnplSettlements::settleDue();

        $this->info('واریز شد: '.Fmt::fa($deposited).' تسویه — مجموع '.Fmt::money($amount));

        if ($deposited > 0) {
            AdminNotifier::notify(
                'bnpl.settled',
                'تسویه اقساطی خودکار '.Fmt::fa($deposited).' مورد 💰',
                'مجموع '.Fmt::money($amount).' تومان به کیف پول فروشندگان واریز شد (کران bnpl:settle).',
                route('admin.bnpl.settlements'),
                'wallet',
            );
        }

        // the developer panel heartbeat already proves the cron runs; keep
        // a dedicated stamp so the BNPL section can show the last sweep
        Setting::set('payment.bnpl_last_settle_run', now()->format('Y-m-d H:i:s'));

        return self::SUCCESS;
    }
}
