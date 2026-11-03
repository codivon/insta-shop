<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * «انتخاب توسط کاربر» delivery windows (بازه زمانی ارسال — انتخاب توسط کاربر).
 *
 * carriers:
 *   user_select_ranges → how many selectable days the customer sees
 *                        (۷ / ۱۰ / ۱۵ / ۲۰ روزه) when delivery_time_type
 *                        = user_select.
 *   disable_fridays    → جمعه‌ها از روزهای قابل انتخاب حذف می‌شوند.
 *   disable_holidays   → تعطیلات رسمی ثابت تقویم حذف می‌شوند.
 *
 * order_shipments:
 *   delivery_date      → the day the customer PICKED at checkout for this
 *                        shop's slice (Y-m-d) — shown to both the seller
 *                        and the customer afterwards.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('carriers', function (Blueprint $table): void {
            $table->unsignedTinyInteger('user_select_ranges')->nullable()->after('start_days_after_order');
            $table->boolean('disable_fridays')->default(false)->after('user_select_ranges');
            $table->boolean('disable_holidays')->default(false)->after('disable_fridays');
        });

        Schema::table('order_shipments', function (Blueprint $table): void {
            $table->date('delivery_date')->nullable()->after('tracking_code');
        });
    }

    public function down(): void
    {
        Schema::table('carriers', function (Blueprint $table): void {
            $table->dropColumn(['user_select_ranges', 'disable_fridays', 'disable_holidays']);
        });

        Schema::table('order_shipments', function (Blueprint $table): void {
            $table->dropColumn('delivery_date');
        });
    }
};
