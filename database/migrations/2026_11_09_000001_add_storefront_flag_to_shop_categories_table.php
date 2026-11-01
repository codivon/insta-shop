<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Seller storefront (public shop page):
 * each shop category gets a «نمایش در صفحه فروشگاه» flag. Only
 * categories with the flag show up as filter chips / sections on the
 * seller's public storefront (دامنه/{آیدی اینستا} یا دامنه/shop/{آیدی اینستا}).
 * Default TRUE so existing categories stay visible on the new page.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shop_categories', function (Blueprint $table) {
            $table->boolean('show_on_storefront')->default(true)->after('sort');
        });
    }

    public function down(): void
    {
        Schema::table('shop_categories', function (Blueprint $table) {
            $table->dropColumn('show_on_storefront');
        });
    }
};
