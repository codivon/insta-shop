<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Specification templates (نوع مشخصات):
 * A seller saves a reusable set of spec groups under a title
 * (e.g. «گوشی موبایل») and re-applies it on new products —
 * only values are filled per product.
 *
 *  - specification_templates  → per-shop library: title + groups JSON
 *  - products.spec_type       → which template the product's specs came from
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('specification_templates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shop_id');
            $table->string('title', 120);                    // e.g. «گوشی موبایل»
            $table->json('groups');                          // [{name, items:[{title}]}]
            $table->timestamps();

            $table->unique(['shop_id', 'title']);
            $table->foreign('shop_id')->references('id')->on('shops')->cascadeOnDelete();
        });

        Schema::table('products', function (Blueprint $table) {
            $table->string('spec_type', 120)->nullable()->after('specifications');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('specification_templates');

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('spec_type');
        });
    }
};
