<?php

namespace App\Support;

use Illuminate\Support\Facades\Schema;

/**
 * Tiny helper for code that must not explode when an optional table has
 * not been migrated yet (fresh installs mid-wizard, admin visiting the
 * panel right after a partial deploy, ...).
 */
class SchemaReady
{
    /** Does the given table exist (result cached for the request)? */
    public static function check(string $table): bool
    {
        static $cache = [];

        if (! array_key_exists($table, $cache)) {
            try {
                $cache[$table] = Schema::hasTable($table);
            } catch (\Throwable) {
                $cache[$table] = false;
            }
        }

        return $cache[$table];
    }
}
