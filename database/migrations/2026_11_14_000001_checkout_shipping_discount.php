<?php

use App\Support\Database\Concerns\DefensiveMigration;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    use DefensiveMigration;

    /**
     * Checkout upgrade — per-shop carriers with a real price, seller
     * discount codes and the order snapshot that keeps them:
     *
     *   carriers   + base_price            → هزینهٔ ارسال پایهٔ هر روش (تومان)
     *   orders     + shipping_cost         → مجموع هزینهٔ حامل‌های انتخابی
     *              + discount_amount/code  → تخفیف کدخوردهٔ سفارش
     *              + shipments             → snapshot حامل هر فروشگاه
     *   discounts  (new)                   → کدهای تخفیف فروشنده‌ها
     */
    public function up(): void
    {
        self::columnIfMissing('carriers', 'base_price',
            fn (Blueprint $table) => $table->unsignedBigInteger('base_price')->default(0)->after('carriage_forward')
                ->comment('هزینهٔ ارسال پایه (تومان) — پس‌کرایه آن را نادیده می‌گیرد'));

        self::columnIfMissing('orders', 'shipping_cost',
            fn (Blueprint $table) => $table->unsignedBigInteger('shipping_cost')->default(0)->after('total_amount'));
        self::columnIfMissing('orders', 'discount_amount',
            fn (Blueprint $table) => $table->unsignedBigInteger('discount_amount')->default(0)->after('shipping_cost'));
        self::columnIfMissing('orders', 'discount_code',
            fn (Blueprint $table) => $table->string('discount_code', 60)->nullable()->after('discount_amount'));
        self::columnIfMissing('orders', 'shipments',
            fn (Blueprint $table) => $table->json('shipments')->nullable()->after('discount_code')
                ->comment('snapshot: [{shop_id, carrier_id, carrier_title, cost}]'));

        self::recreate('discounts');

        // NOTE (v0.21.2): the shop_id FK is added AFTER the create via
        // foreignKeyIfMissing() — Laravel's inline ->constrained() turns the
        // create into «CREATE TABLE …» + «ALTER TABLE … ADD CONSTRAINT» and
        // on the client's flaky MySQL the ALTER (second statement on a fresh
        // table) intermittently died with 1146 «table doesn't exist», and
        // every whole-file retry re-ran into the same window. See the helper.
        Schema::create('discounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shop_id');

            /* ── اطلاعات ── */
            $table->string('title', 120);
            $table->string('code', 60)->unique();
            $table->string('type', 12)->default('amount');          // amount | percent
            $table->unsignedBigInteger('price')->default(0);         // مقدار (تومان) برای type=amount
            $table->unsignedTinyInteger('percent')->default(0);      // برای type=percent
            $table->unsignedBigInteger('discount_ceiling')->nullable(); // سقف تخفیف درصدی
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();

            /* ── محدودیت‌ها ── */
            $table->unsignedBigInteger('least_price')->default(0);   // کمترین مبلغ سفارش
            $table->unsignedInteger('least_products_count')->default(0);
            $table->unsignedInteger('quantity')->default(0);          // 0 = بی‌نهایت
            $table->unsignedInteger('quantity_per_user')->default(0); // 0 = بی‌نهایت
            $table->unsignedInteger('used_count')->default(0);
            $table->json('customer_ids')->nullable();                 // مشتریان مجاز (خالی = همه)
            $table->string('include_type', 12)->default('all');       // all | category | product
            $table->json('include_ids')->nullable();
            $table->string('exclude_type', 12)->default('none');      // none | category | product
            $table->json('exclude_ids')->nullable();
            $table->boolean('only_first_purchase')->default(false);
            $table->boolean('not_discount_products')->default(false);
            $table->text('description')->nullable();
            $table->boolean('published')->default(true);

            $table->timestamps();

            $table->index(['shop_id', 'published']);
        });

        self::foreignKeyIfMissing('discounts', 'shop_id', 'shops', 'id');
    }

    public function down(): void
    {
        Schema::dropIfExists('discounts');

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['shipping_cost', 'discount_amount', 'discount_code', 'shipments']);
        });

        Schema::table('carriers', function (Blueprint $table) {
            $table->dropColumn('base_price');
        });
    }
};
