<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Activity log («فعالیت‌های اخیر») — every meaningful event on the site is
 * recorded here with WHO did it, WHAT happened and (for edits) the
 * before/after values, so the admin panel can show a full audit trail.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('activity_logs')) {
            return;
        }

        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->string('actor_type', 20)->default('system')->index(); // admin|seller|customer|system
            $table->unsignedBigInteger('actor_id')->nullable();
            $table->string('actor_name', 120)->default('');
            $table->string('action', 80)->index();          // e.g. settings.updated, order.created
            $table->string('title', 500);                    // human-readable (fa)
            $table->nullableTimestamps();
            $table->string('subject_type', 80)->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->json('before')->nullable();              // values BEFORE the change
            $table->json('after')->nullable();               // values AFTER the change
            $table->string('ip', 45)->nullable();
            $table->string('user_agent', 500)->nullable();

            $table->index(['created_at']);
            $table->index(['actor_type', 'actor_id']);
            $table->index(['subject_type', 'subject_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
