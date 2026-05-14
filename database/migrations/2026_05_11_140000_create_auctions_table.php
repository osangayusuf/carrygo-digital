<?php

use App\Enums\AuctionStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('auctions', function (Blueprint $table) {
            $table->id();
            $table->string('category');
            $table->string('name');
            $table->text('description');
            $table->integer('opening_points');
            $table->integer('current_points')->default(0);
            $table->string('status')->default(AuctionStatus::DRAFT->value)->index();
            $table->string('image')->nullable();
            $table->integer('bid_count')->default(0);
            $table->integer('countdown_duration_seconds');
            $table->timestamp('triggered_at')->nullable();
            $table->timestamp('expires_at')->nullable()->index();
            $table->foreignId('winner_id')->nullable()->constrained('users', indexName: 'fk_auctions_winner_id')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auctions');
    }
};
