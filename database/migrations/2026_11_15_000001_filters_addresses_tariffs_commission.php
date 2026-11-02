<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Batch 72-74 + 79-80 data layer:
     *
     *   shop_filters / shop_filter_items  → فیلترینگ فروشگاه (72)
     *   customer_addresses                → دفترچه آدرس مشتری (73)
     *   carrier_tariffs                   → تعرفه وزنی درون/بیرون استانی (74)
     *   sellers.commission_*              → کمیسیون اختصاصی فروشنده (79)
     *   wallet_transactions.commission    → کمیسیون کسرشده هر واریز (80)
     *   orders.commissions                → snapshot تسویه سفارش (80)
     */
    public function up(): void
    {
        /* ── 72 — فیلترهای فروشگاه ── */
        Schema::create('shop_filters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained()->cascadeOnDelete();
            $table->string('title', 80);
            $table->boolean('published')->default(true);
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();

            $table->index(['shop_id', 'published']);
        });

        // Spec groups (from the seller's OWN products) attached to a filter.
        Schema::create('shop_filter_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_filter_id')->constrained('shop_filters')->cascadeOnDelete();
            $table->string('spec_name', 80);

            $table->unique(['shop_filter_id', 'spec_name']);
        });

        /* ── 73 — دفترچه آدرس مشتری ── */
        Schema::create('customer_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->string('title', 60)->nullable();          // خانه / محل کار …
            $table->foreignId('province_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('city_id')->nullable()->constrained()->nullOnDelete();
            $table->string('province_name', 60)->nullable();  // snapshot
            $table->string('city_name', 80)->nullable();      // snapshot
            $table->text('address');
            $table->string('postal_code', 10)->nullable();
            $table->string('plaque', 20)->nullable();          // پلاک
            $table->string('unit', 20)->nullable();            // واحد
            $table->string('recipient_name', 80)->nullable();
            $table->string('phone', 15)->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->index('customer_id');
        });

        /* ── 74 — تعرفه‌های وزنی هر روش ارسال ── */
        Schema::create('carrier_tariffs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('carrier_id')->constrained()->cascadeOnDelete();
            $table->string('zone_type', 16)->default('in_province');   // in_province | out_province
            $table->unsignedInteger('max_weight')->default(1000);      // گرم — سقف این پله
            $table->unsignedBigInteger('cost')->default(0);            // تومان
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();

            $table->index(['carrier_id', 'zone_type', 'max_weight']);
        });

        /* ── 79 — کمیسیون اختصاصی فروشنده (خالی = پیش‌فرض سراسری) ── */
        Schema::table('sellers', function (Blueprint $table) {
            $table->string('commission_type', 10)->nullable()->after('status'); // null | percent | fixed
            $table->unsignedBigInteger('commission_value')->nullable()->after('commission_type');
        });

        /* ── 80 — کمیسیون در دفتر واریز + snapshot تسویه سفارش ── */
        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->unsignedBigInteger('commission')->default(0)->after('amount')
                ->comment('کمیسیون کسرشده از این واریز (تومان)');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->json('commissions')->nullable()->after('shipments')
                ->comment('[{shop_id, shop_name, items_total, commission_type, commission_value, commission_amount, payout}]');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('commissions');
        });

        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->dropColumn('commission');
        });

        Schema::table('sellers', function (Blueprint $table) {
            $table->dropColumn(['commission_type', 'commission_value']);
        });

        Schema::dropIfExists('carrier_tariffs');
        Schema::dropIfExists('customer_addresses');
        Schema::dropIfExists('shop_filter_items');
        Schema::dropIfExists('shop_filters');
    }
};
