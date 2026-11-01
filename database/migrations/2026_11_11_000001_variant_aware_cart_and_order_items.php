<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Variant-aware cart & orders:
     *  - cart_items.variant_id  (0 = product without a chosen variant) and the
     *    line identity becomes (cart, product, variant) so one product can sit
     *    in the cart once per variant.
     *  - order_items.variant_id + variant_label snapshot, so paid orders keep
     *    the exact picked combination («رنگ: مشکی / سایز: L») and its price.
     */
    public function up(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            $table->unsignedBigInteger('variant_id')->default(0)->after('product_id');

            // One line per (cart, product, variant) — 0 doubles as "no variant".
            $table->dropUnique('cart_items_cart_id_product_id_unique');
            $table->unique(['cart_id', 'product_id', 'variant_id']);
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->unsignedBigInteger('variant_id')->default(0)->after('product_id');
            $table->string('variant_label')->nullable()->after('product_name');
        });
    }

    public function down(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropUnique('cart_items_cart_id_product_id_variant_id_unique');
            $table->dropColumn('variant_id');
            $table->unique(['cart_id', 'product_id']);
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn(['variant_id', 'variant_label']);
        });
    }
};
