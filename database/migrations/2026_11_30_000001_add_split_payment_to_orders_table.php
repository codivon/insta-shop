<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * پرداخت تسهیمی (v0.27.0) — flag on the order remembering that the
 * seller's share was settled DIRECTLY to the seller's bank destination
 * through the gateway's split-payment API at purchase time. When set,
 * finalizePaid skips the wallet credit (the money never enters the
 * platform account) but still persists the commissions snapshot so the
 * income reports stay complete.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->boolean('split_payment')->default(false)->after('gateway_ref');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropColumn('split_payment');
        });
    }
};
