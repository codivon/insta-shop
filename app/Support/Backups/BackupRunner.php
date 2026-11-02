<?php

namespace App\Support\Backups;

use App\Jobs\ProcessBackupJob;
use App\Jobs\ProcessRestoreJob;
use App\Models\Backup;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * BackupRunner — pushes backup/restore work to the `database` queue and
 * tries to start a worker IMMEDIATELY (best effort) so the admin does not
 * have to wait for the next cron minute.
 *
 * Shared-hosting reality (this app's production target):
 *  • The queue connection is always `database` (explicit on dispatch), so
 *    QUEUE_CONNECTION does not matter.
 *  • If the instant kick is impossible (php-fpm/cgi web SAPI, or disabled
 *    exec functions), the heartbeat cron that already runs every minute
 *    picks the job up via its own `queue:work database --stop-when-empty`
 *    tick scheduled in routes/console.php. Worst case the work starts
 *    ~60s later — still zero web timeouts.
 */
final class BackupRunner
{
    public static function dispatchBackup(Backup $backup): void
    {
        ProcessBackupJob::dispatch($backup->id)->onConnection('database');
        self::kick();
    }

    public static function dispatchRestore(Backup $backup): void
    {
        ProcessRestoreJob::dispatch($backup->id)->onConnection('database');
        self::kick();
    }

    /**
     * Start a short-lived queue worker in the background.
     *
     * The command is detached through the shell (`... & > /dev/null 2>&1`)
     * so it survives the web request ending — a raw child process dies
     * with the parent when its pipes close. Under php-fpm/cgi the binary
     * cannot run artisan at all, so we skip and let the cron tick handle
     * it. Everything is best-effort and never throws.
     */
    public static function kick(): void
    {
        try {
            if (! function_exists('shell_exec') || ! function_exists('escapeshellarg')) {
                return;
            }

            $binary = PHP_BINARY;

            if (! $binary || ! is_executable($binary)) {
                return;
            }

            $base = strtolower(basename($binary));

            // php-fpm / php-cgi cannot run artisan scripts — skip the kick;
            // the scheduler's queue:work tick handles it within a minute.
            if (str_contains($base, 'fpm') || str_contains($base, 'cgi')) {
                return;
            }

            $command = sprintf(
                'cd %s && %s %s queue:work database --stop-when-empty --max-time=300 --sleep=1 --tries=1 > /dev/null 2>&1 &',
                escapeshellarg(base_path()),
                escapeshellarg($binary),
                escapeshellarg(base_path('artisan')),
            );

            shell_exec($command);
        } catch (Throwable $e) {
            Log::debug('backup queue kick skipped: '.$e->getMessage());
        }
    }
}
