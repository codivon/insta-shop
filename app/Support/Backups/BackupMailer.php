<?php

namespace App\Support\Backups;

use App\Mail\BackupPartMail;
use App\Mail\BackupTestMail;
use App\Models\Backup;
use App\Models\Setting;
use App\Support\Fmt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * BackupMailer — emails the backup archive through the SMTP account the
 * admin saved in تنظیمات → «پشتیبان‌گیری خودکار».
 *
 * Small files go out as ONE email; big archives are SPLIT into equal
 * parts (backup.zip.001, .002, …) and each part is sent as its own
 * email with reassembly instructions. If the archive is so large that
 * mailing it is unreasonable, the step is skipped gracefully.
 */
final class BackupMailer
{
    /** Hard ceiling: never attempt more than this many parts. */
    private const MAX_PARTS = 20;

    /**
     * Send the backup archive. Returns [ok, human message].
     * Never throws.
     *
     * @return array{0: bool, 1: string}
     */
    public static function send(Backup $backup): array
    {
        try {
            $to = trim((string) Setting::get('backups.email_to', ''));

            if (! (bool) Setting::get('backups.email_enabled', false)) {
                return [false, 'ارسال ایمیل پشتیبان در تنظیمات فعال نیست.'];
            }

            if ($to === '' || ! filter_var($to, FILTER_VALIDATE_EMAIL)) {
                return [false, 'آدرس ایمیل گیرندهٔ پشتیبان معتبر نیست.'];
            }

            $disk = Storage::disk('local');

            if (! $backup->path || ! $disk->exists($backup->path)) {
                return [false, 'فایل پشتیبان روی دیسک پیدا نشد.'];
            }

            self::configureMailer();

            $abs = $disk->path($backup->path);
            $size = (int) $disk->size($backup->path);
            $maxBytes = max(2, (int) Setting::get('backups.email_max_mb', 10)) * 1024 * 1024;

            // Small enough — a single email with the whole archive.
            if ($size <= $maxBytes) {
                Mail::mailer('instashop_backup')
                    ->to($to)
                    ->send(new BackupPartMail($backup, 1, 1, $abs, $backup->filename));

                self::markSent();

                return [true, 'فایل پشتیبان به '.$to.' ایمیل شد.'];
            }

            // Big — split into equal parts and mail each one separately.
            $parts = (int) ceil($size / $maxBytes);

            if ($parts > self::MAX_PARTS) {
                return [false, 'فایل پشتیبان بسیار حجیم است ('.Fmt::bytes($size).') و امکان ارسال ایمیلی آن وجود ندارد.'];
            }

            $tmpDir = storage_path('app/backups-mail-'.uniqid('p', true));

            if (! is_dir($tmpDir) && ! mkdir($tmpDir, 0775, true)) {
                return [false, 'ساخت پوشهٔ موقت برای تکه‌تکه‌کردن فایل ناموفق بود.'];
            }

            $baseName = pathinfo($backup->filename ?? 'instashop-backup.zip', PATHINFO_FILENAME);
            $source = fopen($abs, 'rb');

            try {
                for ($i = 1; $i <= $parts; $i++) {
                    $partPath = sprintf('%s/%s.zip.%03d', $tmpDir, $baseName, $i);
                    $out = fopen($partPath, 'wb');

                    if ($source) {
                        stream_copy_to_stream($source, $out, $maxBytes);
                    }

                    fclose($out);

                    Mail::mailer('instashop_backup')
                        ->to($to)
                        ->send(new BackupPartMail(
                            $backup,
                            $i,
                            $parts,
                            $partPath,
                            basename($partPath),
                        ));
                }
            } finally {
                if ($source) {
                    fclose($source);
                }

                foreach (glob($tmpDir.'/*') ?: [] as $file) {
                    @unlink($file);
                }
                @rmdir($tmpDir);
            }

            self::markSent();

            return [true, 'فایل پشتیبان در '.Fmt::fa($parts).' بخش جداگانه به '.$to.' ایمیل شد.'];
        } catch (Throwable $e) {
            Log::error('backup mail failed: '.$e->getMessage());

            return [false, 'ارسال ایمیل ناموفق بود: '.mb_substr($e->getMessage(), 0, 240)];
        }
    }

    /**
     * Send a simple test email with the CURRENT saved SMTP settings.
     * Returns [ok, message].
     *
     * @return array{0: bool, 1: string}
     */
    public static function sendTest(string $to): array
    {
        try {
            self::configureMailer();

            Mail::mailer('instashop_backup')->to($to)->send(new BackupTestMail);

            return [true, 'ایمیل آزمایشی به '.$to.' ارسال شد.'];
        } catch (Throwable $e) {
            Log::error('backup test mail failed: '.$e->getMessage());

            return [false, 'ارسال ایمیل آزمایشی ناموفق بود: '.mb_substr($e->getMessage(), 0, 240)];
        }
    }

    /** Register the runtime `instashop_backup` mailer from saved settings. */
    public static function configureMailer(): void
    {
        $encryption = (string) Setting::get('backups.smtp_encryption', 'tls');
        $from = trim((string) Setting::get('backups.smtp_from_address', ''));

        config([
            'mail.mailers.instashop_backup' => [
                'transport' => 'smtp',
                'host' => (string) Setting::get('backups.smtp_host', ''),
                'port' => (int) Setting::get('backups.smtp_port', 587),
                'encryption' => $encryption === 'none' ? null : $encryption,
                'username' => (string) Setting::get('backups.smtp_username', ''),
                'password' => (string) Setting::get('backups.smtp_password', ''),
                'timeout' => 30,
            ],
        ]);

        if ($from !== '' && filter_var($from, FILTER_VALIDATE_EMAIL)) {
            config([
                'mail.from.address' => $from,
                'mail.from.name' => (string) Setting::get('backups.smtp_from_name', '')
                    ?: config('mail.from.name', 'InstaShop'),
            ]);
        }
    }

    private static function markSent(): void
    {
        try {
            Setting::set('backups.email_last_sent', now()->format('Y-m-d H:i:s'));
        } catch (Throwable) {
            // best effort
        }
    }
}
