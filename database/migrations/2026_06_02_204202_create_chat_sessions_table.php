<?php

use App\Enums\ChatSessionStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_sessions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('customer_id')->nullable()->constrained('users', indexName: 'fk_chat_sessions_customer_id')->nullOnDelete();
            $table->string('customer_name')->nullable();
            $table->string('customer_email')->nullable();
            $table->foreignId('agent_id')->nullable()->constrained('users', indexName: 'fk_chat_sessions_agent_id')->nullOnDelete();
            $table->foreignId('ticket_id')->nullable()->constrained('tickets', indexName: 'fk_chat_sessions_ticket_id')->nullOnDelete();
            $table->string('status')->default(ChatSessionStatus::WAITING->value)->index();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_sessions');
    }
};
