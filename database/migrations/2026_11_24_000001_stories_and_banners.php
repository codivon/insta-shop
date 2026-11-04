<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Instagram-style shop stories + admin-managed home banners:
 *
 *  - stories          → image stories belonging to a SHOP (seller posts
 *                       from the seller panel, admin from the admin panel),
 *                       optional attached product + external link, caption,
 *                       display window (starts_at / ends_at — default 24h),
 *                       moderation status (pending / approved / rejected —
 *                       «auto» or «manual» per the admin settings) and a
 *                       view counter.
 *  - story_likes      → one like per customer per story (unique pair).
 *  - story_comments   → free-text comments of logged-in customers.
 *  - story_views      → per-customer "seen" marks driving the Instagram
 *                       ring colors (all stories of a shop seen → gray ring).
 *  - banners          → home hero slider slides managed by the admin
 *                       (image, link, sort, active toggle).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained('shops')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->string('type', 12)->default('image');      // image | video (video reserved)
            $table->string('media_path', 400);
            $table->text('caption')->nullable();
            $table->string('link_url', 500)->nullable();
            $table->dateTime('starts_at')->index();
            $table->dateTime('ends_at')->index();
            $table->string('status', 12)->default('pending');  // pending | approved | rejected
            $table->unsignedInteger('views')->default(0);
            $table->timestamps();

            $table->index(['status', 'starts_at', 'ends_at']);
        });

        Schema::create('story_likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('story_id')->constrained('stories')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['story_id', 'customer_id']);
        });

        Schema::create('story_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('story_id')->constrained('stories')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->text('body');
            $table->timestamps();

            $table->index(['story_id', 'created_at']);
        });

        Schema::create('story_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('story_id')->constrained('stories')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['story_id', 'customer_id']);
        });

        Schema::create('banners', function (Blueprint $table) {
            $table->id();
            $table->string('title', 150)->nullable();
            $table->string('image_path', 400);
            $table->string('link_url', 500)->nullable();
            $table->string('position', 24)->default('home_hero')->index();
            $table->unsignedInteger('sort')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('banners');
        Schema::dropIfExists('story_views');
        Schema::dropIfExists('story_comments');
        Schema::dropIfExists('story_likes');
        Schema::dropIfExists('stories');
    }
};
