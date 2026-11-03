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
        // The wallet_transactions table itself is created by a later-dated
        // migration (2026_11_06_000001) — on a fresh install there is nothing
        // to alter here, and on databases that already ran the newer create
        // the columns may exist as well, so guard both cases.
        if (! Schema::hasTable('wallet_transactions')) {
            return;
        }

        if (Schema::hasColumn('wallet_transactions', 'card_number')
            && Schema::hasColumn('wallet_transactions', 'admin_note')) {
            return;
        }

        Schema::table('wallet_transactions', function (Blueprint $table) {
            if (! Schema::hasColumn('wallet_transactions', 'card_number')) {
                $table->string('card_number', 20)->nullable();
            }
            if (! Schema::hasColumn('wallet_transactions', 'admin_note')) {
                $table->text('admin_note')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->dropColumn(['card_number', 'admin_note']);
        });
    }
};
