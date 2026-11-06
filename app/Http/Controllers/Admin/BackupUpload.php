<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Backup;
use App\Support\Backups\BackupRunner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;
use ZipArchive;

/**
 * Chunked backup upload — the admin's browser slices the file into ~4MB
 * pieces (JS on the Backups page) so even heavy archives pass through
 * PHP upload limits and slow connections without a single giant POST.
 *
 *   phase=chunk  → append one piece to storage/app/private/backups/tmp/{uuid}.part
 *   phase=finish → validate the ZIP, register a Backup row, queue restore
 */
class BackupUpload extends Controller
{
    private const CHUNK_MAX = 8 * 1024 * 1024; // 8MB per piece

    /** SECURITY: hard cap on the TOTAL uploaded archive (disk-exhaustion guard). */
    private const TOTAL_MAX = 300 * 1024 * 1024; // 300MB

    public function store(Request $request): JsonResponse
    {
        $phase = (string) $request->input('phase', 'chunk');

        if ($phase === 'finish') {
            return $this->finish($request);
        }

        $request->validate([
            'uuid' => ['required', 'string', 'regex:/^[a-f0-9-]{8,64}$/i'],
            'index' => ['required', 'integer', 'min:0'],
            'chunk' => ['required', 'file', 'max:8192'],
        ], [
            'uuid.regex' => 'شناسهٔ آپلود نامعتبر است.',
            'chunk.max' => 'حجم هر تکه بیش از حد مجاز است.',
        ]);

        $tmpDir = storage_path('app/private/backups/tmp');
        $this->ensureDirectory($tmpDir);

        $target = $tmpDir.'/'.$request->string('uuid')->toString().'.part';

        // Sequential upload — index is expected to be exactly the next byte offset.
        if ((int) $request->input('index') === 0) {
            @unlink($target);
        } elseif (! is_file($target)) {
            return response()->json(['ok' => false, 'message' => 'آپلود نامعتبر است — تکه‌ها به‌ترتیب ارسال نمی‌شوند.'], 422);
        }

        $handle = fopen($target, 'ab');
        fwrite($handle, (string) $request->file('chunk')->getContent());
        fclose($handle);

        if (is_file($target) && filesize($target) > self::TOTAL_MAX) {
            @unlink($target);

            return response()->json(['ok' => false, 'message' => 'حجم فایل پشتیبان بیش از حد مجاز است (۳۰۰ مگابایت).'], 422);
        }

        return response()->json(['ok' => true, 'index' => (int) $request->input('index')]);
    }

    private function finish(Request $request): JsonResponse
    {
        $request->validate([
            'uuid' => ['required', 'string', 'regex:/^[a-f0-9-]{8,64}$/i'],
            'name' => ['required', 'string', 'max:190'],
        ], [
            'uuid.regex' => 'شناسهٔ آپلود نامعتبر است.',
        ]);

        $uuid = $request->string('uuid')->toString();
        $part = storage_path('app/private/backups/tmp/'.$uuid.'.part');

        abort_unless(is_file($part), 404, 'فایل آپلودشده پیدا نشد — دوباره تلاش کنید.');

        $originalName = $request->string('name')->toString();

        if (! str_ends_with(strtolower($originalName), '.zip')) {
            @unlink($part);

            return response()->json(['ok' => false, 'message' => 'فقط فایل پشتیبان با پسوند .zip پذیرفته می‌شود.'], 422);
        }

        if (Backup::query()->running()->exists()) {
            @unlink($part);

            return response()->json(['ok' => false, 'message' => 'یک عملیات پشتیبان‌گیری/بازگردانی در حال اجراست — پس از پایان دوباره تلاش کنید.'], 422);
        }

        // Quick structural sanity check BEFORE queueing the heavy restore.
        $zip = new ZipArchive;
        $valid = $zip->open($part) === true
            && $zip->locateName('backup.meta.json') !== false;
        if ($valid) {
            $zip->close();
        }

        if (! $valid) {
            @unlink($part);

            return response()->json(['ok' => false, 'message' => 'فایل، یک پشتیبان معتبر اینستاشاپ نیست (ساختار ZIP یا backup.meta.json معتبر نیست).'], 422);
        }

        $disk = Storage::disk('local');
        $prefix = pathinfo($originalName, PATHINFO_FILENAME);
        $prefix = Str::limit(preg_replace('/[^A-Za-z0-9\-\._]/', '', $prefix) ?: 'instashop-backup', 60, '');
        $stored = 'backups/upload-'.now()->format('Ymd-His').'-'.Str::lower(Str::random(4)).'-'.$prefix.'.zip';

        $disk->put($stored, fopen($part, 'rb'));
        @unlink($part);

        $backup = Backup::create([
            'filename' => basename($stored),
            'original_name' => $originalName,
            'path' => $stored,
            'size' => (int) $disk->size($stored),
            'type' => Backup::TYPE_UPLOAD,
            'status' => Backup::STATUS_PENDING,
            'admin_id' => auth('admin')->id(),
        ]);

        try {
            BackupRunner::dispatchRestore($backup);
        } catch (Throwable $e) {
            report($e);

            return response()->json(['ok' => false, 'message' => 'ثبت بازگردانی در صف پردازش ناموفق بود — دوباره تلاش کنید.'], 500);
        }

        return response()->json([
            'ok' => true,
            'id' => $backup->id,
            'message' => 'فایل آپلود شد؛ بازگردانی در پس‌زمینه آغاز شد (ردیف‌های تکراری رد می‌شوند).',
        ]);
    }

    private function ensureDirectory(string $dir): void
    {
        if (! is_dir($dir) && ! mkdir($dir, 0775, true) && ! is_dir($dir)) {
            abort(500, 'ساخت پوشهٔ موقت آپلود ناموفق بود.');
        }
    }
}
