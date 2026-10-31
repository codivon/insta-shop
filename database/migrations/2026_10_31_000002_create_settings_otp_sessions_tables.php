<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── DB-driven system settings (key/value per group) ──
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('group', 50);                 // general|theme|sms|payment|...
            $table->string('key', 100);
            $table->longText('value')->nullable();       // scalar or JSON
            $table->string('type', 20)->default('text'); // text|boolean|number|json
            $table->timestamps();

            $table->unique(['group', 'key']);
        });

        // ── OTP codes (seller/customer phone login + verification) ──
        Schema::create('otp_codes', function (Blueprint $table) {
            $table->id();
            $table->string('phone', 15)->index();
            $table->string('code', 6);
            $table->string('purpose', 30)->default('login');  // login|register|verify
            $table->string('panel', 20)->default('customer'); // seller|customer
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->unsignedTinyInteger('max_attempts')->default(5);
            $table->timestamp('expires_at');
            $table->timestamp('consumed_at')->nullable();
            $table->timestamps();
        });

        // ── Isolated session tables per panel ──
        foreach (['admin_sessions', 'seller_sessions', 'customer_sessions'] as $tbl) {
            Schema::create($tbl, function (Blueprint $table) {
                $table->string('id')->primary();
                $table->foreignId('user_id')->nullable()->index();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->longText('payload');
                $table->integer('last_activity')->index();
            });
        }
    }

    public function down(): void
    {
        foreach (['customer_sessions', 'seller_sessions', 'admin_sessions', 'otp_codes', 'settings'] as $tbl) {
            Schema::dropIfExists($tbl);
        }
    }
};
