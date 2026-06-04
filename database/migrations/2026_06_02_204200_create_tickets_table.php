<?php

use App\Enums\TicketCategory;
use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('customer_id')->constrained('users', indexName: 'fk_tickets_customer_id')->cascadeOnDelete();
            $table->foreignId('agent_id')->nullable()->constrained('users', indexName: 'fk_tickets_agent_id')->nullOnDelete();
            $table->string('status')->default(TicketStatus::OPEN->value)->index();
            $table->string('priority')->default(TicketPriority::NORMAL->value)->index();
            $table->string('category')->default(TicketCategory::GENERAL->value);
            $table->string('subject');
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
