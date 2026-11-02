<?php

namespace App\Console\Commands;

use App\Models\Backup;
use App\Models\Setting;
use App\Support\AdminNotifier;
use App\Support\Backups\BackupMailer;
use App\Support\Backups\BackupManager;
use App\Support\Fmt;
use Illuminate\Console\Command;
use Throwable;

/**
 * Runs whenever the heartbeat cron ticks (every 10 minutes) and fires an
 * automatic backup when the configured schedule is due:
 * روزانه / هفتگی / ماهانه / بازهٔ دلخواه (روز) + ساعت اجرا.
 */
class BackupsAutoCommand extends Command
{
    protected $signature = 'backups:auto';

    protected $description = 'اجرای پشتیبان‌گیری خودکار بر اساس تنظیمات پنل مدیریت';

    public function handle(BackupManager $manager): int
    {
        if (! (bool) Setting::get('backups.auto_enabled', false)) {
            return self::SUCCESS;
        }

        // One automatic run at a time.
        if (Backup::query()->where('type', Backup::TYPE_AUTO)->running()->exists()) {
            return self::SUCCESS;
        }

        $last = Backup::query()
            ->where('type', Backup::TYPE_AUTO)
            ->where('status', Backup::STATUS_COMPLETED)
            ->orderByDesc('completed_at')
            ->first();

        $dueAt = $last
            ? $last->completed_at->copy()->add(BackupManager::autoInterval())
            : now()->startOfDay();

        $dueAt = $dueAt->setTimeFromTimeString(BackupManager::autoRunTime());

        if (now()->lt($dueAt)) {
            $this->line('پشتیبان‌گیری خودکار هنوز موعدش نشده (موعد بعدی: '.$dueAt->format('Y-m-d H:i').').');

            return self::SUCCESS;
        }

        $this->info('شروع پشتیبان‌گیری خودکار...');

        $backup = $manager->create(Backup::TYPE_AUTO);
        $manager->build($backup);

        $backup->refresh();

        if ($backup->status !== Backup::STATUS_COMPLETED) {
            $this->error('پشتیبان‌گیری خودکار ناموفق بود: '.($backup->error ?? 'خطای نامشخص'));

            AdminNotifier::notify(
                'backup.failed',
                'پشتیبان‌گیری خودکار ناموفق بود ❌',
                (string) $backup->error,
                route('admin.backups'),
            );

            return self::SUCCESS;
        }

        Setting::set('backups.last_auto_run', now()->format('Y-m-d H:i:s'));

        $this->info('پشتیبان‌گیری کامل شد: '.$backup->filename.' ('.Fmt::bytes($backup->size).')');

        AdminNotifier::notify(
            'backup.completed',
            'پشتیبان‌گیری خودکار کامل شد ✅',
            'فایل «'.$backup->filename.'» ('.Fmt::bytes($backup->size).') آمادهٔ دانلود است.',
            route('admin.backups'),
        );

        // Email the archive when the admin opted in (parts are split automatically).
        try {
            [$ok, $message] = BackupMailer::send($backup);

            $this->line($ok ? $message : 'ایمیل پشتیبان: '.$message);
        } catch (Throwable $e) {
            $this->warn('ارسال ایمیل پشتیبان ناموفق بود: '.$e->getMessage());
        }

        return self::SUCCESS;
    }
}
