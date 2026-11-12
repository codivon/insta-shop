<?php

use App\Support\Database\Concerns\DefensiveMigration;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * v0.32.0 — محصولات مستثنی از خرید اقساطی (seller-level BNPL exclusions):
 *
 * A seller who opted an installment (BNPL) gateway in can now pull
 * INDIVIDUAL products out of installment selling («نمی‌خوام چندتا از
 * محصولاتم اقساطی فروخته بشن»). One row per (seller, product).
 *
 * Effect of a row:
 *  • checkout  → the product can NOT ride an installment gateway; when the
 *                customer picks one, the eligibility modal names the product
 *                (and every shop that did not enable the gateway) and offers
 *                a one-click removal; the whole BNPL section disappears when
 *                no cart seller supports it at all.
 *  • product   → the «امکان خرید اقساطی» badge never shows for an excluded
 *                product, even with show_on_product ticked.
 *  • fee       → surcharges are computed on the shop's ELIGIBLE goods only,
 *                so excluded lines never carry an installment fee.
 */
return new class extends Migration
{
    use DefensiveMigration;

    public function up(): void
    {
        self::recreate('installment_exclusions');

        Schema::create('installment_exclusions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('seller_id')->constrained('sellers')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->timestamps();

            // one exclusion per seller+product — toggling is idempotent
            $table->unique(['seller_id', 'product_id']);
            $table->index('product_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('installment_exclusions');
    }
};
