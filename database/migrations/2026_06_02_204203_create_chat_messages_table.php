<?php

use App\Enums\ChatSenderType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chat_session_id')->constrained('chat_sessions', indexName: 'fk_chat_messages_chat_session_id')->cascadeOnDelete();
            $table->foreignId('sender_id')->nullable()->constrained('users', indexName: 'fk_chat_messages_sender_id')->nullOnDelete();
            $table->string('sender_type')->default(ChatSenderType::CUSTOMER->value);
            $table->text('body');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_messages');
    }
};
