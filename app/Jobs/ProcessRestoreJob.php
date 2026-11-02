<?php

namespace App\Jobs;

use App\Models\Backup;
use App\Support\AdminNotifier;
use App\Support\Backups\BackupManager;
use App\Support\Fmt;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

/**
 * Restores an uploaded backup ZIP in the background — only missing rows
 * are inserted, duplicates are skipped (insertOrIgnore).
 */
class ProcessRestoreJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public int $timeout = 3600;

    public function __construct(public int $backupId)
    {
        //
    }

    public function handle(BackupManager $manager): void
    {
        $backup = Backup::query()->find($this->backupId);

        if (! $backup || $backup->status === Backup::STATUS_COMPLETED) {
            return;
        }

        $manager->restore($backup);

        $backup->refresh();

        if ($backup->status === Backup::STATUS_COMPLETED) {
            AdminNotifier::notify(
                'backup.restored',
                'بازگردانی پشتیبان کامل شد ✅',
                Fmt::fa($backup->rows_inserted).' ردیف افزوده شد و '
                    .Fmt::fa($backup->rows_skipped).' ردیف تکراری رد شد.',
                route('admin.backups'),
            );
        } else {
            AdminNotifier::notify(
                'backup.failed',
                'بازگردانی پشتیبان ناموفق بود ❌',
                (string) $backup->error,
                route('admin.backups'),
            );
        }
    }

    public function failed(Throwable $exception): void
    {
        $backup = Backup::query()->find($this->backupId);

        if ($backup && $backup->isRunning()) {
            $backup->forceFill([
                'status' => Backup::STATUS_FAILED,
                'error' => mb_substr($exception->getMessage(), 0, 900),
            ])->save();
        }
    }
}
