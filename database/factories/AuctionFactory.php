<?php

namespace Database\Factories;

use App\Enums\AuctionStatus;
use App\Models\Auction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Auction>
 */
class AuctionFactory extends Factory
{
    public function definition(): array
    {
        $open_points = fake()->numberBetween(100, 10000);

        return [
            'category' => fake()->randomElement(['Electronics', 'Furniture', 'Clothing', 'Vehicles', 'Appliances', 'Computing', 'Gaming', 'Kitchen']),
            'name' => fake()->words(3, true),
            'description' => fake()->paragraph(),
            'opening_points' => $open_points,
            'current_points' => 0,
            'status' => AuctionStatus::DRAFT,
            'price' => $open_points * 10,
            'image' => 'https://images.unsplash.com/photo-1611348586755-53860f7ae57a?w=900&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxzZWFyY2h8MTR8fHBsYWNlaG9sZGVyfGVufDB8fDB8fHww',
            'bid_count' => 0,
            'countdown_duration_seconds' => fake()->randomElement([3600, 7200, 10800, 14400]),
            'triggered_at' => null,
            'expires_at' => null,
            'winner_id' => null,
        ];
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => AuctionStatus::DRAFT,
            'triggered_at' => null,
            'expires_at' => null,
            'winner_id' => null,
        ]);
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => AuctionStatus::ACTIVE,
            'triggered_at' => null,
            'expires_at' => null,
            'winner_id' => null,
        ]);
    }

    public function triggered(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => AuctionStatus::TRIGGERED,
            'triggered_at' => now(),
            'expires_at' => now()->addSeconds($attributes['countdown_duration_seconds'] ?? 300),
            'winner_id' => null,
        ]);
    }

    public function closed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => AuctionStatus::CLOSED,
            'triggered_at' => now()->subMinutes(10),
            'expires_at' => now()->subMinutes(5),
            'winner_id' => null,
        ]);
    }

    public function withWinner(User $winner): static
    {
        return $this->state(fn (array $attributes) => [
            'winner_id' => $winner->id,
        ]);
    }
}
