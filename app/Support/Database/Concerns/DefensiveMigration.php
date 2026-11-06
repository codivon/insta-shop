<?php

namespace App\Support\Database\Concerns;

use Closure;
use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
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
                // A leftover VIEW with the same name silently blocks the
                // Schema::create() that follows (1050 «table already exists»).
                // After the table drop, any object still holding the name can
                // only be a view — clear it (portable via getViews(), then a
                // grammar-wrapped DROP so MySQL backticks / SQLite quotes fit).
                $connection = Schema::getConnection();

                $viewLeft = collect($connection->getSchemaBuilder()->getViews())
                    ->contains(fn ($view) => strcasecmp((string) ($view['name'] ?? ''), $table) === 0);

                if ($viewLeft) {
                    $connection->statement('DROP VIEW IF EXISTS '.$connection->getQueryGrammar()->wrapTable($table));
                }
            }
        } finally {
            Schema::enableForeignKeyConstraints();
        }
    }

    /**
     * Add a foreign key only when it is missing — with in-place retries.
     *
     * WHY THIS EXISTS (v0.21.2): Laravel emits Schema::create() as
     * «CREATE TABLE …» (no FK) followed by «ALTER TABLE … ADD CONSTRAINT»
     * for every ->constrained(). On flaky shared-hosting MySQL the ALTER —
     * the SECOND statement touching a freshly created table — can 1146
     * («table doesn't exist», the documented settings-table class of
     * failures) and the installer's whole-file retry then re-drops and
     * re-runs into the same window. Building the table WITHOUT the inline
     * FK and adding it through THIS helper instead:
     *   • keeps every retry cheap (only the ALTER re-fires, the fresh
     *     table is never dropped again),
     *   • re-checks the table per attempt (a stale second wizard tab
     *     dropping the table mid-flight becomes a rethrow, not a hang),
     *   • stays idempotent (existing FK → immediate return).
     *
     * @param  string|list<string>  $columns
     * @param  string|list<string>  $refColumns
     */
    protected static function foreignKeyIfMissing(
        string $table,
        string|array $columns,
        string $refTable,
        string|array $refColumns,
        ?string $name = null,
        string $onDelete = 'cascade',
    ): void {
        if (! Schema::hasTable($table)) {
            throw new RuntimeException("foreignKeyIfMissing: table «{$table}» does not exist — create it before adding its FK.");
        }

        $prefix = Schema::getConnection()->getTablePrefix();
        $name ??= $prefix.str_replace('.', '_', strtolower("{$table}_".implode('_', (array) $columns).'_foreign'));

        $existing = collect(Schema::getForeignKeys($table))
            ->pluck('name')
            ->map(fn ($n) => strtolower((string) $n));

        if ($existing->contains(strtolower($name))) {
            return; // already there (a healthier earlier attempt added it)
        }

        $attempts = 0;

        while (true) {
            try {
                Schema::table($table, function (Blueprint $blueprint) use ($columns, $refTable, $refColumns, $name, $onDelete): void {
                    // The name goes through foreign()'s SECOND argument —
                    // calling ->name() on a ForeignKeyDefinition would
                    // OVERWRITE the command's own name ('foreign') and
                    // Blueprint::toSql() would then silently skip the
                    // statement (no compile<custom-name> method exists).
                    $fk = $blueprint->foreign($columns, $name)->references($refColumns)->on($refTable);

                    match ($onDelete) {
                        'cascade' => $fk->cascadeOnDelete(),
                        'null' => $fk->nullOnDelete(),
                        'restrict' => $fk->restrictOnDelete(),
                        default => $fk->onDelete($onDelete),
                    };
                });

                return;
            } catch (Throwable $e) {
                // The table vanished mid-flight (stale tab / dictionary lag)
                // — nothing an in-place retry can fix; let the caller's
                // whole-file retry rebuild it.
                if (! Schema::hasTable($table)) {
                    throw $e;
                }

                $code = (int) ($e instanceof QueryException ? ($e->errorInfo[1] ?? 0) : 0);
                $retryable = in_array($code, [1146, 1205, 1213, 1215, 1822], true);

                if (! $retryable || ++$attempts > 2) {
                    throw $e;
                }

                usleep($attempts === 1 ? 250_000 : ($attempts === 2 ? 500_000 : 1_000_000));
            }
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
