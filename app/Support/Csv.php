<?php

namespace App\Support;

/**
 * CSV formula-injection guard for spreadsheet exports.
 *
 * Seller/customer-authored strings (product names, shop names, ticket
 * texts, descriptions) land inside .csv files that ADMINS open in Excel.
 * A cell starting with = + - @ TAB or CR is interpreted by Excel/Sheets
 * as a FORMULA — e.g. =HYPERLINK("http://evil","کلیک") or
 * =CMD|'/C calc'!A0 — turning a report into a phishing/attack surface.
 * Neutralizing cells prefixes a single quote, which every spreadsheet
 * renders harmlessly.
 */
class Csv
{
    /** Characters Excel treats as formula starters. */
    protected const DANGEROUS = ['=', '+', '-', '@', "\t", "\r"];

    public static function safe(mixed $value): mixed
    {
        if (! is_string($value) || $value === '') {
            return $value;
        }

        if (in_array($value[0], self::DANGEROUS, true)) {
            return "'".$value;
        }

        return $value;
    }

    /** Apply safe() to every string cell of a row array. */
    public static function row(array $cells): array
    {
        return array_map(self::safe(...), $cells);
    }
}
