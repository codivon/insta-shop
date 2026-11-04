<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Global (platform-wide) category tree, managed only by the admin:
 *
 *  - `categories`  → hierarchical tree (parent_id), seeded from the
 *                    Basalam navigation tree (14 roots / 119 subs / 983
 *                    sub-subs), each with image + slug + basalam id.
 *  - products      → now belong to a global category (`category_id`).
 *                    The per-shop `shop_categories` / `shop_category_id`
 *                    pair is retired: sellers can no longer create,
 *                    rename or delete categories.
 *  - shops         → new `show_stock` preference: when the seller turns
 *                    it on, the storefront shop page and the product page
 *                    display the real remaining stock.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('categories');

        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('categories')->cascadeOnDelete();
            $table->string('name', 120);
            $table->string('slug', 160)->nullable()->unique();
            $table->string('image', 400)->nullable();          // remote URL (Basalam statics) or local path
            $table->unsignedBigInteger('basalam_id')->nullable()->unique();
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();

            $table->index(['parent_id', 'sort']);
        });

        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                $table->foreignId('category_id')->nullable()->after('shop_id')->constrained('categories')->nullOnDelete();
                $table->index('category_id');
            });

            if (Schema::hasColumn('products', 'shop_category_id')) {
                // SQLite refuses to drop a column that participates in an
                // index or a foreign key — clear both before the drop.
                Schema::table('products', function (Blueprint $table) {
                    $table->dropIndex(['shop_id', 'shop_category_id']);
                    $table->dropForeign(['shop_category_id']);
                });

                Schema::table('products', function (Blueprint $table) {
                    $table->dropColumn('shop_category_id');
                });
            }
        }

        if (Schema::hasTable('shops')) {
            Schema::table('shops', function (Blueprint $table) {
                $table->boolean('show_stock')->default(false)->after('status');
            });
        }

        Schema::dropIfExists('shop_categories');
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');

        if (Schema::hasTable('products')) {
            if (! Schema::hasColumn('products', 'shop_category_id')) {
                Schema::table('products', function (Blueprint $table) {
                    $table->foreignId('shop_category_id')->nullable()->after('shop_id');
                    $table->index('shop_category_id');
                });
            }

            if (Schema::hasColumn('products', 'category_id')) {
                Schema::table('products', function (Blueprint $table) {
                    $table->dropForeign(['category_id']);
                    $table->dropColumn('category_id');
                });
            }
        }

        if (Schema::hasTable('shops') && Schema::hasColumn('shops', 'show_stock')) {
            Schema::table('shops', function (Blueprint $table) {
                $table->dropColumn('show_stock');
            });
        }

        Schema::create('shop_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained()->cascadeOnDelete();
            $table->string('name', 80);
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();

            $table->index(['shop_id', 'sort']);
        });
    }
};
