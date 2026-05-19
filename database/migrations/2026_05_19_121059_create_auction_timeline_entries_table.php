<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('auction_timeline_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('auction_id')->constrained(indexName: 'fk_timeline_auction_id')->cascadeOnDelete();
            $table->string('type');
            $table->foreignId('user_id')->nullable()->constrained(indexName: 'fk_timeline_user_id')->nullOnDelete();
            $table->foreignId('bid_id')->nullable()->constrained(indexName: 'fk_timeline_bid_id')->nullOnDelete();
            $table->json('payload')->nullable();
            $table->timestamp('occurred_at', 6);
            $table->index(['auction_id', 'occurred_at'], 'timeline_auction_occurred_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auction_timeline_entries');
    }
};
