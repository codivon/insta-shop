<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Backup;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Admin downloads a backup archive from the private `local` disk.
 */
class BackupDownload extends Controller
{
    public function __invoke(Backup $backup): StreamedResponse
    {
        abort_if($backup->status !== Backup::STATUS_COMPLETED || ! $backup->path, 404, 'فایل پشتیبان آمادهٔ دانلود نیست.');

        $disk = Storage::disk('local');

        abort_unless($disk->exists($backup->path), 404, 'فایل روی دیسک یافت نشد.');

        $name = $backup->original_name ?: ($backup->filename ?? 'instashop-backup.zip');

        return $disk->download($backup->path, $name);
    }
}
