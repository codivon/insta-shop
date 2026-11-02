<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One backup archive — either produced by the «پشتیبان‌گیری» page /
 * the automatic scheduler, or uploaded by the admin for restore.
 */
class Backup extends Model
{
    public const TYPE_MANUAL = 'manual';

    public const TYPE_AUTO = 'auto';

    public const TYPE_UPLOAD = 'upload';

    public const STATUS_PENDING = 'pending';

    public const STATUS_PROCESSING = 'processing';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'filename', 'original_name', 'path', 'size', 'type', 'status',
        'error', 'tables_total', 'tables_done', 'records_count',
        'rows_inserted', 'rows_skipped', 'tables', 'admin_id', 'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'size' => 'integer',
            'tables_total' => 'integer',
            'tables_done' => 'integer',
            'records_count' => 'integer',
            'rows_inserted' => 'integer',
            'rows_skipped' => 'integer',
            'tables' => 'array',
            'completed_at' => 'datetime',
        ];
    }

    /* ------------------------------------------------------------------
     |  Scopes & helpers
     *----------------------------------------------------------------- */

    public function scopeRunning($query)
    {
        return $query->whereIn('status', [self::STATUS_PENDING, self::STATUS_PROCESSING]);
    }

    public function isRunning(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_PROCESSING], true);
    }

    /** «دستی / خودکار / آپلودی» */
    public function typeLabel(): string
    {
        return match ($this->type) {
            self::TYPE_AUTO => 'خودکار',
            self::TYPE_UPLOAD => 'آپلودی',
            default => 'دستی',
        };
    }

    /** «کامل شد / در حال پردازش / ...» */
    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'در صف پردازش',
            self::STATUS_PROCESSING => 'در حال پردازش',
            self::STATUS_COMPLETED => 'کامل شد',
            default => 'ناموفق',
        };
    }

    /** Color classes for the status badge in the UI. */
    public function statusClasses(): string
    {
        return match ($this->status) {
            self::STATUS_COMPLETED => 'bg-emerald-50 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-400',
            self::STATUS_PROCESSING => 'bg-primary-50 text-primary-600 dark:bg-primary-950/40 dark:text-primary-400',
            self::STATUS_PENDING => 'bg-amber-50 text-amber-600 dark:bg-amber-950/40 dark:text-amber-400',
            default => 'bg-rose-50 text-rose-600 dark:bg-rose-950/40 dark:text-rose-400',
        };
    }

    public function typeClasses(): string
    {
        return match ($this->type) {
            self::TYPE_AUTO => 'bg-violet-50 text-violet-600 dark:bg-violet-950/40 dark:text-violet-400',
            self::TYPE_UPLOAD => 'bg-sky-50 text-sky-700 dark:bg-sky-950/40 dark:text-sky-400',
            default => 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400',
        };
    }
}
