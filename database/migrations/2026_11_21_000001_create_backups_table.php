<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Backups — every backup file (created here OR uploaded for restore)
 * gets a row so the admin can watch progress, download or delete it.
 *
 * type:    manual | auto | upload
 * status:  pending | processing | completed | failed
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('backups', function (Blueprint $table) {
            $table->id();
            $table->string('filename')->nullable();      // stored name on disk
            $table->string('original_name')->nullable(); // for uploaded files
            $table->string('path')->nullable();          // relative path on the local disk
            $table->unsignedBigInteger('size')->default(0);
            $table->string('type')->default('manual')->index();
            $table->string('status')->default('pending')->index();
            $table->text('error')->nullable();
            $table->unsignedInteger('tables_total')->default(0);
            $table->unsignedInteger('tables_done')->default(0);
            $table->unsignedBigInteger('records_count')->default(0);
            $table->unsignedBigInteger('rows_inserted')->default(0);
            $table->unsignedBigInteger('rows_skipped')->default(0);
            $table->text('tables')->nullable(); // json: table => row count
            $table->foreignId('admin_id')->nullable()->constrained('admins')->nullOnDelete();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('backups');
    }
};
