<?php

namespace App\Livewire\Admin\Developer;

use App\Livewire\Admin\Concerns\GuardsSuperAdmin;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Admin — «لاگ سیستم» (زیرمنوی برنامه‌نویس) — super admin ONLY.
 *
 * Modern log viewer for storage/logs: file picker, per-level stats with
 * clickable filters, search, expandable stack traces and destructive
 * actions (پاک‌سازی فایل فعلی / حذف فایل‌های قدیمی).
 *
 * Parsing is memory-safe: only the LAST megabyte of each file is read.
 */
#[Layout('layouts.admin-slot')]
#[Title('لاگ سیستم')]
class SystemLogs extends Component
{
    use GuardsSuperAdmin;

    /** Max bytes read from the tail of a log file. */
    protected const TAIL_BYTES = 1048576;

    /** Entries rendered per page. */
    protected const PAGE_SIZE = 80;

    public string $file = '';

    public string $level = 'all';

    public string $search = '';

    public int $shown = self::PAGE_SIZE;

    /** Pending destructive action target (current-file clear / old-files purge). */
    public ?string $confirmAction = null; // clear-current | delete-others | delete-file:<name>

    public function mount(): void
    {
        $files = $this->logFiles();

        $this->file = ($this->file !== '' && in_array($this->file, $files, true))
            ? $this->file
            : ($files[0] ?? '');
    }

    /** Available log files (newest first). */
    public function logFiles(): array
    {
        $dir = storage_path('logs');

        if (! is_dir($dir)) {
            return [];
        }

        $files = glob($dir.'/*.log') ?: [];

        usort($files, fn ($a, $b) => filemtime($b) <=> filemtime($a));

        return array_map(fn ($f) => basename($f), $files);
    }

    /** [name => pretty label + meta] for the view. */
    public function fileOptions(): array
    {
        $out = [];

        foreach ($this->logFiles() as $name) {
            $path = storage_path('logs/'.$name);

            $out[] = [
                'name'  => $name,
                'label' => str_starts_with($name, 'laravel-')
                    ? str_replace(['laravel-', '.log'], '', $name)
                    : 'امروز (تک‌فایلی)',
                'size'  => filesize($path) ?: 0,
                'mtime' => filemtime($path) ?: time(),
                'is_current' => $name === $this->file,
            ];
        }

        return $out;
    }

    public function selectFile(string $name): void
    {
        if (! in_array($name, $this->logFiles(), true)) {
            return;
        }

        $this->file   = $name;
        $this->level  = 'all';
        $this->search = '';
        $this->shown  = self::PAGE_SIZE;
    }

    public function selectLevel(string $level): void
    {
        $this->level = $level;
        $this->shown = self::PAGE_SIZE;
    }

    /** Parsed entries of the selected file (tail), newest LAST in file order. */
    protected function parse(string $content): array
    {
        $entries   = [];
        $current   = null;
        $pattern   = '/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] (\S+)\.([A-Z]+): (.*)$/';

        foreach (preg_split('/\r\n|\r|\n/', $content) ?: [] as $line) {
            if (preg_match($pattern, $line, $m)) {
                if ($current !== null) {
                    $entries[] = $current;
                }

                $current = [
                    'ts'    => $m[1],
                    'env'   => $m[2],
                    'level' => $m[3],
                    'msg'   => $m[4],
                    'stack' => '',
                ];

                continue;
            }

            // Continuation (stack trace, dumped context...)
            if ($current !== null && trim($line) !== '') {
                $current['stack'] .= ($current['stack'] === '' ? '' : "\n").$line;
            }
        }

        if ($current !== null) {
            $entries[] = $current;
        }

        // Newest first for display.
        return array_reverse($entries);
    }

    /** Level chips with counts + a total, computed from the parsed tail. */
    protected function levelStatsFrom(array $entries): array
    {
        $counts = [];

        foreach ($entries as $e) {
            $counts[$e['level']] = ($counts[$e['level']] ?? 0) + 1;
        }

        $total = array_sum($counts);

        return [
            'all'    => $total,
            'error'  => $counts['ERROR'] ?? 0,
            'warning'=> $counts['WARNING'] ?? 0,
            'info'   => $counts['INFO'] ?? 0,
            'debug'  => $counts['DEBUG'] ?? 0,
            'other'  => $total - (($counts['ERROR'] ?? 0) + ($counts['WARNING'] ?? 0) + ($counts['INFO'] ?? 0) + ($counts['DEBUG'] ?? 0)),
        ];
    }

    /** Parsed + filtered entries (search/level), no pagination. */
    public function parsed(): array
    {
        if ($this->file === '') {
            return [];
        }

        $path = realpath(storage_path('logs/'.$this->file));

        // Hard security: only plain .log files inside storage/logs.
        if ($path === false || dirname($path) !== realpath(storage_path('logs')) || ! str_ends_with($path, '.log')) {
            return [];
        }

        $size = filesize($path) ?: 0;

        if ($size === 0) {
            return [];
        }

        $fh = fopen($path, 'rb');

        if ($fh === false) {
            return [];
        }

        $bytes = min($size, self::TAIL_BYTES);

        fseek($fh, -$bytes, SEEK_END);

        $content = fread($fh, $bytes);
        fclose($fh);

        if ($content === false) {
            return [];
        }

        // Drop the possibly-truncated first line.
        $content = substr($content, (int) strpos($content, "\n") + 1);

        $entries = $this->parse($content);

        if ($this->level !== 'all') {
            $map = ['error' => ['ERROR', 'CRITICAL', 'ALERT', 'EMERGENCY'], 'warning' => ['WARNING', 'NOTICE'], 'info' => ['INFO'], 'debug' => ['DEBUG']];

            $levels = $map[$this->level] ?? ($this->level === 'other' ? null : [$this->level]);

            $entries = array_values(array_filter($entries, function ($e) use ($map, $levels) {
                if ($levels === null) { // "other"
                    $known = array_merge(...array_values($map));

                    return ! in_array($e['level'], $known, true);
                }

                return in_array($e['level'], $levels, true);
            }));
        }

        if (trim($this->search) !== '') {
            $needle = mb_strtolower(trim($this->search));

            $entries = array_values(array_filter($entries, fn ($e) => str_contains(mb_strtolower($e['msg']), $needle) || str_contains(mb_strtolower($e['stack']), $needle)));
        }

        return $entries;
    }

    public function loadMore(): void
    {
        $this->shown += self::PAGE_SIZE;
    }

    public function updatedSearch(): void
    {
        $this->shown = self::PAGE_SIZE;
    }

    /* ── destructive actions ── */

    public function askClearCurrent(): void
    {
        $this->confirmAction = 'clear-current';
    }

    public function askDeleteOthers(): void
    {
        $this->confirmAction = 'delete-others';
    }

    public function cancelAction(): void
    {
        $this->confirmAction = null;
    }

    /** Empty the selected log file (keeps the file itself). */
    public function clearCurrent(): void
    {
        $this->confirmAction = null;

        if ($this->file === '' || ! in_array($this->file, $this->logFiles(), true)) {
            return;
        }

        $path = storage_path('logs/'.$this->file);

        if (@file_put_contents($path, '') !== false) {
            $this->shown = self::PAGE_SIZE;
            session()->flash('admin-devlog-flash', 'فایل «'.$this->file.'» پاک‌سازی شد.');
        } else {
            session()->flash('admin-devlog-error', 'پاک‌سازی فایل ناموفق بود (مجوز نوشتن).');
        }
    }

    /** Delete every log file except the currently open one. */
    public function deleteOthers(): void
    {
        $this->confirmAction = null;

        $dir = storage_path('logs');
        $n   = 0;

        foreach (glob($dir.'/*.log') ?: [] as $f) {
            if (basename($f) === $this->file) {
                continue;
            }

            if (@unlink($f)) {
                $n++;
            }
        }

        session()->flash('admin-devlog-flash', \App\Support\Fmt::fa($n).' فایل لاگ قدیمی حذف شد.');
    }

    public function render()
    {
        $parsed = $this->parsed();

        return view('livewire.admin.developer.system-logs', [
            'files'   => $this->fileOptions(),
            'entries' => array_slice($parsed, 0, $this->shown),
            'stats'   => $this->levelStatsFrom($parsed),
            'total'   => count($parsed),
        ])->title('لاگ سیستم')
            ->layout('layouts.admin-slot', ['breadcrumb' => 'مدیریت کل — برنامه‌نویس — لاگ سیستم']);
    }
}
