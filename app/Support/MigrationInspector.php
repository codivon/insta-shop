<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

/**
 * Inspects database/migrations/*.php and reports which ones have not been
 * run yet — including WHAT they will change: new tables (with their
 * parsed column list) and alterations (added columns), so the developer
 * panel can show a human-readable diff before running them.
 *
 * Used by Developer Settings → «دیتابیس و مایگریشن» tab. When nothing is
 * pending the whole section stays hidden.
 */
class MigrationInspector
{
    /** @return string[] migration file names (without .php) that are pending */
    public static function pendingNames(): array
    {
        $files = self::migrationFiles();

        $ran = [];

        if (SchemaReady::check('migrations')) {
            try {
                $ran = DB::table('migrations')->pluck('migration')->all();
            } catch (\Throwable) {
                $ran = [];
            }
        }

        return array_values(array_diff($files, $ran));
    }

    public static function hasPending(): bool
    {
        return self::pendingNames() !== [];
    }

    /**
     * Detailed pending report:
     * [ ['name' => '2026_..._create_x', 'tables' => [['name', [columns]]], 'alters' => [['table', [columns]]]] ]
     *
     * @return array<int, array{name: string, tables: array, alters: array}>
     */
    public static function pendingReport(): array
    {
        $out = [];

        foreach (self::pendingNames() as $name) {
            [$tables, $alters] = self::inspectFile($name);
            $out[] = ['name' => $name, 'tables' => $tables, 'alters' => $alters];
        }

        return $out;
    }

    /**
     * Run `artisan migrate --force`, then apply the (idempotent) seeders
     * so brand-new migrations that ship with their own seeds get their
     * data immediately. Returns [ok, output].
     */
    public static function runMigrations(): array
    {
        try {
            @set_time_limit(300);

            \Artisan::call('migrate', ['--force' => true]);

            $output = trim(\Artisan::output());

            // Seeders (DatabaseSeeder is idempotent) — best effort: a seeder
            // failure must not hide the successful migrations.
            try {
                \Artisan::call('db:seed', ['--force' => true]);

                $seed = trim(\Artisan::output());

                if ($seed !== '') {
                    $output .= "\n— سیدرها —\n".$seed;
                }
            } catch (\Throwable $e) {
                $output .= "\n— سیدرها ناموفق: ".$e->getMessage();
            }

            return [true, $output];
        } catch (\Throwable $e) {
            return [false, $e->getMessage()];
        }
    }

    /* ----------------------------- internals ----------------------------- */

    /** @return string[] sorted migration file names (without .php) */
    protected static function migrationFiles(): array
    {
        $dir = database_path('migrations');

        if (! is_dir($dir)) {
            return [];
        }

        $files = [];

        foreach (scandir($dir) ?: [] as $f) {
            if (str_ends_with($f, '.php') && ! str_starts_with($f, '.')) {
                $files[] = substr($f, 0, -4);
            }
        }

        sort($files);

        return $files;
    }

    /**
     * Static-parse one migration file for Schema::create / Schema::table
     * calls and the $table->type('column') definitions inside them.
     *
     * @return array{0: array<int, array{string, list<string>}>, 1: array<int, array{string, list<string>}>}
     */
    protected static function inspectFile(string $name): array
    {
        $path = database_path('migrations/'.$name.'.php');

        $code = @file_get_contents($path);

        if ($code === false) {
            return [[], []];
        }

        $tables = [];
        $alters = [];

        // Split the source into Schema::create(...) / Schema::table(...) blocks.
        if (! preg_match_all('/Schema::(create|table)\s*\(\s*[\'"]([a-zA-Z0-9_]+)[\'"]\s*,\s*function\s*\(([^)]*)\)\s*\{/', $code, $m, PREG_OFFSET_CAPTURE)) {
            return [[], []];
        }

        $len = strlen($code);

        foreach ($m[2] as $i => $cap) {
            $kind  = $m[1][$i][0];           // create | table
            $table = $cap[0];

            // crude block end: the next "});" after the match start
            $start = $cap[1];
            $end   = strpos($code, '});', $start);
            $end   = $end === false ? $len : $end + 3;

            $body = substr($code, $start, $end - $start);

            $columns = self::parseColumns($body);

            if ($kind === 'create') {
                $tables[] = [$table, $columns];
            } else {
                $alters[] = [$table, $columns];
            }
        }

        return [$tables, $alters];
    }

    /** @return list<string> column names mentioned in the closure body */
    protected static function parseColumns(string $body): array
    {
        $columns = [];

        // $table->string('name', ...), ->foreignId('x'), ->decimal('y', ...)
        if (preg_match_all('/->(string|char|text|mediumText|longText|binary|integer|tinyInteger|smallInteger|mediumInteger|bigInteger|unsignedInteger|unsignedBigInteger|unsignedTinyInteger|unsignedSmallInteger|unsignedMediumInteger|float|double|decimal|unsignedDecimal|boolean|enum|set|date|dateTime|dateTimeTz|time|timeTz|timestamp|timestampTz|year|json|jsonb|uuid|foreignId|foreignUuid|macAddress|ipAddress|tinyText|smallIncrements|increments)\s*\(\s*[\'"]([a-zA-Z0-9_]+)[\'"]/', $body, $m)) {
            foreach ($m[2] as $col) {
                $columns[] = $col;
            }
        }

        // shorthand helpers
        if (preg_match('/->id\s*\(/', $body)) {
            array_unshift($columns, 'id');
        }
        if (preg_match('/->timestamps\s*\(\s*\)/', $body)) {
            array_push($columns, 'created_at', 'updated_at');
        }
        if (preg_match('/->softDeletes\s*\(\s*\)/', $body)) {
            array_push($columns, 'deleted_at');
        }
        if (preg_match('/->rememberToken\s*\(\s*\)/', $body)) {
            array_push($columns, 'remember_token');
        }

        return array_values(array_unique($columns));
    }
}
