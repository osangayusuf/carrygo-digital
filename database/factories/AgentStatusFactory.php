<?php

namespace Database\Factories;

use App\Enums\AgentStatus;
use App\Models\AgentStatus as AgentStatusModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AgentStatusModel>
 */
class AgentStatusFactory extends Factory
{
    protected $model = AgentStatusModel::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'status' => AgentStatus::OFFLINE,
            'last_activity_at' => null,
            'manual_override' => false,
        ];
    }

    public function online(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => AgentStatus::ONLINE,
            'last_activity_at' => now(),
            'manual_override' => false,
        ]);
    }

    public function away(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => AgentStatus::AWAY,
            'last_activity_at' => now()->subMinutes(15),
            'manual_override' => false,
        ]);
    }

    public function offline(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => AgentStatus::OFFLINE,
            'last_activity_at' => null,
            'manual_override' => false,
        ]);
    }

    public function manualOverride(AgentStatus $status): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => $status,
            'manual_override' => true,
        ]);
    }
}
