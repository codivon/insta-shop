<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Admins (back-office users, email + password) ──
        Schema::create('admins', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->boolean('is_super')->default(false);
            $table->timestamp('last_login_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        // ── Sellers (phone + instagram username, OTP login) ──
        Schema::create('sellers', function (Blueprint $table) {
            $table->id();
            $table->string('phone', 15)->unique();          // E.164-ish: 989xxxxxxxxx
            $table->string('instagram_username', 80)->nullable()->unique();
            $table->string('full_name', 120)->nullable();
            $table->string('status', 20)->default('pending'); // pending|approved|suspended
            $table->timestamp('phone_verified_at')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        // ── Customers (phone only, OTP login) ──
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('phone', 15)->unique();
            $table->string('full_name', 120)->nullable();
            $table->string('status', 20)->default('active');  // active|blocked
            $table->timestamp('phone_verified_at')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
        Schema::dropIfExists('sellers');
        Schema::dropIfExists('admins');
    }
};
