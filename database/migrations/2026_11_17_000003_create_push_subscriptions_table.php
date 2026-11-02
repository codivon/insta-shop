<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Web-Push subscriptions (devices) for admin push notifications — the
 * endpoint + keys returned by the browser's pushManager.subscribe() call.
 * Lets the admin receive notifications even when the panel tab/browser
 * is closed.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('push_subscriptions')) {
            return;
        }

        Schema::create('push_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->string('endpoint_hash', 64)->unique();  // sha1 of endpoint (unique index on a stable length)
            $table->text('endpoint');                        // full push service endpoint URL
            $table->text('p256dh');                          // user public key
            $table->text('auth');                            // auth secret
            $table->string('user_agent', 500)->nullable();
            $table->string('device_label', 120)->default('');
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('push_subscriptions');
    }
};
