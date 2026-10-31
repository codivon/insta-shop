<?php

namespace App\Support;

/**
 * Iranian mobile number normalization.
 * Storage format (DB): 989XXXXXXXXX (E.164-ish, matches sellers/customers
 * migration). Providers usually want the local 09XXXXXXXXX form.
 */
class Phone
{
    /** Normalize any common Iranian mobile input to 989XXXXXXXXX. */
    public static function normalize(?string $input): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $input);

        if ($digits === '') {
            return null;
        }

        return match (true) {
            str_starts_with($digits, '0098') => '98'.substr($digits, 4),
            str_starts_with($digits, '98')   => $digits,
            str_starts_with($digits, '0')    => '98'.substr($digits, 1),
            default                          => '98'.$digits,
        };
    }

    /** Storage form (989XXXXXXXXX) → provider form (09XXXXXXXXX). */
    public static function toLocal(?string $normalized): string
    {
        $normalized = preg_replace('/\D+/', '', (string) $normalized);

        if (str_starts_with((string) $normalized, '98')) {
            return '0'.substr($normalized, 2);
        }

        return (string) $normalized;
    }

    /** 989XXXXXXXXX → 0912 345 6789 (pretty, for UI display). */
    public static function pretty(?string $normalized): string
    {
        $local = self::toLocal($normalized);

        if (strlen($local) !== 11) {
            return $local;
        }

        return substr($local, 0, 4).' '.substr($local, 4, 3).' '.substr($local, 7, 4);
    }

    /** Validate a normalized Iranian mobile number. */
    public static function isValid(?string $normalized): bool
    {
        return (bool) preg_match('/^989\d{9}$/', (string) $normalized);
    }

    /** Validate raw user input (before normalization). */
    public static function isRawValid(?string $input): bool
    {
        $digits = preg_replace('/\D+/', '', (string) $input);

        return (bool) preg_match('/^(?:09\d{9}|989\d{9}|00989\d{9}|\+?989\d{9})$/', (string) $input)
            && strlen($digits) >= 10;
    }
}
