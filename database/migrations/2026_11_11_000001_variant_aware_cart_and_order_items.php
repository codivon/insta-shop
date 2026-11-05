<?php

use App\Support\Database\Concerns\DefensiveMigration;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    use DefensiveMigration;

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
        // The old (cart_id, product_id) identity must go — variant support
        // needs one line per (cart, product, VARIANT). Every step guarded:
        // a partial previous run may have applied any subset of them.
        if (Schema::hasIndex('cart_items', 'cart_items_cart_id_product_id_unique', 'unique')) {
            Schema::table('cart_items', function (Blueprint $table) {
                $table->dropUnique('cart_items_cart_id_product_id_unique');
            });
        }

        self::columnIfMissing('cart_items', 'variant_id',
            fn (Blueprint $table) => $table->unsignedBigInteger('variant_id')->default(0)->after('product_id'));

        // One line per (cart, product, variant) — 0 doubles as "no variant".
        self::indexIfMissing('cart_items', 'cart_items_cart_id_product_id_variant_id_unique',
            fn (Blueprint $table) => $table->unique(['cart_id', 'product_id', 'variant_id']), 'unique');

        self::columnIfMissing('order_items', 'variant_id',
            fn (Blueprint $table) => $table->unsignedBigInteger('variant_id')->default(0)->after('product_id'));
        self::columnIfMissing('order_items', 'variant_label',
            fn (Blueprint $table) => $table->string('variant_label')->nullable()->after('product_name'));
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
