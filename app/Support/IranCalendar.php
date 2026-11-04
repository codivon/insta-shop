<?php

namespace App\Support;

use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

/**
 * Iranian calendar helpers for «انتخاب توسط کاربر» delivery days.
 *
 * The seller picks a start day (مثلاً از ۵ روز بعد), a window length
 * (۷ / ۱۰ / ۱۵ / ۲۰ روزه) and can exclude جمعه‌ها and تعطیلات رسمی —
 * this class turns those settings into the concrete list of days the
 * customer chooses from at checkout.
 */
class IranCalendar
{
    /** Persian weekday names, indexed by Carbon::dayOfWeek (0=Sunday). */
    private const WEEKDAYS = [
        0 => 'یکشنبه',
        1 => 'دوشنبه',
        2 => 'سه‌شنبه',
        3 => 'چهارشنبه',
        4 => 'پنجشنبه',
        5 => 'جمعه',
        6 => 'شنبه',
    ];

    /** Whether the date falls on a Friday (جمعه). */
    public static function isFriday(CarbonInterface $date): bool
    {
        return (int) $date->dayOfWeek === Carbon::FRIDAY;
    }

    /**
     * Whether the date is an official holiday per the bundled holiday
     * file of ITS OWN Jalali year (Solar + Lunar holidays move together
     * with the year). null = no file for that year → unknown.
     */
    public static function isOfficialHoliday(CarbonInterface $date): ?bool
    {
        [$jy, $jm, $jd] = JDate::toJalali(
            (int) $date->format('Y'),
            (int) $date->format('n'),
            (int) $date->format('j'),
        );

        return IranHolidays::isHoliday($jy, $jm, $jd);
    }

    /** The holiday's description («جشن نوروز» …) — null when not one. */
    public static function holidayDescription(CarbonInterface $date): ?string
    {
        [$jy, $jm, $jd] = JDate::toJalali(
            (int) $date->format('Y'),
            (int) $date->format('n'),
            (int) $date->format('j'),
        );

        return IranHolidays::description($jy, $jm, $jd);
    }

    /** «شنبه» / «یکشنبه» / … for a date. */
    public static function weekdayName(CarbonInterface $date): string
    {
        return self::WEEKDAYS[(int) $date->dayOfWeek] ?? '';
    }

    /**
     * «۹ آبان» — the day + month WITHOUT the year (chip-friendly).
     */
    public static function shortLabel(CarbonInterface $date): string
    {
        [$jy, $jm, $jd] = JDate::toJalali(
            (int) $date->format('Y'),
            (int) $date->format('n'),
            (int) $date->format('j'),
        );

        return self::toFaDigits($jd).' '.JDate::monthName($jm);
    }

    /**
     * «پنجشنبه ۹ آبان ۱۴۰۵» — the full human label of a delivery day.
     */
    public static function longLabel(CarbonInterface $date): string
    {
        return self::weekdayName($date).' '.JDate::long($date);
    }

    /**
     * Build the selectable delivery days.
     *
     * Starts `$startDaysAfterOrder` days from TODAY, skips disabled
     * Fridays / official holidays and keeps walking forward until
     * `$count` selectable days are collected (bounded so a pathological
     * configuration can never loop forever).
     *
     * Holidays come from the bundled per-year files — a day whose year
     * file is missing is treated as UNKNOWN (never skipped), so a window
     * crossing Nowruz degrades gracefully instead of failing.
     *
     * @return array<int, array{date: string, weekday: string, jdate: string, short: string, long: string, holiday: ?string}>
     */
    public static function deliveryDays(int $startDaysAfterOrder, int $count, bool $skipFridays = false, bool $skipHolidays = false): array
    {
        $count = max(1, min(31, $count));

        $days = [];
        $cursor = Carbon::today()->addDays(max(1, $startDaysAfterOrder));
        $guard = 0;

        while (count($days) < $count && $guard++ < 200) {
            if ($skipFridays && self::isFriday($cursor)) {
                $cursor->addDay();

                continue;
            }

            if ($skipHolidays && self::isOfficialHoliday($cursor) === true) {
                $cursor->addDay();

                continue;
            }

            $days[] = [
                'date' => $cursor->toDateString(),
                'weekday' => self::weekdayName($cursor),
                'jdate' => self::shortLabel($cursor),
                'short' => self::weekdayName($cursor).' '.JDate::long($cursor),
                'long' => self::longLabel($cursor),
                'holiday' => $skipHolidays ? null : self::holidayDescription($cursor),
            ];

            $cursor->addDay();
        }

        return $days;
    }

    /** Validate that a picked Y-m-d is really one of the allowed days. */
    public static function isAllowedPick(array $allowedDays, ?string $picked): bool
    {
        if ($picked === null || $picked === '') {
            return false;
        }

        foreach ($allowedDays as $day) {
            if (($day['date'] ?? '') === $picked) {
                return true;
            }
        }

        return false;
    }

    /** Latin → Persian digits (tiny local helper). */
    private static function toFaDigits(int|string $value): string
    {
        return strtr((string) $value, [
            '0' => '۰', '1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴',
            '5' => '۵', '6' => '۶', '7' => '۷', '8' => '۸', '9' => '۹',
        ]);
    }
}
