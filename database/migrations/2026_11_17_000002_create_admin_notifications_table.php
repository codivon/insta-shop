<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * In-app notifications for the ADMIN panel («نوتیفیکیشن‌ها») — every
 * important event (order, payment, seller, security, …) lands here and
 * shows up in the topbar bell + the notifications page.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('admin_notifications')) {
            return;
        }

        Schema::create('admin_notifications', function (Blueprint $table) {
            $table->id();
            $table->string('type', 60)->index();       // order.new | order.paid | seller.new | …
            $table->string('title', 300);
            $table->string('body', 1000)->default('');
            $table->string('icon', 40)->default('bell');
            $table->string('link', 500)->nullable();   // panel link to open on click
            $table->string('subject_type', 80)->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->boolean('is_read')->default(false)->index();
            $table->timestamp('read_at')->nullable();
            $table->timestamp('created_at')->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_notifications');
    }
};
