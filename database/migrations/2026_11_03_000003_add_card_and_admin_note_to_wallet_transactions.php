<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Withdrawals are now paid to the seller's bank CARD (the sheba input is
 * gone from the seller's request box) — the destination card is snapshotted
 * onto the request, and the admin's approve/reject note (توضیحات) is kept
 * so the seller can read exactly why a request was paid or refused.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->string('card_number', 20)->nullable()->after('sheba');
            $table->text('admin_note')->nullable()->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->dropColumn(['card_number', 'admin_note']);
        });
    }
};
