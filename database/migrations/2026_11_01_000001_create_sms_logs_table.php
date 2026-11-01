<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('sms_logs');

        // ── Audit trail of every SMS the system sends ──
        Schema::create('sms_logs', function (Blueprint $table) {
            $table->id();
            $table->string('panel', 20)->default('landing');  // admin|seller|customer|landing
            $table->string('driver', 40);                     // log|kavenegar|ippanel|mellipayamak|restfulsms
            $table->string('phone', 15);                      // normalized 989XXXXXXXXX
            $table->string('template', 60)->nullable();       // otp|order|... (pattern name for verify providers)
            $table->text('message');
            $table->string('status', 12)->default('sent');    // sent|failed
            $table->text('error')->nullable();
            $table->string('provider_message_id', 120)->nullable();
            $table->json('meta')->nullable();                 // provider response dump
            $table->timestamps();

            $table->index(['phone', 'created_at']);
            $table->index(['driver', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sms_logs');
    }
};
