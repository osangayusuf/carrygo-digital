<?php

use App\Enums\TransactionStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('point_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained(indexName: 'fk_point_transactions_user_id')->cascadeOnDelete();
            $table->index('user_id', 'point_transactions_user_id_index');
            $table->string('type');
            $table->decimal('amount', 15, 2);
            $table->decimal('naira_amount', 15, 2)->nullable();
            $table->decimal('exchange_rate', 10, 4);
            $table->string('provider_reference')->nullable();
            $table->string('status')->default(TransactionStatus::PENDING->value);
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('point_transactions');
    }
};
