<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Shipment lifecycle for paid orders (batch v0.12.7):
 *   • shipment_status → none | processing | shipped | delivered
 *   • tracking_code   → کد رهگیری پستی (mandatory when shipped)
 *   • shipment_status_at → last transition timestamp
 *
 * Sellers set these from their سفارش‌ها page; the admin panel mirrors
 * the same control on the order detail page.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->string('shipment_status', 20)->default('none')->after('status')
                ->comment('none|processing|shipped|delivered');
            $table->string('tracking_code', 60)->nullable()->after('shipment_status')
                ->comment('کد رهگیری پستی');
            $table->timestamp('shipment_status_at')->nullable()->after('tracking_code');

            $table->index('shipment_status');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropIndex(['shipment_status']);
            $table->dropColumn(['shipment_status', 'tracking_code', 'shipment_status_at']);
        });
    }
};
