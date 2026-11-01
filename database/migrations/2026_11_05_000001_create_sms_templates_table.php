<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * SMS templates («قالب‌های پیامک»): every outgoing notification the system
 * can send, with its editable body ({var} placeholders), variables list,
 * sample data for live preview, on/off switch and — for pattern-based
 * providers (Kavenegar Verify/Lookup, Faraz IPPanel patterns) — the
 * pattern code registered in the SMS panel.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sms_templates', function (Blueprint $table) {
            $table->id();
            $table->string('key', 64)->unique();      // e.g. order.completed / otp_login
            $table->string('title', 120);             // Persian display title
            $table->string('group', 32)->index();     // orders | auth | wallet | notify | tickets | system
            $table->text('body');                     // message body with {placeholders}
            $table->json('variables')->nullable();    // [order_number, app_name, ...]
            $table->json('sample_data')->nullable();  // live-preview values
            $table->boolean('enabled')->default(true);
            $table->string('pattern_code')->nullable(); // template code in the SMS panel (pattern providers)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sms_templates');
    }
};
