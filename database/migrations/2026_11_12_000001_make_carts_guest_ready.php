<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Guest-ready carts:
     *  - carts.customer_id becomes nullable;
     *  - carts.guest_token (unique) identifies the cart of a NOT-LOGGED-IN
     *    visitor. The token rides a shared domain cookie, so the SAME guest
     *    cart is visible on the public storefront (landing panel session)
     *    AND on the customer-app pages (customer panel session) — the two
     *    panels keep their isolated sessions but share plain cookies.
     *  - On login the guest cart is merged into the customer's own cart
     *    (App\Http\Middleware\MergeGuestCart + PhoneLogin).
     */
    public function up(): void
    {
        Schema::table('carts', function (Blueprint $table) {
            $table->dropForeign(['customer_id']);
            $table->dropUnique(['customer_id']);

            $table->string('guest_token', 64)->nullable()->unique()->after('id');
            $table->foreignId('customer_id')->nullable()->change();

            $table->foreign('customer_id')
                ->references('id')->on('customers')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('carts', function (Blueprint $table) {
            $table->dropForeign(['customer_id']);
            $table->dropColumn('guest_token');

            $table->foreignId('customer_id')->change();
            $table->unique('customer_id');

            $table->foreign('customer_id')
                ->references('id')->on('customers')
                ->cascadeOnDelete();
        });
    }
};
