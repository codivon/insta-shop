<?php

use App\Support\Database\Concerns\DefensiveMigration;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    use DefensiveMigration;

    /**
     * Product slug for SEO-friendly public URLs:
     *   دامنه/shop/{آیدی-اینستا}/{اسلاگ-محصول}
     * Unique per shop (the storefront handle scopes the URL), nullable so
     * legacy rows keep working — the public resolver falls back to «p{id}».
     */
    public function up(): void
    {
        self::columnIfMissing('products', 'slug',
            fn (Blueprint $table) => $table->string('slug', 190)->nullable()->after('name'));

        self::indexIfMissing('products', 'products_shop_id_slug_unique',
            fn (Blueprint $table) => $table->unique(['shop_id', 'slug']), 'unique');
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique(['shop_id', 'slug']);
            $table->dropColumn('slug');
        });
    }
};
