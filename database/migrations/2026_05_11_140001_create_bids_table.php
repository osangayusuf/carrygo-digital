<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bids', function (Blueprint $table) {
            $table->id();
            $table->foreignId('auction_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete()->index();
            $table->integer('amount');
            $table->boolean('is_winning')->default(false);
            $table->timestamp('created_at', 6)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bids');
    }
};
