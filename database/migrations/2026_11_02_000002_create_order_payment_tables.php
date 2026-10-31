<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Phase 5 — payment data layer:
     * orders (customer checkout snapshots), order_items (product snapshots,
     * survive product deletion) and payment_logs (per-attempt gateway audit).
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();

            // pending | paid | failed | canceled
            $table->string('status', 20)->default('pending')->index();

            // Snapshot totals (Toman) — computed from valid cart items at checkout
            $table->unsignedBigInteger('total_amount')->default(0);
            $table->unsignedInteger('items_count')->default(0);

            // Delivery snapshot
            $table->string('receiver_name', 80);
            $table->string('delivery_phone', 15);
            $table->string('delivery_address', 500);
            $table->string('postal_code', 20)->nullable();

            // Gateway bookkeeping
            $table->string('gateway', 30)->default('local');
            $table->string('gateway_ref', 100)->nullable()->index(); // transactionId from purchase
            $table->string('receipt_ref', 100)->nullable();          // referenceNo after verify
            $table->timestamp('paid_at')->nullable();

            $table->timestamps();

            $table->index(['customer_id', 'created_at']);
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();

            // Nullable + nullOnDelete: the snapshot below survives deletion
            $table->foreignId('shop_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();

            // Immutable snapshot of the product at purchase time
            $table->string('product_name', 150);
            $table->unsignedBigInteger('unit_price')->default(0);
            $table->unsignedInteger('quantity')->default(1);

            $table->timestamps();

            $table->index('order_id');
            $table->index('shop_id');
        });

        Schema::create('payment_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('gateway', 30);
            $table->string('event', 30); // purchase | redirect | precheck_failed | verify_ok | verify_failed
            $table->string('transaction_id', 100)->nullable();
            $table->unsignedBigInteger('amount')->nullable();
            $table->boolean('ok')->default(true);
            $table->text('message')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index('order_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_logs');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
