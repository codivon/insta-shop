<?php

namespace App\Support;

use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

/**
 * Gregorian → Jalali (Solar Hijri) conversion without any external package.
 * Classic jalaali algorithm; output is already in Persian digits.
 */
class JDate
{
    private const FA_DIGITS = [
        '0' => '۰', '1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴',
        '5' => '۵', '6' => '۶', '7' => '۷', '8' => '۸', '9' => '۹',
    ];

    private const MONTHS = [
        1 => 'فروردین', 2 => 'اردیبهشت', 3 => 'خرداد', 4 => 'تیر', 5 => 'مرداد',
        6 => 'شهریور', 7 => 'مهر', 8 => 'آبان', 9 => 'آذر', 10 => 'دی',
        11 => 'بهمن', 12 => 'اسفند',
    ];

    /** [jy, jm, jd] for the given Gregorian date. */
    public static function toJalali(int $gy, int $gm, int $gd): array
    {
        $gDaysInMonth = [0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334];

        $jy = ($gy <= 1600) ? 0 : 979;
        $gy -= ($gy <= 1600) ? 621 : 1600;
        $gy2 = ($gm > 2) ? ($gy + 1) : $gy;

        $days = (365 * $gy)
            + intdiv($gy2 + 3, 4)
            - intdiv($gy2 + 99, 100)
            + intdiv($gy2 + 399, 400)
            - 80
            + $gd
            + $gDaysInMonth[$gm - 1];

        $jy += 33 * intdiv($days, 12053);
        $days %= 12053;
        $jy += 4 * intdiv($days, 1461);
        $days %= 1461;

        if ($days > 365) {
            $jy += intdiv($days - 1, 365);
            $days = ($days - 1) % 365;
        }

        $jm = ($days < 186) ? 1 + intdiv($days, 31) : 7 + intdiv($days - 186, 30);
        $jd = 1 + (($days < 186) ? ($days % 31) : (($days - 186) % 30));

        return [$jy, $jm, $jd];
    }

    /** «۱۴۰۵/۰۸/۰۹» */
    public static function date(CarbonInterface $date): string
    {
        [$jy, $jm, $jd] = self::toJalali((int) $date->format('Y'), (int) $date->format('n'), (int) $date->format('j'));

        return self::fa(sprintf('%04d/%02d/%02d', $jy, $jm, $jd));
    }

    /** «۱۴۰۵/۰۸/۰۹ — ۲۳:۲۱» */
    public static function dateTime(CarbonInterface $date): string
    {
        return self::date($date).' — '.self::fa($date->format('H:i'));
    }

    /** «۹ آبان ۱۴۰۵» (long, human-friendly form) */
    public static function long(CarbonInterface $date): string
    {
        [$jy, $jm, $jd] = self::toJalali((int) $date->format('Y'), (int) $date->format('n'), (int) $date->format('j'));

        return self::fa($jd).' '.self::MONTHS[$jm].' '.self::fa($jy);
    }

    /** «چند لحظه پیش» / «۵ دقیقه پیش» / «۳ ساعت پیش» / «دیروز» / تاریخ کامل */
    public static function ago(CarbonInterface $date): string
    {
        $diff = (int) abs($date->timestamp - time());

        return match (true) {
            $diff < 45        => 'چند لحظه پیش',
            $diff < 90        => 'یک دقیقه پیش',
            $diff < 3600      => self::fa((string) (int) round($diff / 60)).' دقیقه پیش',
            $diff < 7200      => 'یک ساعت پیش',
            $diff < 86400     => self::fa((string) (int) round($diff / 3600)).' ساعت پیش',
            $diff < 172800    => 'دیروز',
            default           => self::dateTime($date),
        };
    }

    /** Persian month name by number (۱..۱۲ accepted). */
    public static function monthName(int $jm): string
    {
        return self::MONTHS[max(1, min(12, $jm))];
    }

    /** Jalali (Solar Hijri) → Gregorian [gy, gm, gd]. Classic jalaali algorithm. */
    public static function toGregorian(int $jy, int $jm, int $jd): array
    {
        $jy += 1595;

        $days = -355668
            + (365 * $jy)
            + (intdiv($jy, 33) * 8)
            + intdiv(($jy % 33) + 3, 4)
            + $jd
            + (($jm < 7) ? ($jm - 1) * 31 : (($jm - 7) * 30) + 186);

        $gy = 400 * intdiv($days, 146097);
        $days %= 146097;

        if ($days > 36524) {
            $gy += 100 * intdiv(--$days, 36524);
            $days %= 36524;

            if ($days >= 365) {
                $days++;
            }
        }

        $gy += 4 * intdiv($days, 1461);
        $days %= 1461;

        if ($days > 365) {
            $gy += intdiv($days - 1, 365);
            $days = ($days - 1) % 365;
        }

        $gd    = $days + 1;
        $leap  = ($gy % 4 === 0 && $gy % 100 !== 0) || ($gy % 400 === 0);
        $gDays = [0, 31, $leap ? 29 : 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];

        $gm = 0;

        for ($gm = 1; $gm <= 12 && $gd > $gDays[$gm]; $gm++) {
            $gd -= $gDays[$gm];
        }

        return [$gy, $gm, $gd];
    }

    /**
     * Parse a panel date input into a Carbon instance at 00:00.
     * Accepts BOTH:
     *   • Gregorian ISO from the datepicker's machine value («2026-08-23»)
     *   • hand-typed Jalali dates («۱۴۰۵/۰۶/۰۱», Persian or Latin digits)
     * Years ≥ 1900 are Gregorian; anything below is treated as Jalali.
     * Returns null when the input is not a valid date.
     */
    public static function parse(string $value): ?\Illuminate\Support\Carbon
    {
        $value = strtr(trim($value), array_flip(self::FA_DIGITS));

        if (! preg_match('/^(\d{4})[\/\-.](\d{1,2})[\/\-.](\d{1,2})$/', $value, $matches)) {
            return null;
        }

        $y = (int) $matches[1];
        $m = (int) $matches[2];
        $d = (int) $matches[3];

        if ($m < 1 || $m > 12 || $d < 1 || $d > 31) {
            return null;
        }

        try {
            if ($y >= 1900) {
                // Gregorian ISO straight from the datepicker
                return \Illuminate\Support\Carbon::create($y, $m, $d, 0, 0, 0);
            }

            [$gy, $gm, $gd] = self::toGregorian($y, $m, $d);

            return \Illuminate\Support\Carbon::create($gy, $gm, $gd, 0, 0, 0);
        } catch (\Throwable) {
            return null;
        }
    }

    /** Jalali month key «۱۴۰۵-۰۶» (string key for grouping, Persian digits). */
    public static function monthKey(CarbonInterface $date): string
    {
        [$jy, $jm] = self::toJalali((int) $date->format('Y'), (int) $date->format('n'), (int) $date->format('j'));

        return sprintf('%04d-%02d', $jy, $jm);
    }

    private static function fa(string $value): string
    {
        return strtr($value, self::FA_DIGITS);
    }
}
