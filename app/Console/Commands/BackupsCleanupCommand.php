<?php

namespace App\Console\Commands;

use App\Models\Backup;
use App\Models\Setting;
use App\Support\Backups\BackupManager;
use App\Support\Fmt;
use Illuminate\Console\Command;

/**
 * Deletes automatic backups older than the retention window the admin
 * configured (e.g. ۱۸۰ روز = ۶ ماه) and sweeps stale temp folders left
 * by interrupted builds.
 */
class BackupsCleanupCommand extends Command
{
    protected $signature = 'backups:cleanup';

    protected $description = 'حذف پشتیبان‌های خودکار قدیمی‌تر از بازهٔ تعیین‌شده در تنظیمات';

    public function handle(): int
    {
        $days = max(7, (int) Setting::get('backups.retention_days', 180));
        $cut = now()->subDays($days);

        $old = Backup::query()
            ->where('type', Backup::TYPE_AUTO)
            ->where('status', Backup::STATUS_COMPLETED)
            ->where('created_at', '<', $cut)
            ->get();

        $deleted = 0;

        foreach ($old as $backup) {
            app(BackupManager::class)->delete($backup);
            $deleted++;
        }

        // Sweep temp dirs from interrupted builds (older than a day).
        $swept = 0;
        foreach (['backups-tmp', 'backups-restore', 'backups-mail'] as $prefix) {
            foreach (glob(storage_path('app/'.$prefix).'-*') ?: [] as $dir) {
                if (is_dir($dir) && filemtime($dir) < now()->subDay()->getTimestamp()) {
                    foreach (glob($dir.'/*') ?: [] as $file) {
                        @unlink($file);
                    }
                    @rmdir($dir);
                    $swept++;
                }
            }
        }

        $this->info("پاک‌سازی: {$deleted} پشتیبان قدیمی حذف شد (بازهٔ نگهداری: {$days} روز)"
            .($swept > 0 ? " — {$swept} پوشهٔ موقت خالی شد." : ''));

        if ($deleted > 0) {
            logger()->info("backups:cleanup — {$deleted} auto backup (".Fmt::fa($deleted).") older than {$days} days deleted.");
        }

        return self::SUCCESS;
    }
}
