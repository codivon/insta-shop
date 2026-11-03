<?php

namespace App\Support;

/**
 * Iran official holidays — driven by the bundled per-year JSON files in
 * «resources/holidays/{جy}.json» (each day of the Jalali year carries an
 * «isHoliday» flag; Fridays are NOT part of these files and are handled
 * separately by IranCalendar::isFriday).
 *
 * Files currently shipped: 1405 … 1410. When a year's file is missing the
 * year is simply «unknown» — callers (seller shipping form, checkout day
 * picker) treat holidays as unknown and disable holiday filtering instead
 * of guessing.
 */
class IranHolidays
{
    /** 'MM/DD' (Jalali) => description, per year — request-level memo. */
    private static array $cache = [];

    /** Years whose file was already probed (missing ones too). */
    private static array $probed = [];

    /** Absolute path of a year's holiday file. */
    public static function filePath(int $jy): string
    {
        return resource_path('holidays/'.$jy.'.json');
    }

    /** Whether the Jalali year has a holiday file bundled. */
    public static function exists(int $jy): bool
    {
        if (isset(self::$probed[$jy])) {
            return self::$probed[$jy];
        }

        return self::$probed[$jy] = is_file(self::filePath($jy));
    }

    /**
     * All bundled years (empty array when nothing is shipped).
     *
     * @return array<int, int> ascending Jalali years
     */
    public static function availableYears(): array
    {
        $years = [];

        foreach (glob(resource_path('holidays/*.json')) ?: [] as $file) {
            if (preg_match('/(\d{4})\.json$/', $file, $m) === 1) {
                $years[] = (int) $m[1];
            }
        }

        sort($years);

        return $years;
    }

    /**
     * Holiday map of one Jalali year — null when the file is missing.
     *
     * @return array<string, string>|null 'MM/DD' => description
     */
    public static function forYear(int $jy): ?array
    {
        if (array_key_exists($jy, self::$cache)) {
            return self::$cache[$jy];
        }

        if (! self::exists($jy)) {
            return self::$cache[$jy] = null;
        }

        $map = [];

        $raw = json_decode((string) file_get_contents(self::filePath($jy)), true);
        $rows = is_array($raw) ? ($raw['data'] ?? null) : null;

        if (is_array($rows)) {
            foreach ($rows as $row) {
                if (! is_array($row) || empty($row['isHoliday'])) {
                    continue;
                }

                $date = (string) ($row['date'] ?? '');

                // "1405/01/01" → key "01/01" (year is the file itself)
                if (preg_match('#^\d{4}/(\d{2}/\d{2})$#', $date, $m) === 1) {
                    $map[$m[1]] = trim((string) ($row['holidayDesription'] ?? '')) ?: 'تعطیل رسمی';
                }
            }
        }

        return self::$cache[$jy] = $map;
    }

    /**
     * Is the given Jalali date an official holiday?
     *
     * true / false — or null when the year's file is missing (unknown).
     */
    public static function isHoliday(int $jy, int $jm, int $jd): ?bool
    {
        $map = self::forYear($jy);

        if ($map === null) {
            return null;
        }

        return isset($map[sprintf('%02d/%02d', $jm, $jd)]);
    }

    /** Description of the holiday (null = not a holiday / unknown). */
    public static function description(int $jy, int $jm, int $jd): ?string
    {
        $map = self::forYear($jy);

        return $map === null ? null : ($map[sprintf('%02d/%02d', $jm, $jd)] ?? null);
    }

    /** Jalali years a Gregorian window (inclusive) spans. */
    public static function yearsSpanned(\Carbon\CarbonInterface $from, \Carbon\CarbonInterface $to): array
    {
        $years = [];
        $cursor = $from->copy()->startOfDay();

        while ($cursor->lte($to)) {
            [$jy] = JDate::toJalali((int) $cursor->format('Y'), (int) $cursor->format('n'), (int) $cursor->format('j'));

            if (! in_array($jy, $years, true)) {
                $years[] = $jy;
            }

            // jump in month steps for speed, then re-check
            $cursor->addMonth();
        }

        [$lastJy] = JDate::toJalali((int) $to->format('Y'), (int) $to->format('n'), (int) $to->format('j'));

        if (! in_array($lastJy, $years, true)) {
            $years[] = $lastJy;
        }

        sort($years);

        return $years;
    }
}
