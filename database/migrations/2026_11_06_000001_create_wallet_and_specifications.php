<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Seller wallet + product specifications:
 *  - products.specifications      → JSON groups of {title, value} specs (seller-managed)
 *  - wallets                      → one wallet per seller (running balance in Toman)
 *  - wallet_transactions          → ledger: deposit (paid order share) / withdrawal
 *                                   (pending → done|rejected, balance reserved on request)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->json('specifications')->nullable()->after('description');
        });

        Schema::dropIfExists('wallet_transactions');
        Schema::dropIfExists('wallets');

        Schema::create('wallets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('seller_id')->unique();
            $table->unsignedBigInteger('balance')->default(0); // Toman
            $table->timestamps();

            $table->foreign('seller_id')->references('id')->on('sellers')->cascadeOnDelete();
        });

        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wallet_id')->constrained('wallets')->cascadeOnDelete();
            $table->string('type', 20);                 // deposit | withdrawal
            $table->unsignedBigInteger('amount');        // Toman
            $table->string('status', 20)->default('done'); // done | pending | rejected
            $table->unsignedBigInteger('order_id')->nullable();
            $table->string('description')->nullable();
            $table->string('sheba', 30)->nullable();     // withdrawal destination
            $table->timestamps();

            $table->index(['wallet_id', 'created_at']);
            $table->foreign('order_id')->references('id')->on('orders')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallet_transactions');
        Schema::dropIfExists('wallets');

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('specifications');
        });
    }
};
