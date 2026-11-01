<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Seller bank cards (کارت‌های بانکی):
 * managed from the seller profile / wallet — used to pre-fill the
 * withdrawal sheba and later for payouts. Each seller keeps a small
 * list; one card can be flagged as the default destination.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bank_cards', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('seller_id');
            $table->string('full_name', 60);              // صاحب کارت
            $table->string('card_number', 19);            // 16 digits, stored plain-Latin
            $table->string('sheba', 26);                  // IR + 24 digits
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->unique(['seller_id', 'card_number']);
            $table->foreign('seller_id')->references('id')->on('sellers')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_cards');
    }
};
