<?php

use App\Support\Database\Concerns\DefensiveMigration;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * Support ticket system — sellers AND customers open tickets to the
 * admin panel and chat with the support staff in a threaded box.
 */
return new class extends Migration
{
    use DefensiveMigration;

    public function up(): void
    {
        self::recreate('support_ticket_messages', 'support_tickets');

        Schema::create('support_tickets', function (Blueprint $table) {
            $table->id();
            $table->string('subject', 120);
            $table->string('category', 30)->default('other')->index();
            $table->string('status', 20)->default('open')->index(); // open | answered | closed
            $table->morphs('ticketable'); // App\Models\Seller or App\Models\Customer
            $table->timestamp('last_reply_at')->nullable()->index();
            $table->timestamps();
        });

        Schema::create('support_ticket_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('support_tickets')->cascadeOnDelete();
            $table->morphs('author'); // Seller | Customer | Admin
            $table->text('body');
            $table->timestamps();

            $table->index(['ticket_id', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('support_ticket_messages');
        Schema::dropIfExists('support_tickets');
    }
};
