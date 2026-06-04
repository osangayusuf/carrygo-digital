<?php

namespace Database\Factories;

use App\Enums\ChatSenderType;
use App\Models\ChatMessage;
use App\Models\ChatSession;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ChatMessage>
 */
class ChatMessageFactory extends Factory
{
    public function definition(): array
    {
        return [
            'chat_session_id' => ChatSession::factory(),
            'sender_id' => User::factory(),
            'sender_type' => ChatSenderType::CUSTOMER,
            'body' => fake()->sentence(),
        ];
    }

    public function fromAgent(User $agent): static
    {
        return $this->state(fn (array $attributes) => [
            'sender_id' => $agent->id,
            'sender_type' => ChatSenderType::AGENT,
        ]);
    }

    public function system(): static
    {
        return $this->state(fn (array $attributes) => [
            'sender_id' => null,
            'sender_type' => ChatSenderType::SYSTEM,
        ]);
    }
}
