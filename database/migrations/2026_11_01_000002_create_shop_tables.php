<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Phase 3 — seller catalog data layer:
     * shops (one per seller), shop_categories (seller-defined),
     * products, product_images (gallery per product).
     */
    public function up(): void
    {
        Schema::dropIfExists('product_images');
        Schema::dropIfExists('products');
        Schema::dropIfExists('shop_categories');
        Schema::dropIfExists('shops');

        Schema::create('shops', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('name', 120);
            $table->string('slug', 150)->nullable()->unique(); // generated on create, used by public URLs later
            $table->string('instagram_username', 80)->nullable();
            $table->text('about')->nullable();
            $table->string('logo_path')->nullable();
            $table->string('banner_path')->nullable();
            $table->string('phone', 15)->nullable();
            $table->string('address', 300)->nullable();
            $table->string('status', 20)->default('active'); // active|paused (seller switch; public visibility also requires seller approval)
            $table->timestamps();
        });

        Schema::create('shop_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained()->cascadeOnDelete();
            $table->string('name', 80);
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();

            $table->index(['shop_id', 'sort']);
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained()->cascadeOnDelete();
            $table->foreignId('shop_category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->unsignedBigInteger('price')->default(0);            // Toman, no decimals
            $table->unsignedBigInteger('compare_at_price')->nullable(); // pre-discount price
            $table->unsignedInteger('stock')->default(0);
            $table->string('status', 20)->default('draft');             // draft|published
            $table->unsignedBigInteger('views')->default(0);
            $table->timestamps();

            $table->index(['shop_id', 'status']);
            $table->index(['shop_id', 'shop_category_id']);
        });

        Schema::create('product_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('path'); // public disk path (storage/app/public/...)
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();

            $table->index(['product_id', 'sort']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_images');
        Schema::dropIfExists('products');
        Schema::dropIfExists('shop_categories');
        Schema::dropIfExists('shops');
    }
};
