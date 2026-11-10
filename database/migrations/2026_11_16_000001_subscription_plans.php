<?php

use App\Support\Database\Concerns\DefensiveMigration;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    use DefensiveMigration;

    /**
     * v0.22.0 — «اشتراک فروشندگان» (monetization beyond commission):
     *
     *   plans                → پلن‌های اشتراک قابل فروش (ماهانه/۶ماهه/سالانه …)
     *                          هر پلن می‌تواند درصد/مبلغ کمیسیون مخصوص خودش
     *                          را تعیین کند (null = نرخ سراسری).
     *   seller_subscriptions → اشتراک فعال/تاریخچهٔ هر فروشنده. «فعال»
     *                          یعنی ends_at > now — منقضی‌شدن با خودِ تاریخ
     *                          انجام می‌شود و نیازی به cron ندارد.
     *   seller_subscriptions.reminded_at → یادآوری اتمام اشتراک (۳ روز مانده)
     *                            فقط یک‌بار برای هر اشتراک ارسال شود.
     *
     * FOREIGN KEYS are added AFTER the tables exist via the defensive
     * helper (v0.21.2 lesson): inline ->constrained() compiles as
     * CREATE + a separate ALTER that repeatedly hit the shared host's
     * flake window on freshly created tables.
     */
    public function up(): void
    {
        self::recreate('seller_subscriptions', 'plans');

        /* ── پلن‌های اشتراک ── */
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('title', 80);
            $table->string('tagline', 160)->nullable();      // یک خط معرفی روی کارت
            $table->json('features')->nullable();            // ردیف‌های ویژگی کارت
            $table->unsignedInteger('duration_days');        // ۳۰ / ۱۸۰ / ۳۶۵ …
            $table->unsignedBigInteger('price')->default(0); // تومان (۰ = رایگان)
            $table->string('commission_type', 10)->nullable();   // null | percent | fixed
            $table->unsignedBigInteger('commission_value')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();

            $table->index(['is_active', 'sort']);
        });

        /* ── اشتراک‌های فروشندگان (تاریخچه + فعال) ── */
        Schema::create('seller_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_id');              // FK added defensively below
            // trial rows have NO plan (plan_id null) — nullOnDelete so a
            // deleted plan never deletes the subscription history
            $table->foreignId('plan_id')->nullable();    // FK added defensively below
            $table->timestamp('starts_at');
            $table->timestamp('ends_at');
            $table->unsignedBigInteger('price_paid')->default(0); // ۰ = هدیه/تریال
            $table->string('source', 16)->default('admin');       // admin | wallet | trial
            $table->boolean('is_trial')->default(false);
            $table->timestamp('reminded_at')->nullable();         // یک‌بار یادآوری پایان
            $table->string('note', 200)->nullable();              // توضیح مدیر
            $table->timestamps();

            $table->index(['seller_id', 'ends_at']);
            $table->index('ends_at');
        });

        self::foreignKeyIfMissing('seller_subscriptions', 'seller_id', 'sellers', 'id');
        self::foreignKeyIfMissing('seller_subscriptions', 'plan_id', 'plans', 'id', onDelete: 'null');
    }

    public function down(): void
    {
        Schema::dropIfExists('seller_subscriptions');
        Schema::dropIfExists('plans');
    }
};
