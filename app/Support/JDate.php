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

    private static function fa(string $value): string
    {
        return strtr($value, self::FA_DIGITS);
    }
}
