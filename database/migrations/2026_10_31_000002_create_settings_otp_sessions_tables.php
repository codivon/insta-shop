<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['customer_sessions', 'seller_sessions', 'admin_sessions'] as $tbl) {
            Schema::dropIfExists($tbl);
        }
        Schema::dropIfExists('otp_codes');
        Schema::dropIfExists('settings');

        // ── DB-driven system settings (key/value per group) ──
        //
        // Tables are created WITHOUT inline indexes; every secondary index is
        // then added through self::safeIndex() as its own guarded step. On one
        // shared MySQL host the ALTER immediately following the CREATE of this
        // very table died with 42S02 «table settings doesn't exist» and broke
        // the web installer mid-way (not reproducible on sqlite/plain MySQL;
        // hosting-side DDL quirk — the same host already needed the 86afa3a
        // index-length fix). Indexes here are integrity niceties, not
        // functional requirements: Setting::set()/setMany() upsert by
        // (group, key), so a skipped index only costs DB-level protection.
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('group', 50);                 // general|theme|sms|payment|...
            $table->string('key', 100);
            $table->longText('value')->nullable();       // scalar or JSON
            $table->string('type', 20)->default('text'); // text|boolean|number|json
            $table->timestamps();
        });

        self::safeIndex('settings', 'settings_group_key_unique',
            fn (Blueprint $t) => $t->unique(['group', 'key'], 'settings_group_key_unique'));

        // ── OTP codes (seller/customer phone login + verification) ──
        Schema::create('otp_codes', function (Blueprint $table) {
            $table->id();
            $table->string('phone', 15);
            $table->string('code', 6);
            $table->string('purpose', 30)->default('login');  // login|register|verify
            $table->string('panel', 20)->default('customer'); // seller|customer
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->unsignedTinyInteger('max_attempts')->default(5);
            $table->timestamp('expires_at');
            $table->timestamp('consumed_at')->nullable();
            $table->timestamps();
        });

        self::safeIndex('otp_codes', 'otp_codes_phone_index',
            fn (Blueprint $t) => $t->index('phone'));

        // ── Isolated session tables per panel ──
        foreach (['admin_sessions', 'seller_sessions', 'customer_sessions'] as $tbl) {
            Schema::create($tbl, function (Blueprint $table) {
                $table->string('id')->primary();
                $table->foreignId('user_id')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->longText('payload');
                $table->integer('last_activity');
            });

            self::safeIndex($tbl, $tbl.'_user_id_index',
                fn (Blueprint $t) => $t->index('user_id'));
            self::safeIndex($tbl, $tbl.'_last_activity_index',
                fn (Blueprint $t) => $t->index('last_activity'));
        }
    }

    public function down(): void
    {
        foreach (['customer_sessions', 'seller_sessions', 'admin_sessions', 'otp_codes', 'settings'] as $tbl) {
            Schema::dropIfExists($tbl);
        }
    }

    /**
     * Add a secondary index without ever failing the migration: skip when it
     * already exists (installer retry over a partial attempt) and log+continue
     * when the host rejects the DDL (see the block comment in up()).
     */
    protected static function safeIndex(string $table, string $name, Closure $add): void
    {
        try {
            $existing = collect(Schema::getIndexes($table))->pluck('name');

            if ($existing->contains($name)) {
                return; // already there (e.g. this up() ran on a healthier host before)
            }

            Schema::table($table, $add);
        } catch (Throwable $e) {
            report($e);
            logger()->warning("InstaShop install: index «{$name}» on «{$table}» skipped — {$e->getMessage()}");
        }
    }
};
