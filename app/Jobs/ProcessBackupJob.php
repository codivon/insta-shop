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
 * Builds a full database backup ZIP in the background.
 * `tries = 1` — a half-written backup is worthless, never retried.
 */
class ProcessBackupJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    /** Generous wall-clock budget; the job streams, so memory stays flat. */
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

        $manager->build($backup);

        $backup->refresh();

        if ($backup->status === Backup::STATUS_COMPLETED) {
            AdminNotifier::notify(
                'backup.completed',
                'پشتیبان‌گیری کامل شد ✅',
                'فایل «'.$backup->filename.'» ('.Fmt::bytes($backup->size).') آمادهٔ دانلود است.',
                route('admin.backups'),
            );
        } else {
            AdminNotifier::notify(
                'backup.failed',
                'پشتیبان‌گیری ناموفق بود ❌',
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

        AdminNotifier::notify(
            'backup.failed',
            'پشتیبان‌گیری ناموفق بود ❌',
            mb_substr($exception->getMessage(), 0, 400),
            route('admin.backups'),
        );
    }
}
