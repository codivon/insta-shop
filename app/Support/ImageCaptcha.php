<?php

namespace App\Support;

/**
 * Image captcha for the admin login (GD-drawn PNG).
 *
 * The old «math» challenge printed its own operands into the DOM, so any
 * script could read ۷ + ۳ and submit the sum — a captcha a for-loop
 * solves. Here the challenge is a 5-character code rendered into a noisy
 * PNG: the answer only exists in the session, and reading it back
 * requires real OCR.
 *
 * The code never leaves the server; the browser receives a base64
 * data-URI that is rebuilt (from the SAME session code + a stable noise
 * seed) on every render, so Livewire re-renders never change the puzzle.
 *
 * Fallback: when GD or the bundled font is missing (rare hosts),
 * available() returns false and the login keeps the legacy math
 * challenge — the admin must never be locked out of the panel.
 */
class ImageCaptcha
{
    public const SESSION_KEY  = 'admin_captcha_code';

    public const SESSION_SEED = 'admin_captcha_seed';

    /** 5 characters — DIGITS ONLY. Ambiguous glyphs (0, 1) excluded, so the
     *  code is always typeable from a phone's NUMERIC keyboard. */
    private const ALPHABET = '23456789';

    public static function fontPath(): string
    {
        return resource_path('captcha/DejaVuSans-Bold.ttf');
    }

    /** True when the image captcha can actually be drawn on this host. */
    public static function available(): bool
    {
        return function_exists('imagefttext')
            && function_exists('imagecreatetruecolor')
            && is_file(self::fontPath());
    }

    /** Fresh challenge into the session (+ its stable noise seed). */
    public static function issue(): void
    {
        $code = '';

        for ($i = 0; $i < 5; $i++) {
            $code .= self::ALPHABET[random_int(0, strlen(self::ALPHABET) - 1)];
        }

        session([
            self::SESSION_KEY  => $code,
            self::SESSION_SEED => random_int(1, PHP_INT_MAX),
        ]);
    }

    public static function forget(): void
    {
        session()->forget([self::SESSION_KEY, self::SESSION_SEED]);
    }

    /** Persian/Arabic digits → Latin, lowercase, spaces and noise stripped. */
    public static function normalize(string $input): string
    {
        $normalized = strtr(trim($input), [
            '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
            '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
            '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
        ]);

        return mb_strtolower(preg_replace('/[^a-z0-9]/i', '', $normalized));
    }

    /** Constant-time compare of the typed answer against the session code. */
    public static function verify(string $input): bool
    {
        $expected = (string) session(self::SESSION_KEY, '');

        return $expected !== ''
            && hash_equals($expected, self::normalize($input));
    }

    /** PNG data-URI of the session code — '' when no challenge is live. */
    public static function dataUri(): string
    {
        $code = (string) session(self::SESSION_KEY, '');

        if ($code === '' || ! self::available()) {
            return '';
        }

        // deterministic noise for this challenge's whole lifetime
        mt_srand((int) session(self::SESSION_SEED, random_int(1, PHP_INT_MAX)));

        try {
            $png = self::draw($code);
        } catch (\Throwable) {
            return '';
        } finally {
            mt_srand(); // restore entropy
        }

        return $png !== '' ? 'data:image/png;base64,'.base64_encode($png) : '';
    }

    /** Render the code into a noisy PNG (GD) and return the binary string. */
    private static function draw(string $code): string
    {
        $width  = 190;
        $height = 60;

        $img = imagecreatetruecolor($width, $height);

        if (! $img) {
            return '';
        }

        // soft light background (fits the light captcha box in both themes)
        $bg = imagecolorallocate($img, 246, 248, 251);
        imagefill($img, 0, 0, $bg);

        // two faint diagonal washes for texture
        $washA = imagecolorallocate($img, 236, 240, 246);
        $washB = imagecolorallocate($img, 228, 234, 243);
        imagefilledpolygon($img, [0, $height, 0, (int) ($height * 0.55), (int) ($width * 0.4), $height], $washA);
        imagefilledpolygon($img, [$width, 0, $width, (int) ($height * 0.5), (int) ($width * 0.55), 0], $washB);

        // scattered dots
        for ($i = 0; $i < 160; $i++) {
            $dot = imagecolorallocate($img, random_int(160, 215), random_int(165, 215), random_int(175, 225));
            imagefilledrectangle($img, random_int(0, $width), random_int(0, $height), random_int(0, $width), random_int(0, $height), $dot);
        }

        // interfering arcs and lines
        $palette = [
            [92, 114, 158], [148, 108, 76], [96, 138, 108], [136, 92, 140],
            [86, 130, 160], [156, 98, 96], [104, 104, 150], [110, 128, 90],
        ];

        for ($i = 0; $i < 5; $i++) {
            [$r, $g, $b] = $palette[random_int(0, count($palette) - 1)];
            $line = imagecolorallocate($img, $r, $g, $b);
            imagesetthickness($img, random_int(1, 2));
            imageline(
                $img,
                random_int(-15, (int) ($width * 0.3)), random_int(0, $height),
                random_int((int) ($width * 0.7), $width + 15), random_int(0, $height),
                $line,
            );
        }

        // the characters — each tilted its own way
        $font  = self::fontPath();
        $count = mb_strlen($code);
        $slot  = ($width - 24) / $count;

        for ($i = 0; $i < $count; $i++) {
            $char  = mb_substr($code, $i, 1);
            $size  = random_int(21, 25);
            $angle = random_int(-20, 20);

            [$r, $g, $b] = $palette[random_int(0, count($palette) - 1)];
            $ink = imagecolorallocate($img, $r, $g, $b);

            $box = imagettfbbox($size, $angle, $font, $char);
            $cw  = abs($box[4] - $box[0]);
            $ch  = abs($box[5] - $box[1]);

            $x = (int) (12 + $slot * $i + ($slot - $cw) / 2);
            $y = (int) (($height + $ch) / 2 + random_int(-3, 3));

            imagettftext($img, $size, $angle, $x, $y, $ink, $font, $char);
        }

        // hairline border
        imagerectangle($img, 0, 0, $width - 1, $height - 1, imagecolorallocate($img, 226, 232, 240));

        ob_start();
        $ok = imagepng($img, null, 6);
        $data = $ok ? (string) ob_get_clean() : '';
        // imagedestroy() intentionally omitted — no-op since PHP 8.0 and
        // deprecated (warns) since 8.5; the canvas is garbage-collected.

        return $data;
    }
}
