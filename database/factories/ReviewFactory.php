<?php

namespace Database\Factories;

use App\Models\Auction;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Review>
 */
class ReviewFactory extends Factory
{
    public function definition(): array
    {
        return [
            'auction_id' => Auction::factory(),
            'user_id' => User::factory(),
            'rating' => $this->faker->randomFloat(1, 3.0, 5.0),
            'comment' => $this->faker->sentences(3, true),
            'social_platform' => $this->faker->optional(0.4)->randomElement(['Instagram', 'Twitter', 'TikTok']),
            'social_handle' => $this->faker->optional(0.4)->userName(),
        ];
    }
}
