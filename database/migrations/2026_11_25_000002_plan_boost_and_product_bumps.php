<?php

use App\Support\Database\Concerns\DefensiveMigration;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    use DefensiveMigration;

    /**
     * v0.23.0 — «نردبان و درخشش»:
     *
     *   plans.boost_level   → سطح اولویتِ نمایش در صفحهٔ اول (۰=بدون،
     *                         ۱=عادی، ۲=ویژه). فروشگاه‌ها و محصولاتِ
     *                         فروشندگانِ دارای پلنِ اولویت‌دار در صفحهٔ
     *                         اول جلوتر می‌افتند؛ میان خریدارانِ زیادِ
     *                         یک سطح، چرخشِ روزانه (فیرشیر) برقرار است.
     *   product_bumps       → «نردبان» به سبک دیوار: فروشنده برای یک
     *                         محصول به مدت مشخص (روز) نردبان می‌خرد و
     *                         محصولش در نوار «نردبان‌شده‌ها» صدر صفحهٔ
     *                         اول نمایش داده می‌شود. تمدید = انباشت از
     *                         انتهای نردبان فعلی (مثل اشتراک).
     *
     * FOREIGN KEYS via the defensive helper (v0.21.2 lesson) — never inline.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('plans', 'boost_level')) {
            Schema::table('plans', function (Blueprint $table): void {
                $table->unsignedTinyInteger('boost_level')->default(0)->after('commission_value');
            });
        }

        if (! Schema::hasTable('product_bumps')) {
            Schema::create('product_bumps', function (Blueprint $table) {
                $table->id();
                $table->foreignId('seller_id');                 // FK defensively below
                $table->foreignId('product_id');                // FK defensively below
                $table->timestamp('starts_at');
                $table->timestamp('ends_at');                   // «فعال» یعنی ends_at > now
                $table->unsignedBigInteger('price_paid')->default(0);
                $table->string('source', 16)->default('wallet'); // wallet | admin
                $table->string('note', 200)->nullable();
                $table->timestamps();

                $table->index(['product_id', 'ends_at']);
                $table->index('ends_at');
                $table->index('seller_id');
            });

            self::foreignKeyIfMissing('product_bumps', 'seller_id', 'sellers', 'id');
            self::foreignKeyIfMissing('product_bumps', 'product_id', 'products', 'id');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('product_bumps');

        if (Schema::hasColumn('plans', 'boost_level')) {
            Schema::table('plans', function (Blueprint $table): void {
                $table->dropColumn('boost_level');
            });
        }
    }
};
