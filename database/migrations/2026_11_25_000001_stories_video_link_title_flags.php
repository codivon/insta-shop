<?php

use App\Support\Database\Concerns\DefensiveMigration;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Stories round-2 refinements (v0.17.1):
 *
 *  - link_title         → the external link gets a TITLE shown on the
 *                         story's link button («مشاهدهٔ لینک» stays the
 *                         fallback when empty).
 *  - video_url          → VIDEO stories come from a LINK (direct mp4/webm/
 *                         ogg URL) — never uploaded; `type` already exists
 *                         and now becomes real (image | video).
 *  - likes_enabled      → per-story toggle (default ON) — seller/admin can
 *                         turn the like button OFF for a story.
 *  - comments_enabled   → same for the comment composer (default ON).
 *  - media_path nullable→ video stories have no uploaded file (image
 *                         stories keep the uploaded media).
 */
return new class extends Migration
{
    use DefensiveMigration;

    public function up(): void
    {
        self::columnIfMissing('stories', 'link_title',
            fn (Blueprint $table) => $table->string('link_title', 150)->nullable()->after('link_url'));
        self::columnIfMissing('stories', 'video_url',
            fn (Blueprint $table) => $table->string('video_url', 500)->nullable()->after('media_path'));
        self::columnIfMissing('stories', 'likes_enabled',
            fn (Blueprint $table) => $table->boolean('likes_enabled')->default(true)->after('caption'));
        self::columnIfMissing('stories', 'comments_enabled',
            fn (Blueprint $table) => $table->boolean('comments_enabled')->default(true)->after('likes_enabled'));

        if (! self::columnIsNullable('stories', 'media_path')) {
            Schema::table('stories', function (Blueprint $table) {
                $table->string('media_path', 400)->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        Schema::table('stories', function (Blueprint $table) {
            $table->dropColumn(['link_title', 'video_url', 'likes_enabled', 'comments_enabled']);
        });
    }
};
