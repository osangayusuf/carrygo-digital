<?php

namespace Database\Factories;

use App\Enums\ChatSessionStatus;
use App\Models\ChatSession;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ChatSession>
 */
class ChatSessionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'customer_id' => User::factory(),
            'customer_name' => null,
            'customer_email' => null,
            'agent_id' => null,
            'ticket_id' => null,
            'status' => ChatSessionStatus::WAITING,
            'started_at' => null,
            'closed_at' => null,
        ];
    }

    public function waiting(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ChatSessionStatus::WAITING,
            'agent_id' => null,
            'started_at' => null,
        ]);
    }

    public function active(User $agent): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ChatSessionStatus::ACTIVE,
            'agent_id' => $agent->id,
            'started_at' => now(),
        ]);
    }

    public function closed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ChatSessionStatus::CLOSED,
            'closed_at' => now(),
        ]);
    }

    public function guest(): static
    {
        return $this->state(fn (array $attributes) => [
            'customer_id' => null,
            'customer_name' => fake()->name(),
            'customer_email' => fake()->safeEmail(),
        ]);
    }
}
