<?php

namespace Database\Factories;

use App\Enums\ActivityType;
use App\Models\User;
use App\Models\UserActivity;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UserActivity>
 */
class UserActivityFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'type' => fake()->randomElement(ActivityType::cases())->value,
            'subject_type' => null,
            'subject_id' => null,
            'metadata' => null,
            'ip_address' => fake()->ipv4(),
        ];
    }

    /**
     * Set a specific activity type.
     */
    public function ofType(ActivityType $type): static
    {
        return $this->state(['type' => $type->value]);
    }

    /**
     * Mark as a guest (unauthenticated) activity.
     */
    public function asGuest(): static
    {
        return $this->state(['user_id' => null]);
    }
}
