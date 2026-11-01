<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * فروش ویژه (special sale):
 * A product can be flagged as part of the shop's «special sale» block with
 * an optional deadline. The storefront shows a dedicated, beautifully styled
 * section (with live countdown) for products whose window is still open, and
 * the single product page shows a modern countdown to the deadline.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('is_special')->default(false)->after('status');
            $table->dateTime('special_ends_at')->nullable()->after('is_special');

            // storefront hot path: this shop's active special products
            $table->index(['shop_id', 'is_special']);
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['shop_id', 'is_special']);
            $table->dropColumn(['is_special', 'special_ends_at']);
        });
    }
};
