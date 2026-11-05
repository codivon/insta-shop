<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Stories — optional circular ICON (thumbnail) per story.
 *
 * The seller's PUBLIC storefront shows every active story as its own
 * story ring («۵ استوری = انگار ۵ اکانت استوری گذاشته» — client), and
 * each ring needs a face: for image stories the media itself works,
 * but VIDEO stories have no poster — the seller picks this icon in the
 * add-story modal (fallbacks: media → shop logo).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stories', function (Blueprint $table): void {
            $table->string('icon_path')->nullable()->after('media_path');
        });
    }

    public function down(): void
    {
        Schema::table('stories', function (Blueprint $table): void {
            $table->dropColumn('icon_path');
        });
    }
};
