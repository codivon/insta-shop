<?php

namespace App\Support;

/**
 * SECURITY (v0.20.0) — image payload hardening.
 *
 * Laravel's image/mimes validation is CONTENT-MIME based, so a valid PNG
 * with an appended PHP payload (`...PNG bytes...<?php system($_GET["c"]);`)
 * passes every stock rule. The file is then stored on the PUBLIC disk as
 * {random}.png. A sane server never executes .png — but on shared cPanel
 * boxes a single wrong handler line (AddHandler application/x-httpd-php
 * .png, a "cache" hack, a pooled php-fpm over storage) turns that file
 * into remote code execution. This guard scans the RAW bytes of every
 * image that is about to land on a web-served disk for PHP-executable
 * markers and rejects the upload when any is present.
 *
 * Only high-signal needles are checked (<?php, <?=, phar://,
 * __HALT_COMPILER) — a generic `<?` check would false-positive on random
 * binary data inside compressed images.
 */
class ImagePayload
{
    /** Marker needles that must never appear inside an image file. */
    private const NEEDLES = ['<?php', '<?=', 'phar://', '__HALT_COMPILER'];

    /** Upper bound for the scan — validation rules cap images at ≤5MB. */
    private const MAX_SCAN = 8 * 1024 * 1024;

    /** True when the file's raw bytes contain no PHP-executable markers. */
    public static function clean(mixed $file): bool
    {
        $bytes = self::bytes($file);

        if ($bytes === null) {
            return false;
        }

        foreach (self::NEEDLES as $needle) {
            if (stripos($bytes, $needle) !== false) {
                return false;
            }
        }

        return true;
    }

    /** Laravel validation rule (null-safe) for use inside every image rule array. */
    public static function rule(): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail) {
            if ($value === null) {
                return;
            }

            if (! self::clean($value)) {
                $fail('فایل تصویر معتبر نیست (محتوای غیرمجاز شناسایی شد).');
            }
        };
    }

    /** Raw byte string of the uploaded file, or null when unreadable. */
    private static function bytes(mixed $file): ?string
    {
        try {
            if (! is_object($file) || ! method_exists($file, 'getRealPath')) {
                return null;
            }

            $path = $file->getRealPath();

            if (! is_string($path) || ! is_file($path)) {
                return null;
            }

            $handle = @fopen($path, 'rb');

            if ($handle === false) {
                return null;
            }

            $buffer = '';

            while (! feof($handle) && strlen($buffer) <= self::MAX_SCAN) {
                $chunk = fread($handle, 65536);

                if ($chunk === false) {
                    break;
                }

                $buffer .= $chunk;
            }

            fclose($handle);

            return $buffer;
        } catch (\Throwable) {
            return null;
        }
    }
}
