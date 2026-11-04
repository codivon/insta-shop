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
     *
     * IMPORTANT — idempotent by design: on shared hosting a previous run may
     * have added the columns but crashed right after (DDL in MySQL commits
     * per statement, so the migration row is never written). Re-running it
     * must NOT crash with «SQLSTATE[42S21] Duplicate column name 'kind'» —
     * every step checks the current schema first.
     */
    public function up(): void
    {
        if (! Schema::hasTable('shop_filter_items')) {
            return;
        }

        $hasKind = Schema::hasColumn('shop_filter_items', 'kind');
        $hasAttrName = Schema::hasColumn('shop_filter_items', 'shop_attribute_id');

        if (! $hasKind || ! $hasAttrName) {
            Schema::table('shop_filter_items', function (Blueprint $table) use ($hasKind, $hasAttrName) {
                if (! $hasKind) {
                    $table->string('kind', 16)->default('spec')->after('spec_name');
                }

                if (! $hasAttrName) {
                    $table->unsignedBigInteger('shop_attribute_id')->nullable()->after('kind');
                }
            });
        }

        // Laravel default index name for $table->index(['shop_attribute_id']).
        // NOTE: no type argument — SQLite reports type null, MySQL 'index';
        // the name check alone is cross-driver safe.
        if (! Schema::hasIndex('shop_filter_items', 'shop_filter_items_shop_attribute_id_index')) {
            Schema::table('shop_filter_items', function (Blueprint $table) {
                $table->index(['shop_attribute_id']);
            });
        }

        // Safe drop — the index name is the Laravel default on the 2026_11_15
        // table, but a legacy/renamed index must never block the migration.
        try {
            if (Schema::hasIndex('shop_filter_items', 'shop_filter_items_shop_filter_id_spec_name_unique', 'unique')) {
                Schema::table('shop_filter_items', function (Blueprint $table) {
                    $table->dropUnique(['shop_filter_id', 'spec_name']);
                });
            }
        } catch (Throwable) {
            // Index already gone or named differently — nothing left to do.
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('shop_filter_items')) {
            return;
        }

        // Restore the unique only when both columns still exist.
        if (Schema::hasColumn('shop_filter_items', 'spec_name')
            && Schema::hasColumn('shop_filter_items', 'shop_filter_id')
            && ! Schema::hasIndex('shop_filter_items', 'shop_filter_items_shop_filter_id_spec_name_unique', 'unique')) {
            Schema::table('shop_filter_items', function (Blueprint $table) {
                $table->unique(['shop_filter_id', 'spec_name']);
            });
        }

        $hasKind = Schema::hasColumn('shop_filter_items', 'kind');
        $hasAttrName = Schema::hasColumn('shop_filter_items', 'shop_attribute_id');

        if ($hasAttrName && Schema::hasIndex('shop_filter_items', 'shop_filter_items_shop_attribute_id_index')) {
            Schema::table('shop_filter_items', function (Blueprint $table) {
                $table->dropIndex(['shop_attribute_id']);
            });
        }

        if ($hasKind || $hasAttrName) {
            Schema::table('shop_filter_items', function (Blueprint $table) use ($hasKind, $hasAttrName) {
                $columns = array_filter([
                    $hasKind ? 'kind' : null,
                    $hasAttrName ? 'shop_attribute_id' : null,
                ]);

                $table->dropColumn($columns);
            });
        }
    }
};
