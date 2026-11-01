<?php

namespace App\Support;

/**
 * Iranian banking helpers for the seller «کارت‌ها» feature:
 * digit normalization, Luhn validation of 16-digit card numbers,
 * mod-97 validation of IR sheba (IBAN) and bank detection from the
 * card BIN. Pure static, no external packages (shared hosting).
 */
final class Banking
{
    /** Well-known Iranian bank BINs (first 6 digits of the card). */
    private const BANK_BINS = [
        '603799' => 'بانک سامان',
        '621986' => 'بانک سامان',
        '589210' => 'بانک سپه',
        '627353' => 'بانک تجارت',
        '585983' => 'بانک تجارت',
        '622106' => 'بانک مسکن',
        '502908' => 'بانک توسعه تعاون',
        '627488' => 'بانک کارآفرین',
        '502806' => 'بانک عمران',
        '603769' => 'بانک صادرات',
        '610433' => 'بانک ملت',
        '627412' => 'بانک اقتصاد نوین',
        '627760' => 'پست بانک',
        '502938' => 'بانک دی',
        '603770' => 'بانک کشاورزی',
        '639217' => 'بانک کیش',
        '606373' => 'بانک مهر ایران',
        '639347' => 'بانک پاسارگاد',
        '502229' => 'بانک پاسارگاد',
        '627388' => 'بانک انصار',
        '639607' => 'بانک سرمد (سامان)',
        '505785' => 'بانک ایران زمین',
        '636214' => 'بانک آینده',
        '628023' => 'بانک مسکن',
        '627648' => 'بانک توسعه صادرات',
        '207177' => 'بانک توسعه صادرات',
        '627354' => 'بانک تجارت',
        '585949' => 'بانک خاورمیانه (مید)',
        '502910' => 'بانک کارآفرین',
        '603764' => 'بانک مرکزی',
        '589463' => 'بانک رفاه',
        '502800' => 'بانک صنعت و معدن',
    ];

    /* ───────────────────────── normalization ───────────────────────── */

    /** Persian/Arabic digits → Latin, then strip everything but [0-9]. */
    public static function digitsOnly(string $value): string
    {
        return Fmt::toLatin($value);
    }

    /** Persian/Arabic digits → Latin, KEEP the IR letters, drop spaces/dashes. */
    public static function shebaNormalize(string $value): string
    {
        $v = strtr($value, [
            '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
            '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
            '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
        ]);

        $v = preg_replace('/\s+|[-_.]/u', '', $v) ?? '';

        return strtoupper($v);
    }

    /* ─────────────────────────── validation ─────────────────────────── */

    /** 16 digits + Luhn checksum (standard for Iranian bank cards). */
    public static function validCard(string $card): bool
    {
        $card = self::digitsOnly($card);

        if (strlen($card) !== 16 || ! ctype_digit($card)) {
            return false;
        }

        return self::luhn($card);
    }

    private static function luhn(string $number): bool
    {
        $sum = 0;

        $len = strlen($number);

        for ($i = 0; $i < $len; $i++) {
            $digit = (int) $number[$len - 1 - $i];

            if ($i % 2 === 1) {
                $digit *= 2;

                if ($digit > 9) {
                    $digit -= 9;
                }
            }

            $sum += $digit;
        }

        return $sum % 10 === 0;
    }

    /** IR + 24 digits with the official mod-97 (ISO 7064) check. */
    public static function validSheba(string $sheba): bool
    {
        $sheba = self::shebaNormalize($sheba);

        if (! preg_match('/^IR\d{24}$/', $sheba)) {
            return false;
        }

        // Rearrange: IBAN first four chars move to the end, letters → numbers.
        $rearranged = substr($sheba, 4).substr($sheba, 0, 4);
        $numeric    = '';

        foreach (str_split($rearranged) as $char) {
            if (ctype_alpha($char)) {
                $numeric .= (string) (ord(strtoupper($char)) - ord('A') + 10);
            } else {
                $numeric .= $char;
            }
        }

        // Big-int free mod-97 (bcmath is available on the target stack).
        if (function_exists('bcmod')) {
            return bcmod($numeric, '97') === '1';
        }

        // Fallback: chunked modulo (safe for any PHP build).
        $mod = 0;

        foreach (str_split($numeric, 9) as $chunk) {
            $mod = ((int) $mod.$chunk) % 97;
        }

        return $mod === 1;
    }

    /** BIN → bank-name map for the client-side instant card detection (bank-cards form). */
    public static function bins(): array
    {
        return self::BANK_BINS;
    }

    /* ──────────────────────────── naming ───────────────────────────── */

    /** Iranian bank name from the card BIN, or a neutral fallback. */
    public static function bankName(string $card): string
    {
        $card = self::digitsOnly($card);

        if (strlen($card) < 6) {
            return 'کارت بانکی';
        }

        foreach (self::BANK_BINS as $bin => $name) {
            if (str_starts_with($card, $bin)) {
                return $name;
            }
        }

        return 'کارت بانکی';
    }

    /* ─────────────────────────── formatting ────────────────────────── */

    /** «6037 6912 3456 1234» groups of four (display). */
    public static function formatCard(string $card): string
    {
        $card = self::digitsOnly($card);

        return trim(implode(' ', str_split($card, 4))) ?: $card;
    }

    /** «6037 •••• •••• 1234» (privacy display). */
    public static function maskCard(string $card): string
    {
        $card = self::digitsOnly($card);

        if (strlen($card) !== 16) {
            return self::formatCard($card);
        }

        return substr($card, 0, 4).' •••• •••• '.substr($card, -4);
    }

    /** «IR06 2980 0000 0012 3456 7890 01» groups of four (display). */
    public static function formatSheba(string $sheba): string
    {
        $sheba = self::shebaNormalize($sheba);

        return trim(implode(' ', str_split($sheba, 4)));
    }

    /** Last 4 digits of the sheba digits — quick human identifier. */
    public static function shebaTail(string $sheba): string
    {
        $digits = preg_replace('/\D/', '', self::shebaNormalize($sheba)) ?? '';

        return substr($digits, -4);
    }
}
