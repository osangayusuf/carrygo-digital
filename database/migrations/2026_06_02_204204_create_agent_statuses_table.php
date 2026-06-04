<?php

use App\Enums\AgentStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agent_statuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users', indexName: 'fk_agent_statuses_user_id')->cascadeOnDelete();
            $table->string('status')->default(AgentStatus::OFFLINE->value)->index();
            $table->timestamp('last_activity_at')->nullable();
            $table->boolean('manual_override')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agent_statuses');
    }
};
