<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Story analytics for EVERYONE — not just logged-in customers.
 *
 * Until now story_views rows were only written for logged-in customers
 * («seen» marks), so the admin-dashboard daily-views chart stayed near
 * zero on real shops where most viewers are guests. Guests now get a
 * row too, keyed by an anonymous visitor token; customer_id becomes
 * nullable for that. The old (story_id, customer_id) unique index keeps
 * working (multiple NULLs are allowed in unique indexes on both MySQL
 * and SQLite) and a matching (story_id, visitor_token) index keeps
 * firstOrCreate idempotent for guest views.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('story_views', function (Blueprint $table) {
            $table->foreignId('customer_id')->nullable()->change();
            $table->string('visitor_token', 128)->nullable()->after('customer_id')->index();
            $table->unique(['story_id', 'visitor_token']);
        });
    }

    public function down(): void
    {
        Schema::table('story_views', function (Blueprint $table) {
            $table->dropUnique(['story_id', 'visitor_token']);
            $table->dropIndex(['visitor_token']);
            $table->dropColumn('visitor_token');
            $table->foreignId('customer_id')->nullable(false)->change();
        });
    }
};
