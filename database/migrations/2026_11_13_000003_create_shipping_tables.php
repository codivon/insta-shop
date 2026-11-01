<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Seller shipping methods (روش‌های ارسال) — one shop owns many carriers.
     *
     * Tabs of the create/edit modal:
     *   اطلاعات کلی        → title/image/status/waiting-time/weights/description
     *   مناطق و هزینه‌ها   → origin province/city, carriage-forward, free-shipping
     *                        thresholds, heavy-package extra cost, covered cities
     *   بازه زمانی ارسال   → fixed text vs user-select range + start day
     */
    public function up(): void
    {
        Schema::dropIfExists('carrier_city');
        Schema::dropIfExists('carriers');

        Schema::create('carriers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained()->cascadeOnDelete();

            /* ── اطلاعات کلی ── */
            $table->string('title', 120);
            $table->string('image_path')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('waiting_time', 120)->nullable();     // زمان انتظار
            $table->unsignedInteger('max_package_weight')->nullable(); // گرم
            $table->unsignedInteger('min_package_weight')->nullable(); // گرم
            $table->text('description')->nullable();

            /* ── مناطق و هزینه‌ها ── */
            $table->foreignId('origin_province_id')->nullable()->constrained('provinces')->nullOnDelete();
            $table->foreignId('origin_city_id')->nullable()->constrained('cities')->nullOnDelete();
            $table->boolean('carriage_forward')->default(false); // پس کرایه
            $table->unsignedBigInteger('free_shipping_weight')->nullable(); // ارسال رایگان بالاتر از این وزن (گرم)
            $table->unsignedBigInteger('free_shipping_price')->nullable();  // ارسال رایگان بالاتر از این مبلغ
            $table->unsignedBigInteger('extra_cost')->default(0);           // هزینه اضافی هر کیلوگرم سنگین‌تر
            $table->string('covered_cities', 20)->default('all');           // all | select_city

            /* ── بازه زمانی ارسال ── */
            $table->string('delivery_time_type', 20)->default('default');   // default | user_select
            $table->unsignedTinyInteger('start_days_after_order')->default(1);
            $table->string('default_delivery_range', 150)->nullable();      // مثال: ارسال در ۳ الی ۶ روز کاری

            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();

            $table->index(['shop_id', 'is_active']);
        });

        // Cities chosen when covered_cities = select_city
        Schema::create('carrier_city', function (Blueprint $table) {
            $table->id();
            $table->foreignId('carrier_id')->constrained()->cascadeOnDelete();
            $table->foreignId('city_id')->constrained()->cascadeOnDelete();

            $table->unique(['carrier_id', 'city_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('carrier_city');
        Schema::dropIfExists('carriers');
    }
};
