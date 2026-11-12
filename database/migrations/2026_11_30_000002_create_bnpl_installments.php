<?php

use App\Support\Database\Concerns\DefensiveMigration;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * v0.30.0 — درگاه‌های پرداخت اقساطی (BNPL: دیجی‌پی / اسنپ‌پی / ترب‌پی):
 *
 *  1. seller_bnpl_settings — the seller-level opt-in of each BNPL gateway
 *     (فعال‌سازی + نمایش در صفحهٔ محصول + کارمزد درصدی/ثابت). One row per
 *     (seller, gateway).
 *  2. bnpl_settlements — the monthly settlement ledger: these gateways
 *     settle the MERCHANT monthly (پرداخت قسط اول در خرید، تسویهٔ پذیرنده
 *     سر ماه), so a paid BNPL order does NOT credit seller wallets
 *     instantly — it creates pending settlement rows that a daily cron
 *     (bnpl:settle) deposits on/after their due date (the admin-configured
 *     day of month) or via the admin's manual «واریز دستی».
 *  3. orders.bnpl_gateway + orders.bnpl_fee — which installment gateway
 *     carried the order and the total seller surcharge added to the
 *     PRODUCTS total (never the shipping).
 */
return new class extends Migration
{
    use DefensiveMigration;

    public function up(): void
    {
        self::recreate('seller_bnpl_settings', 'bnpl_settlements');

        Schema::create('seller_bnpl_settings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('seller_id')->constrained('sellers')->cascadeOnDelete();
            $table->string('gateway', 30); // digipay | snapppay | torobpay
            $table->boolean('enabled')->default(false);
            $table->boolean('show_on_product')->default(false);
            // 'none' | 'percent' | 'fixed' — the surcharge the seller adds
            // when a customer pays THEIR products through this gateway
            $table->string('fee_type', 10)->default('none');
            $table->unsignedBigInteger('fee_value')->default(0);
            $table->timestamps();

            $table->unique(['seller_id', 'gateway']);
            $table->index('gateway');
        });

        Schema::create('bnpl_settlements', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('seller_id')->constrained('sellers')->cascadeOnDelete();
            $table->foreignId('wallet_id')->nullable()->constrained()->nullOnDelete();
            $table->string('gateway', 30);
            // seller payout = shop items total + the shop's BNPL fee − the
            // commission on the items (the fee itself is never commissioned)
            $table->unsignedBigInteger('amount')->default(0);
            $table->unsignedBigInteger('commission')->default(0);
            $table->unsignedBigInteger('fee')->default(0);
            $table->unsignedBigInteger('items_total')->default(0);
            // pending | paid | canceled — plain string like every other
            // status column (sqlite-safe, PHP class-const whitelist)
            $table->string('status', 16)->default('pending');
            $table->date('due_date')->index();
            $table->timestamp('paid_at')->nullable();
            $table->foreignId('wallet_transaction_id')->nullable();
            $table->boolean('manual')->default(false); // deposited by the admin, not the cron
            $table->string('note', 200)->nullable(); // admin note for manual/canceled rows
            $table->timestamps();

            $table->index(['status', 'due_date']);
            $table->index(['seller_id', 'status']);
            $table->index(['gateway', 'status']);
        });

        self::columnIfMissing('orders', 'bnpl_gateway', function (Blueprint $table): void {
            $table->string('bnpl_gateway', 30)->nullable()->after('gateway_ref');
        });

        self::columnIfMissing('orders', 'bnpl_fee', function (Blueprint $table): void {
            $table->unsignedBigInteger('bnpl_fee')->default(0)->after('bnpl_gateway');
        });

        // per-shop surcharge breakdown {shop_id: fee} — frozen at checkout
        // so the monthly settlements always match what the customer paid
        self::columnIfMissing('orders', 'bnpl_fees', function (Blueprint $table): void {
            $table->json('bnpl_fees')->nullable()->after('bnpl_fee');
        });
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();

        Schema::dropIfExists('bnpl_settlements');
        Schema::dropIfExists('seller_bnpl_settings');

        Schema::enableForeignKeyConstraints();
    }
};
