<?php

namespace App\Support;

/**
 * Formatting helpers for the Persian UI:
 * Latin digits → Persian digits, thousand separators, money strings.
 */
class Fmt
{
    private const FA_DIGITS = [
        '0' => '۰', '1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴',
        '5' => '۵', '6' => '۶', '7' => '۷', '8' => '۸', '9' => '۹',
    ];

    /** Any Latin/Persian/Arabic digits + separators → plain Latin digits string. */
    public static function toLatin(null|string|int|float $value): string
    {
        $value = (string) $value;

        // Persian ۰-۹ (U+06F0..U+06F9) and Arabic ٠-٩ (U+0660..U+0669)
        $value = strtr($value, [
            '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
            '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
            '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
            '٬' => '', '،' => '', ',' => '', ' ' => '', '،' => '',
        ]);

        return preg_replace('/[^0-9]/', '', $value) ?? '';
    }

    /** Latin digits → Persian digits. */
    public static function toFa(null|string|int|float $value): string
    {
        return strtr((string) $value, self::FA_DIGITS);
    }

    /** 1234567 → «۱٬۲۳۴٬۵۶۷» (Persian digits + Persian thousand separator). */
    public static function fa(null|string|int|float $number): string
    {
        return self::toFa(number_format((float) $number, 0, '.', '٬'));
    }

    /** Money string: «۱۲۳٬۴۵۶ تومان». */
    public static function money(null|string|int|float $amount): string
    {
        return self::fa($amount).' تومان';
    }

    /**
     * Money digits WITHOUT the «تومان» suffix — always exact,
     * never abbreviated: 120000 → «۱۲۰٬۰۰۰» (was «۱۲۰ه», which
     * read as broken/unclear to users and is now retired).
     */
    public static function moneyShort(int|float $amount): string
    {
        return self::fa($amount);
    }
}
