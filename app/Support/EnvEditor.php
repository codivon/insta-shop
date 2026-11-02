<?php

namespace App\Support;

use Illuminate\Support\Facades\Process;
use Throwable;

/**
 * Minimal, safe .env writer for the developer settings page.
 *
 * Writes go to the .env FILE so the values survive redeploys the same way
 * a manual edit would (unlike the DB settings, which live in the settings
 * table). The file is written atomically (tmp + rename) and the original
 * content is preserved byte-for-byte except for the replaced keys.
 *
 * After a successful write, config caches are cleared so the change takes
 * effect on the very next request.
 */
final class EnvEditor
{
    /** @return string the raw .env content ('' when missing/unreadable) */
    public static function raw(): string
    {
        $path = static::path();

        try {
            return is_file($path) ? (string) file_get_contents($path) : '';
        } catch (Throwable) {
            return '';
        }
    }

    /** Current value of a key as written in the .env file (or null). */
    public static function get(string $key): ?string
    {
        $content = static::raw();

        if ($content === '') {
            return null;
        }

        foreach (preg_split('/\R/', $content) ?: [] as $line) {
            if (preg_match('/^\s*'.preg_quote($key, '/').'\s*=\s*(.*?)\s*$/', $line, $m)) {
                return static::unquote(trim($m[1]));
            }
        }

        return null;
    }

    /**
     * Write several keys at once. Returns [ok(bool), error(?string)].
     * Keys that do not exist yet are appended at the end of the file;
     * existing lines are replaced in place (same order, same comments).
     */
    public static function setMany(array $values): array
    {
        $path = static::path();

        if (! is_file($path) || ! is_writable($path)) {
            // Missing .env → build it from the example file when possible.
            $example = dirname($path).'/.env.example';

            if (! is_file($path) && is_file($example) && @copy($example, $path) === false) {
                return [false, 'فایل .env وجود ندارد یا قابل نوشتن نیست.'];
            }

            if (is_file($path) && ! is_writable($path)) {
                return [false, 'فایل .env قابل نوشتن نیست (مجوز دسترسی).'];
            }
        }

        $content = static::raw();

        $lines   = preg_split('/\R/', $content) ?: [];
        $pending = $values;

        // 1) replace existing lines in place
        foreach ($lines as $i => $line) {
            foreach (array_keys($pending) as $key) {
                if (preg_match('/^\s*'.preg_quote($key, '/').'\s*=/', $line)) {
                    $lines[$i] = $key.'='.static::quote($pending[$key]);
                    unset($pending[$key]);

                    break;
                }
            }
        }

        // 2) append keys that were not present yet
        foreach ($pending as $key => $value) {
            $lines[] = $key.'='.static::quote($value);
        }

        $new = implode(PHP_EOL, $lines);

        // keep the trailing newline shape stable
        if ($content !== '' && str_ends_with($content, "\n") && ! str_ends_with($new, "\n")) {
            $new .= "\n";
        }

        // 3) atomic write (tmp file + rename)
        $tmp = $path.'.tmp-'.bin2hex(random_bytes(4));

        try {
            if (file_put_contents($tmp, $new, LOCK_EX) === false) {
                @unlink($tmp);

                return [false, 'نوشتن فایل .env ناموفق بود (فضا یا مجوز).'];
            }

            if (! @rename($tmp, $path)) {
                // rename can fail across odd host setups — fall back to direct write
                if (file_put_contents($path, $new, LOCK_EX) === false) {
                    @unlink($tmp);

                    return [false, 'نوشتن فایل .env ناموفق بود (فضا یا مجوز).'];
                }

                @unlink($tmp);
            }
        } catch (Throwable $e) {
            @unlink($tmp);

            return [false, 'نوشتن فایل .env ناموفق بود: '.$e->getMessage()];
        }

        // 4) config caches must go — otherwise the old values stay loaded
        static::refreshConfig();

        return [true, null];
    }

    /** Convenience: write one key. */
    public static function set(string $key, string $value): array
    {
        return static::setMany([$key => $value]);
    }

    /* ───────────────────────── internals ───────────────────────── */

    protected static function path(): string
    {
        return base_path('.env');
    }

    protected static function quote(string $value): string
    {
        $value = trim($value);

        if ($value === '') {
            return '';
        }

        // quote when the value contains spaces or special characters
        if (preg_match('/[\s"\'#]/', $value)) {
            return '"'.str_replace('"', '\\"', $value).'"';
        }

        return $value;
    }

    protected static function unquote(string $value): string
    {
        if (strlen($value) >= 2 && str_starts_with($value, '"') && str_ends_with($value, '"')) {
            $value = substr($value, 1, -1);

            return str_replace('\\"', '"', $value);
        }

        return $value;
    }

    /** Clear config caches so the next request sees the new values. */
    protected static function refreshConfig(): void
    {
        try {
            \Artisan::call('config:clear');
        } catch (Throwable) {
            // best effort — shared hosts without a writable bootstrap/cache
        }
    }
}
