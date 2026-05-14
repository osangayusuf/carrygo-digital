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
            $table->foreignId('auction_id')->constrained(indexName: 'fk_bids_auction_id')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained(indexName: 'fk_bids_user_id')->cascadeOnDelete();
            $table->index('user_id', 'bids_user_id_index');
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
