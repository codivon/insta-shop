<?php

use App\Models\Setting;
use App\Support\Database\Concerns\DefensiveMigration;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    use DefensiveMigration;

    /**
     * v0.24.0 — «نردبانِ طرح‌دار + واریز درگاهی + پلن یک‌بارمصرف»:
     *
     *   bump_plans          → طرح‌های نردبانِ تعریف‌شدنی توسط مدیر
     *                         («مثلاً ۱۰ روز با این قیمت») — صفحهٔ جدا در
     *                         پنل مدیریت؛ فروشنده یکی را انتخاب و از کیف
     *                         پول پرداخت می‌کند. (نرخ روزانهٔ قدیمی حذف —
     *                         فقط toggle روشن/خاموش در تنظیمات می‌ماند.)
     *   plans.one_time      → «خرید یک‌بار مصرف (بدون تمدید)»: هر فروشنده
     *                         فقط یک بار می‌تواند این پلن را بخرد.
     *   product_bumps.bump_plan_id → ردّ تاریخچهٔ هر نردبان به طرحِ خریداری‌شده.
     *   wallet_deposits     → واریزِ کیف پول از درگاه پرداخت (مبلغ دلخواه،
     *                         کال‌بک امضاشده، شارژ فقط پس از وریفای درگاه).
     *
     * Foreign keys via the defensive helper (v0.21.2 lesson) — never inline.
     */
    public function up(): void
    {
        /* ── طرح‌های نردبان ── */
        if (! Schema::hasTable('bump_plans')) {
            Schema::create('bump_plans', function (Blueprint $table) {
                $table->id();
                $table->string('title', 80);                     // «نردبان ۱۰ روزه»
                $table->unsignedInteger('days');                 // مدت نردبان
                $table->unsignedBigInteger('price')->default(0); // تومان
                $table->boolean('is_active')->default(true);
                $table->unsignedInteger('sort')->default(0);
                $table->timestamps();

                $table->index(['is_active', 'sort']);
            });
        }

        /* ── گزینهٔ خرید یک‌بارمصرف پلن اشتراک ── */
        if (! Schema::hasColumn('plans', 'one_time')) {
            Schema::table('plans', function (Blueprint $table): void {
                $table->boolean('one_time')->default(false)->after('boost_level');
            });
        }

        /* ── طرحِ خریداری‌شدهٔ هر نردبان (تاریخچه) ── */
        if (Schema::hasTable('product_bumps') && ! Schema::hasColumn('product_bumps', 'bump_plan_id')) {
            Schema::table('product_bumps', function (Blueprint $table): void {
                $table->foreignId('bump_plan_id')->nullable()->after('price_paid');
                $table->index('bump_plan_id');
            });

            self::foreignKeyIfMissing('product_bumps', 'bump_plan_id', 'bump_plans', 'id', onDelete: 'null');
        }

        /* ── واریزهای کیف پول از درگاه ── */
        if (! Schema::hasTable('wallet_deposits')) {
            Schema::create('wallet_deposits', function (Blueprint $table) {
                $table->id();
                $table->foreignId('seller_id');                  // FK defensively below
                $table->foreignId('wallet_id');                  // FK defensively below
                $table->unsignedBigInteger('amount');            // تومان
                $table->string('gateway', 32);                   // local | zarinpal | zibal | …
                $table->string('gateway_ref', 64)->nullable();   // transactionId درگاه
                $table->string('status', 16)->default('pending'); // pending | done | failed | canceled
                $table->string('message', 200)->nullable();      // دلیل شکست / یادداشت
                $table->json('meta')->nullable();                // جزئیات رسید
                $table->timestamps();

                $table->index(['wallet_id', 'status']);
                $table->index('status');
                $table->index('seller_id');
            });

            self::foreignKeyIfMissing('wallet_deposits', 'seller_id', 'sellers', 'id');
            self::foreignKeyIfMissing('wallet_deposits', 'wallet_id', 'wallets', 'id');
        }

        /* ── مهاجرت تنظیمات: نرخ روزانهٔ قدیمی → toggle روشن/خاموش ──
         * فروشگاهی که روی v0.23.0 نرخ روزانهٔ نردبان had passed (>0) —
         * نردبانش با toggle روشن ادامه می‌یابد؛ بقیه خاموش می‌مانند. */
        try {
            if (Setting::get('shops.bump_enabled') === null
                && (int) Setting::get('shops.bump_price_per_day', 0) > 0) {
                Setting::set('shops.bump_enabled', true, 'boolean');
            }
        } catch (Throwable) {
            // a fresh install without the settings table yet — the toggle defaults to off
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('wallet_deposits');

        if (Schema::hasTable('product_bumps') && Schema::hasColumn('product_bumps', 'bump_plan_id')) {
            $fks = collect(Schema::getForeignKeys('product_bumps'))
                ->pluck('name')
                ->map(fn ($name) => strtolower((string) $name));

            Schema::table('product_bumps', function (Blueprint $table) use ($fks): void {
                // the FK must go before the column on MySQL (or the drop fails)
                if ($fks->contains('product_bumps_bump_plan_id_foreign')) {
                    $table->dropForeign('product_bumps_bump_plan_id_foreign');
                }

                try {
                    $table->dropIndex('product_bumps_bump_plan_id_index');
                } catch (Throwable) {
                    // index already gone (another partial run dropped it)
                }

                $table->dropColumn('bump_plan_id');
            });
        }

        if (Schema::hasColumn('plans', 'one_time')) {
            Schema::table('plans', function (Blueprint $table): void {
                $table->dropColumn('one_time');
            });
        }

        Schema::dropIfExists('bump_plans');
    }
};
