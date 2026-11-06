<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * SECURITY: OTP codes are now stored ONLY as an HMAC-SHA256 digest (64 hex
 * chars) instead of the plaintext 6-digit code — a leaked database, backup
 * or log dump no longer contains any usable one-time password.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('otp_codes', function (Blueprint $table) {
            $table->string('code', 64)->change();
        });
    }

    public function down(): void
    {
        Schema::table('otp_codes', function (Blueprint $table) {
            $table->string('code', 6)->change();
        });
    }
};
