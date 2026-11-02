<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Seller-panel notification system (batch v0.12.7) — the seller-side twin
 * of the admin notifications:
 *   • seller_notifications → in-app bell/page rows scoped per seller;
 *   • push_subscriptions.audience / seller_id → Web-Push devices now
 *     belong to a panel (admin = global, seller = one seller only), so a
 *     seller never receives another seller's (or the admin's) pushes.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('seller_notifications')) {
            Schema::create('seller_notifications', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('seller_id')->index();
                $table->string('type', 40)->index();
                $table->string('title', 300);
                $table->string('body', 1000)->default('');
                $table->string('icon', 40)->default('bell');
                $table->string('link', 300)->nullable();
                $table->string('subject_type', 60)->nullable();
                $table->unsignedBigInteger('subject_id')->nullable();
                $table->boolean('is_read')->default(false)->index();
                $table->timestamp('read_at')->nullable();
                $table->timestamp('created_at')->nullable();
            });
        }

        Schema::table('push_subscriptions', function (Blueprint $table): void {
            if (! Schema::hasColumn('push_subscriptions', 'audience')) {
                $table->string('audience', 12)->default('admin')->after('device_label')
                    ->comment('admin|seller — which panel this device listens to');
            }

            if (! Schema::hasColumn('push_subscriptions', 'seller_id')) {
                $table->unsignedBigInteger('seller_id')->nullable()->after('audience')
                    ->comment('when audience=seller: the owning seller');
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seller_notifications');

        Schema::table('push_subscriptions', function (Blueprint $table): void {
            foreach (['seller_id', 'audience'] as $col) {
                if (Schema::hasColumn('push_subscriptions', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
