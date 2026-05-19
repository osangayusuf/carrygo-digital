<?php

namespace Database\Factories;

use App\Enums\AuctionTimelineEntryType;
use App\Models\Auction;
use App\Models\AuctionTimelineEntry;
use App\Models\Bid;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AuctionTimelineEntry>
 */
class AuctionTimelineEntryFactory extends Factory
{
    protected $model = AuctionTimelineEntry::class;

    public function definition(): array
    {
        return [
            'auction_id' => Auction::factory(),
            'type' => AuctionTimelineEntryType::BidPlaced,
            'user_id' => User::factory(),
            'bid_id' => null,
            'payload' => ['amount' => fake()->numberBetween(10, 100)],
            'occurred_at' => now(),
        ];
    }

    public function forBid(Bid $bid): static
    {
        return $this->state(fn (): array => [
            'auction_id' => $bid->auction_id,
            'type' => AuctionTimelineEntryType::BidPlaced,
            'user_id' => $bid->user_id,
            'bid_id' => $bid->id,
            'payload' => ['amount' => $bid->amount],
            'occurred_at' => $bid->created_at ?? now(),
        ]);
    }
}
