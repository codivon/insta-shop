<?php

namespace App\Support\Database\Concerns;

use Closure;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Throwable;

/**
 * Idempotent migration helpers for flaky shared-hosting MySQL.
 *
 * On the client's host the MySQL server occasionally dies on the very
 * first statement that touches a freshly created table (42S02/1146 «table
 * doesn't exist», see the settings-table incident of v0.17.11) or loses a
 * statement to a lock (1205/1213). MySQL DDL commits per statement, so a
 * migration that crashes half-way leaves EARLY statements of its file
 * applied while the `migrations` row is never written — re-running the
 * same file then died with 42S01/1050 «table already exists» (the
 * stories-table incident of v0.17.12).
 *
 * Every migration therefore declares its own defensive strategy:
 *
 *  - migrations that CREATE tables start with self::recreate(...) —
 *    foreign-key checks off, drop leftovers, then build fresh;
 *  - migrations that ALTER tables guard every step with
 *    self::columnIfMissing() / self::indexIfMissing().
 *
 * Re-running any migration file is now ALWAYS safe, which is exactly what
 * the installer's one-shot retry (and the wizard's in-place retry button)
 * relies on.
 */
trait DefensiveMigration
{
    /**
     * Drop leftover copies of the tables this migration creates, with
     * foreign-key checks suspended so drop order never matters (children
     * created by later files of the same partial install cannot block).
     */
    protected static function recreate(string ...$tables): void
    {
        if ($tables === []) {
            return;
        }

        Schema::disableForeignKeyConstraints();

        try {
            foreach ($tables as $table) {
                Schema::dropIfExists($table);
            }
        } finally {
            Schema::enableForeignKeyConstraints();
        }
    }

    /**
     * Add one column only when it is missing (a previous partial run may
     * have applied the ALTER before crashing — DDL commits per statement).
     *
     * @param  Closure(Blueprint): mixed  $definition
     */
    protected static function columnIfMissing(string $table, string $column, Closure $definition): void
    {
        if (Schema::hasColumn($table, $column)) {
            return;
        }

        Schema::table($table, fn (Blueprint $blueprint) => $definition($blueprint));
    }

    /**
     * Add an index/unique only when an index with that name is missing.
     *
     * @param  Closure(Blueprint): mixed  $add
     */
    protected static function indexIfMissing(string $table, string $name, Closure $add, string $type = 'index'): void
    {
        try {
            if (Schema::hasIndex($table, $name, $type)) {
                return; // already there (this up() ran on a healthier attempt before)
            }

            Schema::table($table, $add);
        } catch (Throwable $e) {
            report($e);
            logger()->warning("InstaShop install: index «{$name}» on «{$table}» skipped — {$e->getMessage()}");
        }
    }

    /**
     * Is the column nullable? (portable across MySQL / SQLite via the
     * schema introspection API — used before re-issuing ->change() calls).
     */
    protected static function columnIsNullable(string $table, string $column): bool
    {
        foreach (Schema::getColumns($table) as $meta) {
            if (($meta['name'] ?? null) === $column) {
                return (bool) ($meta['nullable'] ?? false);
            }
        }

        return false;
    }
}
