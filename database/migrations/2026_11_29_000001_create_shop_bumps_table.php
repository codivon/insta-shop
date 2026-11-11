<?php

use App\Support\Database\Concerns\DefensiveMigration;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    use DefensiveMigration;

    /**
     * v0.25.0 — «نردبانِ خودِ فروشگاه» (درخواست کارفرما: «در پنل فروشنده،
     * نردبان خود فروشگاه هم به صفحه نردبان اضافه کن، که فروشنده بتونه خود
     * فروشگاهش را هم نردبان کنه»):
     *
     *   shop_bumps → عیناً همان قاعدهٔ product_bumps اما برای فروشگاه:
     *                فروشنده یک «طرح نردبان» را از کیف پول می‌خرد و
     *                فروشگاهش به‌مدت همان پنجره در صدر لیست فروشگاه‌های
     *                صفحهٔ اول (و اسپات‌لایت) با نشانِ نردبان دیده می‌شود.
     *                تمدید = انباشت از انتهای نردبان فعلی (مثل محصول و
     *                اشتراک)؛ «فعال» یعنی ends_at > now — بدون کرون.
     *
     * FOREIGN KEYS via the defensive helper (v0.21.2 lesson) — never inline.
     */
    public function up(): void
    {
        if (! Schema::hasTable('shop_bumps')) {
            Schema::create('shop_bumps', function (Blueprint $table) {
                $table->id();
                $table->foreignId('seller_id');                 // FK defensively below
                $table->foreignId('shop_id');                   // FK defensively below
                $table->timestamp('starts_at');
                $table->timestamp('ends_at');                   // «فعال» یعنی ends_at > now
                $table->unsignedBigInteger('price_paid')->default(0);
                $table->foreignId('bump_plan_id')->nullable();  // طرحِ خریداری‌شده
                $table->string('source', 16)->default('wallet'); // wallet | admin
                $table->string('note', 200)->nullable();
                $table->timestamps();

                $table->index(['shop_id', 'ends_at']);
                $table->index('ends_at');
                $table->index('seller_id');
                $table->index('bump_plan_id');
            });

            self::foreignKeyIfMissing('shop_bumps', 'seller_id', 'sellers', 'id');
            self::foreignKeyIfMissing('shop_bumps', 'shop_id', 'shops', 'id');
            self::foreignKeyIfMissing('shop_bumps', 'bump_plan_id', 'bump_plans', 'id', onDelete: 'null');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('shop_bumps');
    }
};
