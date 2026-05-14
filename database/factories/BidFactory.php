<?php

namespace Database\Factories;

use App\Models\Auction;
use App\Models\Bid;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Bid>
 */
class BidFactory extends Factory
{
    public function definition(): array
    {
        return [
            'auction_id' => Auction::factory(),
            'user_id' => User::factory(),
            'amount' => config('points.min_bid_increment', 10),
            'is_winning' => false,
        ];
    }

    public function winning(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_winning' => true,
        ]);
    }

    public function forAuction(Auction $auction): static
    {
        return $this->state(fn (array $attributes) => [
            'auction_id' => $auction->id,
        ]);
    }

    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }
}
