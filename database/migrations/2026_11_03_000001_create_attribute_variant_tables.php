<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Phase 6 — dynamic product variations:
     *  - shop_attributes / shop_attribute_values : per-shop attribute fields
     *    (رنگ، گارانتی، سایز، ...) with typed values (color / select / text).
     *    Three defaults are seeded per shop and may be deleted by the seller.
     *  - product_variants / product_variant_values : sellable combinations
     *    (price, stock, discount, min/max purchase, visibility + attribute picks).
     *  - product_files : downloadable product files (title, price, discount, status).
     *  - products : type (physical|downloadable), weight_grams, unit columns.
     *  - shops.instagram_username becomes unique (mirrors sellers handle).
     */
    public function up(): void
    {
        /* ── products: physical/downloadable + weight + unit ── */
        Schema::table('products', function (Blueprint $table) {
            $table->string('type', 20)->default('physical')->after('shop_category_id');   // physical|downloadable
            $table->unsignedInteger('weight_grams')->nullable()->after('stock');          // physical only
            $table->string('unit', 20)->nullable()->default('تعداد')->after('weight_grams'); // تعداد/کیلوگرم/... (null for downloadable)
        });

        /* ── shops.instagram_username: dedupe then unique (safety net) ── */
        ShopInstagramUniqueBackfill::run();

        Schema::table('shops', function (Blueprint $table) {
            $table->unique('instagram_username', 'shops_instagram_username_unique');
        });

        /* ── attribute fields defined per shop ── */
        Schema::create('shop_attributes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained()->cascadeOnDelete();
            $table->string('name', 80);                              // رنگ، گارانتی، سایز، ...
            $table->string('type', 20)->default('select');           // color|select|text
            $table->boolean('is_default')->default(false);           // seeded default (deletable)
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();

            $table->index(['shop_id', 'sort']);
        });

        Schema::create('shop_attribute_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_attribute_id')->constrained()->cascadeOnDelete();
            $table->string('value', 80);
            $table->string('color_hex', 9)->nullable();              // #RRGGBB for color type
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();

            $table->index(['shop_attribute_id', 'sort']);
        });

        /* ── sellable variant rows per product ── */
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('price')->default(0);                          // Toman (required)
            $table->unsignedInteger('stock')->default(0);                             // warehouse (required)
            $table->unsignedTinyInteger('discount_percent')->default(0);              // 0-100
            $table->dateTime('discount_expires_at')->nullable();
            $table->unsignedInteger('max_purchase')->nullable();                      // per order
            $table->unsignedInteger('min_purchase')->nullable();                      // per order
            $table->string('status', 20)->default('active');                          // active|hidden
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();

            $table->index(['product_id', 'sort']);
        });

        /* ── attribute picks per variant row ── */
        Schema::create('product_variant_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_variant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('shop_attribute_id')->constrained()->cascadeOnDelete();
            $table->foreignId('shop_attribute_value_id')->nullable()->constrained()->nullOnDelete();
            $table->string('value_text', 120)->nullable();           // for text-type attributes
            $table->timestamps();

            $table->unique(['product_variant_id', 'shop_attribute_id'], 'pvv_variant_attribute_unique');
        });

        /* ── downloadable product files ── */
        Schema::create('product_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('title', 150);
            $table->unsignedBigInteger('price')->default(0);
            $table->unsignedTinyInteger('discount_percent')->default(0);
            $table->string('status', 20)->default('active');         // active|hidden
            $table->string('file_path');                             // local disk (private)
            $table->unsignedBigInteger('file_size')->nullable();     // bytes
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();

            $table->index(['product_id', 'sort']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_files');
        Schema::dropIfExists('product_variant_values');
        Schema::dropIfExists('product_variants');
        Schema::dropIfExists('shop_attribute_values');
        Schema::dropIfExists('shop_attributes');

        Schema::table('shops', function (Blueprint $table) {
            $table->dropUnique('shops_instagram_username_unique');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['type', 'weight_grams', 'unit']);
        });
    }
};

/**
 * Keeps NULL instagram handles untouched and resolves duplicate handles
 * (all but the first occurrence become NULL) so the unique index can be
 * created safely on any existing dataset.
 */
class ShopInstagramUniqueBackfill
{
    public static function run(): void
    {
        $rows = \Illuminate\Support\Facades\DB::table('shops')
            ->whereNotNull('instagram_username')
            ->orderBy('id')
            ->get(['id', 'instagram_username']);

        $seen = [];

        foreach ($rows as $row) {
            $handle = mb_strtolower(trim((string) $row->instagram_username));

            if ($handle === '' || isset($seen[$handle])) {
                \Illuminate\Support\Facades\DB::table('shops')
                    ->where('id', $row->id)
                    ->update(['instagram_username' => null]);

                continue;
            }

            $seen[$handle] = true;
        }
    }
}
