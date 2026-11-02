<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * فیلترینگ — shop ATTRIBUTES (ویژگی‌ها: رنگ، سایز، گارانتی و…) join the
     * storefront filters alongside spec titles (مشخصات محصولات).
     *
     * shop_filter_items gains:
     *   kind              → 'spec' (title inside product specifications JSON)
     *                       | 'attribute' (a ShopAttribute of the shop)
     *   shop_attribute_id → the attribute row when kind = 'attribute'
     * The old unique(shop_filter_id, spec_name) is dropped: an attribute
     * and a spec title may share the same label inside one filter, and the
     * picker already de-duplicates per kind in PHP.
     */
    public function up(): void
    {
        Schema::table('shop_filter_items', function (Blueprint $table) {
            $table->string('kind', 16)->default('spec')->after('spec_name');
            $table->unsignedBigInteger('shop_attribute_id')->nullable()->after('kind');

            $table->index(['shop_attribute_id']);
        });

        // Safe drop — the index name is the Laravel default on the 2026_11_15 table.
        Schema::table('shop_filter_items', function (Blueprint $table) {
            if (Schema::hasColumn('shop_filter_items', 'spec_name')) {
                $table->dropUnique(['shop_filter_id', 'spec_name']);
            }
        });
    }

    public function down(): void
    {
        Schema::table('shop_filter_items', function (Blueprint $table) {
            $table->unique(['shop_filter_id', 'spec_name']);
        });

        Schema::table('shop_filter_items', function (Blueprint $table) {
            $table->dropIndex(['shop_attribute_id']);
            $table->dropColumn(['kind', 'shop_attribute_id']);
        });
    }
};
