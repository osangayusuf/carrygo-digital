<?php

namespace Database\Factories;

use App\Enums\TicketCategory;
use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Ticket>
 */
class TicketFactory extends Factory
{
    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'customer_id' => User::factory(),
            'agent_id' => null,
            'status' => TicketStatus::OPEN,
            'priority' => TicketPriority::NORMAL,
            'category' => fake()->randomElement(TicketCategory::cases()),
            'subject' => fake()->sentence(),
            'closed_at' => null,
        ];
    }

    public function open(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TicketStatus::OPEN,
            'agent_id' => null,
            'closed_at' => null,
        ]);
    }

    public function inProgress(User $agent): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TicketStatus::IN_PROGRESS,
            'agent_id' => $agent->id,
            'closed_at' => null,
        ]);
    }

    public function closed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TicketStatus::CLOSED,
            'closed_at' => now(),
        ]);
    }

    public function urgent(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => TicketPriority::URGENT,
        ]);
    }
}
