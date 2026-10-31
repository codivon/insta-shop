<?php

namespace App\Support;

/**
 * Color utilities — generate a full 50→950 shade scale from a single hex,
 * in the spirit of Tailwind's palettes, so admin-chosen colors propagate
 * everywhere through CSS variables.
 */
class Color
{
    public const STEPS = [50, 100, 200, 300, 400, 500, 600, 700, 800, 900, 950];

    /** @var array<int, float> percent toward white for lighter steps */
    protected const WHITE_MIX = [
        50 => 0.96, 100 => 0.92, 200 => 0.84, 300 => 0.70, 400 => 0.46,
    ];

    /** @var array<int, float> percent toward black for darker steps */
    protected const BLACK_MIX = [
        600 => 0.13, 700 => 0.28, 800 => 0.42, 900 => 0.56, 950 => 0.70,
    ];

    public static function isHex(string $value): bool
    {
        return (bool) preg_match('/^#?[0-9a-fA-F]{6}$/', trim($value));
    }

    public static function normalize(string $hex): string
    {
        $hex = ltrim(trim($hex), '#');

        return '#'.strtoupper($hex);
    }

    /** @return array{r:int,g:int,b:int} */
    public static function toRgb(string $hex): array
    {
        $hex = ltrim(self::normalize($hex), '#');

        return [
            'r' => hexdec(substr($hex, 0, 2)),
            'g' => hexdec(substr($hex, 2, 2)),
            'b' => hexdec(substr($hex, 4, 2)),
        ];
    }

    public static function toHex(array $rgb): string
    {
        return sprintf('#%02X%02X%02X', $rgb['r'], $rgb['g'], $rgb['b']);
    }

    /** Mix hex toward white (factor>0) or black (factor<0). */
    public static function mix(string $hex, float $factor): string
    {
        $rgb = self::toRgb($hex);
        $target = $factor >= 0 ? ['r' => 255, 'g' => 255, 'b' => 255] : ['r' => 0, 'g' => 0, 'b' => 0];
        $t = abs($factor);

        foreach ($rgb as $ch => $v) {
            $rgb[$ch] = (int) round($v * (1 - $t) + $target[$ch] * $t);
        }

        return self::toHex($rgb);
    }

    /** Full shade scale keyed by step (50…950). */
    public static function shades(string $hex): array
    {
        $hex = self::normalize($hex);
        $out = [];

        foreach (self::STEPS as $step) {
            $out[$step] = match (true) {
                $step === 500   => $hex,
                isset(self::WHITE_MIX[$step]) => self::mix($hex, self::WHITE_MIX[$step]),
                default         => self::mix($hex, -self::BLACK_MIX[$step]),
            };
        }

        return $out;
    }

    /** Perceptual luminance → best readable foreground on this color. */
    public static function onColor(string $hex): string
    {
        ['r' => $r, 'g' => $g, 'b' => $b] = self::toRgb($hex);
        $luminance = (0.299 * $r + 0.587 * $g + 0.114 * $b) / 255;

        return $luminance > 0.62 ? '#0F172A' : '#FFFFFF';
    }
}
