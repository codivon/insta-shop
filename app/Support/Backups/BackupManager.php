<?php

namespace App\Support\Backups;

use App\Models\Backup;
use App\Models\Setting;
use App\Support\Fmt;
use Illuminate\Database\QueryException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;
use ZipArchive;

/**
 * BackupManager — the engine behind «پشتیبان‌گیری».
 *
 *  • build()   : dumps EVERY table of the database (a handful of
 *                transient/system tables excluded) into one ZIP archive,
 *                streamed row-chunk by row-chunk so huge databases never
 *                hit memory or timeout limits. Runs inside a queued job
 *                (or the cron) — never inside a web request.
 *
 *  • restore() : reads an uploaded backup ZIP, compares every record with
 *                the live tables and INSERTS ONLY MISSING ROWS (duplicates
 *                are skipped). Also chunked, also runs in the background.
 */
final class BackupManager
{
    /** Transient / system tables that must never be backed up or restored. */
    private const EXCLUDED = [
        'migrations', 'sessions', 'cache', 'cache_locks', 'jobs', 'job_batches',
        'failed_jobs', 'backups', 'otp_codes', 'password_reset_tokens',
    ];

    /** Rows per insert batch while building / restoring. */
    private const CHUNK = 1000;

    /* ------------------------------------------------------------------
     |  Table list
     *----------------------------------------------------------------- */

    /** Every content table of the CURRENT database (system tables excluded).
     *
     * v0.21.4 SCOPING FIX — the production bug «backup build failed: 1146
     * Table 'owqdlxcv_insta_shop.advertisements' doesn't exist»:
     * `Schema::getTableListing()` with a null schema compiles
     * `... from information_schema.tables where table_schema not in
     * (system schemas)` — i.e. it lists tables from EVERY database this
     * MySQL user can see, NOT just the app's. On cPanel the account user
     * is granted on ALL of the account's databases, so a second database
     * (old test install, unrelated script — its `advertisements` table was
     * the field example) leaked its table NAMES into the dump list;
     * `DB::table($name)` then queried the app database → 1146 → every
     * automatic AND manual backup failed. Fix: pass the CURRENT
     * connection's database name so information_schema is filtered with
     * `table_schema = '<db>'`. SQLite (sqlite_master via PRAGMA) is
     * file-scoped already and its «database name» is a file path — keep
     * the null behavior there. Reproduced + verified on real MariaDB 11.8
     * with a second database holding a foreign `advertisements` table.
     */
    public function tables(): array
    {
        $schema = in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)
            ? DB::getDatabaseName()
            : null;

        return collect(Schema::getTables($schema))
            ->pluck('name')
            ->map(fn ($name) => strtolower((string) $name))
            // SQLite lists schema-qualified names («main.users») — keep the
            // bare table name; MySQL already returns plain names.
            ->map(fn ($name) => str_contains($name, '.') ? substr($name, strrpos($name, '.') + 1) : $name)
            ->reject(fn ($name) => in_array($name, self::EXCLUDED, true))
            ->reject(fn ($name) => str_ends_with($name, '_sessions'))
            ->unique()
            ->values()
            ->all();
    }

    /* ------------------------------------------------------------------
     |  Create
     *----------------------------------------------------------------- */

    /** Register a new backup row; the heavy work happens in build(). */
    public function create(string $type, ?int $adminId = null): Backup
    {
        return Backup::create([
            'type' => in_array($type, [Backup::TYPE_MANUAL, Backup::TYPE_AUTO], true) ? $type : Backup::TYPE_MANUAL,
            'status' => Backup::STATUS_PENDING,
            'admin_id' => $adminId,
        ]);
    }

    /* ------------------------------------------------------------------
     |  Build (dump every table into a ZIP)
     *----------------------------------------------------------------- */

    public function build(Backup $backup): void
    {
        @set_time_limit(0);
        @ini_set('memory_limit', '512M');

        $backup->forceFill(['status' => Backup::STATUS_PROCESSING, 'error' => null])->save();

        $tmpDir = null;
        $zip = null;

        try {
            $tables = $this->tables();

            if ($tables === []) {
                throw new RuntimeException('هیچ جدولی برای پشتیبان‌گیری پیدا نشد.');
            }

            $disk = Storage::disk('local');
            $tmpDir = storage_path('app/backups-tmp/'.Str::uuid());
            $this->ensureDirectory($tmpDir);

            $meta = [
                'app' => 'InstaShop',
                'format' => 2,
                'created_at' => now()->toIso8601String(),
                'php' => PHP_VERSION,
                'laravel' => app()->version(),
                'database' => DB::getDriverName(),
                'tables' => [],
            ];

            $zipPath = $tmpDir.'/archive.zip';
            $zip = new ZipArchive;

            if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                throw new RuntimeException('ساخت فایل فشردهٔ پشتیبان ممکن نشد.');
            }

            $totalRecords = 0;

            foreach ($tables as $index => $table) {
                try {
                    $this->dumpTable($zip, $tmpDir, $table, $meta, $totalRecords);
                } catch (QueryException $e) {
                    // v0.21.4 defense-in-depth: a table that is LISTED but
                    // not readable must not kill the whole backup — skip it,
                    // journal it, keep going. Covered class = «this table
                    // can't be opened at all»: 1051 unknown table, 1109
                    // unknown table (secondary contexts), 1146 no such table,
                    // 1356 view references invalid objects, 1812/1814/1932
                    // missing or discarded InnoDB tablespace (verified live on
                    // MariaDB 11.8: ALTER ... DISCARD TABLESPACE → select
                    // fails with 1814). REAL data errors (deadlock 1205,
                    // gone-away 2006, bad data) still fail loudly.
                    $code = (int) ($e->errorInfo[1] ?? 0);

                    if (! in_array($code, [1051, 1109, 1146, 1356, 1812, 1814, 1932], true)) {
                        throw $e;
                    }

                    Log::warning("backup skipped unreadable table «{$table}»: ".$e->getMessage());
                }

                $backup->forceFill([
                    'tables_total' => count($tables),
                    'tables_done' => $index + 1,
                    'records_count' => $totalRecords,
                ])->save();
            }

            file_put_contents(
                $tmpDir.'/backup.meta.json',
                json_encode($meta, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT),
            );
            $zip->addFile($tmpDir.'/backup.meta.json', 'backup.meta.json');

            if (! $zip->close()) {
                throw new RuntimeException('بستن فایل فشردهٔ پشتیبان ناموفق بود.');
            }

            $prefix = $backup->type === Backup::TYPE_AUTO ? 'auto' : 'manual';
            $filename = 'instashop-'.$prefix.'-'.now()->format('Ymd-His').'-'.Str::lower(Str::random(4)).'.zip';
            $path = 'backups/'.$filename;

            $stream = fopen($zipPath, 'rb');
            $disk->put($path, $stream);
            fclose($stream);

            $this->deleteDirectory($tmpDir);
            $tmpDir = null;

            $backup->forceFill([
                'filename' => $filename,
                'original_name' => null,
                'path' => $path,
                'size' => (int) $disk->size($path),
                'records_count' => $totalRecords,
                'tables_total' => count($tables),
                'tables_done' => count($tables),
                'tables' => $meta['tables'],
                'status' => Backup::STATUS_COMPLETED,
                'completed_at' => now(),
                'error' => null,
            ])->save();
        } catch (Throwable $e) {
            // Close the archive best-effort BEFORE the temp dir vanishes —
            // an open ZipArchive garbage-collected over a deleted temp file
            // spews «Cannot destroy the zip context» warnings.
            if ($zip instanceof ZipArchive) {
                try {
                    @$zip->close();
                } catch (Throwable) {
                }
            }

            if ($tmpDir !== null) {
                $this->deleteDirectory($tmpDir);
            }

            $backup->forceFill([
                'status' => Backup::STATUS_FAILED,
                'error' => mb_substr($e->getMessage(), 0, 900),
            ])->save();

            Log::error('backup build failed: '.$e->getMessage());
        }
    }

    /**
     * Stream ONE table into the ZIP as data/<table>.json (chunked, constant
     * memory). Extracted from build() so a single unreadable table can be
     * skipped without tangling the archive/progress bookkeeping.
     *
     * @param  array<string, int>  $meta  tables map (name → row count)
     */
    private function dumpTable(ZipArchive $zip, string $tmpDir, string $table, array &$meta, int &$totalRecords): void
    {
        $rows = 0;
        $file = $tmpDir.'/'.$table.'.json';
        $handle = fopen($file, 'w');
        fwrite($handle, '[');

        $first = true;

        $writeRows = function ($chunk) use (&$first, &$rows, &$totalRecords, $handle) {
            foreach ($chunk as $row) {
                $json = json_encode((array) $row, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE | JSON_PARTIAL_OUTPUT_ON_ERROR);

                if ($json !== false) {
                    fwrite($handle, ($first ? '' : ',').$json);
                    $first = false;
                    $rows++;
                    $totalRecords++;
                }
            }
        };

        try {
            $query = DB::table($table)->select('*');

            if (in_array('id', Schema::getColumnListing($table), true)) {
                // chunkById paginates by the primary key — no orderBy
                // complaint, constant memory even on huge tables.
                $query->chunkById(self::CHUNK, fn ($chunk) => $writeRows($chunk), 'id');
            } else {
                // Tables without an id column: stream the whole set.
                $writeRows($query->cursor());
            }
        } catch (Throwable $e) {
            @fclose($handle);
            @unlink($file);

            throw $e;
        }

        fwrite($handle, ']');
        fclose($handle);

        $meta['tables'][$table] = $rows;
        $zip->addFile($file, 'data/'.$table.'.json');
    }

    /* ------------------------------------------------------------------
     |  Restore (uploaded ZIP → insert only missing rows)
     *----------------------------------------------------------------- */

    public function restore(Backup $backup): void
    {
        @set_time_limit(0);
        @ini_set('memory_limit', '512M');

        $backup->forceFill(['status' => Backup::STATUS_PROCESSING, 'error' => null])->save();

        $tmpDir = null;

        try {
            $disk = Storage::disk('local');

            if (! $backup->path || ! $disk->exists($backup->path)) {
                throw new RuntimeException('فایل پشتیبان روی دیسک پیدا نشد.');
            }

            $zip = new ZipArchive;
            $zip->open($disk->path($backup->path));

            if ($zip->locateName('backup.meta.json') === false) {
                throw new RuntimeException('ساختار فایل معتبر نیست (backup.meta.json یافت نشد).');
            }

            $tmpDir = storage_path('app/backups-restore-'.Str::uuid());
            $this->ensureDirectory($tmpDir);

            // SECURITY (v0.20.0): entry allowlist BEFORE extractTo — a
            // crafted ZIP must only ever contain the manifest + data/*.json
            // table dumps. Anything else (nested paths, absolute paths,
            // symlinks, unexpected files) is rejected outright (zip-slip
            // defense-in-depth; extraction behavior is PHP-version-dependent).
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $entry = (string) ($zip->getNameIndex($i) ?: '');

                if (! preg_match('#^(backup\.meta\.json|data/[A-Za-z0-9_]+\.json)$#', $entry)) {
                    $zip->close();

                    throw new RuntimeException('ساختار فایل پشتیبان نامعتبر است (ورودی ناشناخته در ZIP: '.basename($entry).').');
                }
            }

            $zip->extractTo($tmpDir);
            $zip->close();

            $files = glob($tmpDir.'/data/*.json') ?: [];
            $backup->forceFill(['tables_total' => count($files), 'tables_done' => 0])->save();

            $driver = DB::getDriverName();
            $this->disableForeignKeyChecks($driver);

            $insertedTotal = 0;
            $skippedTotal = 0;
            $recordsTotal = 0;

            foreach ($files as $index => $file) {
                $table = basename($file, '.json');

                $rows = [];

                if (Schema::hasTable($table)) {
                    $columns = Schema::getColumnListing($table);
                    $decoded = json_decode((string) file_get_contents($file), true);

                    if (is_array($decoded)) {
                        foreach ($decoded as $row) {
                            if (is_array($row)) {
                                $rows[] = collect($row)->only($columns)->all();
                            }
                        }
                    }
                }

                $recordsTotal += count($rows);
                $inserted = 0;

                foreach (array_chunk($rows, 500) as $batch) {
                    $inserted += (int) DB::table($table)->insertOrIgnore($batch);
                }

                if ($inserted > 0) {
                    $this->fixSqliteSequence($driver, $table);
                }

                $insertedTotal += $inserted;
                $skippedTotal += count($rows) - $inserted;

                $backup->forceFill([
                    'tables_done' => $index + 1,
                    'records_count' => $recordsTotal,
                    'rows_inserted' => $insertedTotal,
                    'rows_skipped' => $skippedTotal,
                ])->save();
            }

            $this->enableForeignKeyChecks($driver);

            $this->deleteDirectory($tmpDir);
            $tmpDir = null;

            $backup->forceFill([
                'status' => Backup::STATUS_COMPLETED,
                'completed_at' => now(),
                'error' => null,
            ])->save();
        } catch (Throwable $e) {
            if ($tmpDir !== null) {
                $this->deleteDirectory($tmpDir);
            }

            $backup->forceFill([
                'status' => Backup::STATUS_FAILED,
                'error' => mb_substr($e->getMessage(), 0, 900),
            ])->save();

            Log::error('backup restore failed: '.$e->getMessage());
        }
    }

    /* ------------------------------------------------------------------
     |  Delete
     *----------------------------------------------------------------- */

    public function delete(Backup $backup): void
    {
        if ($backup->path) {
            try {
                Storage::disk('local')->delete($backup->path);
            } catch (Throwable) {
                // best effort — the row disappears anyway
            }
        }

        $backup->delete();
    }

    /* ------------------------------------------------------------------
     |  Auto schedule helpers (shared with the console command + UI)
     *----------------------------------------------------------------- */

    /** Interval string according to the saved settings. */
    public static function autoInterval(): string
    {
        $frequency = (string) Setting::get('backups.frequency', 'daily');

        return match ($frequency) {
            'weekly' => '7 days',
            'monthly' => '1 month',
            'custom' => max(1, (int) Setting::get('backups.interval_days', 3)).' days',
            default => '1 day',
        };
    }

    public static function autoRunTime(): string
    {
        return self::normalizeTime((string) Setting::get('backups.run_time', '03:00'));
    }

    /** When will the next automatic backup fire (for the UI). */
    public static function nextAutoRunAt(): ?Carbon
    {
        if (! (bool) Setting::get('backups.auto_enabled', false)) {
            return null;
        }

        $last = Backup::query()
            ->where('type', Backup::TYPE_AUTO)
            ->where('status', Backup::STATUS_COMPLETED)
            ->orderByDesc('completed_at')
            ->first();

        $dueAt = $last
            ? $last->completed_at->copy()->add(self::autoInterval())
            : now()->startOfDay();

        return $dueAt->setTimeFromTimeString(self::autoRunTime());
    }

    public static function normalizeTime(string $value): string
    {
        // Persian ۰-۹ / Arabic ٠-٩ → Latin digits; separators → ':'.
        // (Fmt::toLatin strips the colon too — a bare digit-only map is
        // needed so «۰۳:۰۰» and «03:00» both survive.)
        $value = trim(strtr($value, [
            '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
            '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
            '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
            '٫' => ':', '،' => ':',
        ]));

        $digits = preg_replace('/[^0-9:]/', '', $value) ?? '';

        if (preg_match('/^(\d{1,2}):(\d{1,2})$/', $digits, $m)) {
            return sprintf('%02d:%02d', min(23, (int) $m[1]), min(59, (int) $m[2]));
        }

        return '03:00';
    }

    /* ------------------------------------------------------------------
     |  Internals
     *----------------------------------------------------------------- */

    private function disableForeignKeyChecks(string $driver): void
    {
        try {
            while (DB::transactionLevel() > 0) {
                DB::commit();
            }

            if ($driver === 'mysql' || $driver === 'mariadb') {
                DB::statement('SET FOREIGN_KEY_CHECKS=0');
            } elseif ($driver === 'sqlite') {
                DB::statement('PRAGMA foreign_keys = OFF');
            }
        } catch (Throwable) {
            // best effort — restores usually still work
        }
    }

    private function enableForeignKeyChecks(string $driver): void
    {
        try {
            if ($driver === 'mysql' || $driver === 'mariadb') {
                DB::statement('SET FOREIGN_KEY_CHECKS=1');
            } elseif ($driver === 'sqlite') {
                DB::statement('PRAGMA foreign_keys = ON');
            }
        } catch (Throwable) {
            // best effort
        }
    }

    /** SQLite: bump the AUTOINCREMENT counter after explicit-ID inserts. */
    private function fixSqliteSequence(string $driver, string $table): void
    {
        if ($driver !== 'sqlite') {
            return;
        }

        try {
            $max = (int) DB::table($table)->max('id');

            if ($max <= 0) {
                return;
            }

            $exists = DB::select("SELECT name FROM sqlite_master WHERE type = 'table' AND name = 'sqlite_sequence'");

            if ($exists === []) {
                return;
            }

            $row = DB::table('sqlite_sequence')->where('name', $table)->first();

            if ($row) {
                DB::update('UPDATE sqlite_sequence SET seq = MAX(seq, ?) WHERE name = ?', [$max, $table]);
            } else {
                DB::insert('INSERT INTO sqlite_sequence (name, seq) VALUES (?, ?)', [$table, $max]);
            }
        } catch (Throwable) {
            // best effort
        }
    }

    private function ensureDirectory(string $dir): void
    {
        if (! is_dir($dir) && ! mkdir($dir, 0775, true) && ! is_dir($dir)) {
            throw new RuntimeException("ساخت پوشهٔ موقت ناموفق بود: {$dir}");
        }
    }

    private function deleteDirectory(string $dir): void
    {
        if (! is_dir($dir)) {
            return;
        }

        foreach (glob($dir.'/*') ?: [] as $item) {
            is_dir($item) ? $this->deleteDirectory($item) : @unlink($item);
        }

        @rmdir($dir);
    }
}
