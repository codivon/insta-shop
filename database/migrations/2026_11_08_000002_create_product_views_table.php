<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Daily product-view aggregation (گزارش بازدید):
 * one row per (product, day) with a running counter — powers the
 * seller «گزارش‌ها» page (views today / 7d / 30d + the 14-day chart)
 * without storing one row per hit (shared-hosting friendly).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_views', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('shop_id');
            $table->string('day', 10);                     // «Y-m-d» in app tz (Asia/Tehran) — plain string is storage-native on MySQL AND sqlite (no UTC shifting)
            $table->unsignedBigInteger('views')->default(0);
            $table->timestamps();

            $table->unique(['product_id', 'day']);
            $table->index(['shop_id', 'day']);
            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
            $table->foreign('shop_id')->references('id')->on('shops')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_views');
    }
};
