<?php

namespace App\Events\Support;

use App\Enums\ChatSenderType;
use App\Models\ChatMessage;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChatMessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly ChatMessage $message
    ) {}

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        // Load the chatSession if not loaded, to retrieve the UUID
        $session = $this->message->chatSession;

        return [
            new Channel("chat.{$session->uuid}"),
        ];
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        $sender = $this->message->sender;
        $senderName = $sender?->name;

        if ($sender && $this->message->sender_type === ChatSenderType::AGENT) {
            $senderName = $sender->agent_display_name;
        }

        return [
            'id' => $this->message->id,
            'chat_session_id' => $this->message->chat_session_id,
            'sender_id' => $this->message->sender_id,
            'sender_type' => $this->message->sender_type->value,
            'body' => $this->message->body,
            'created_at' => $this->message->created_at->toIso8601String(),
            'sender' => $sender ? [
                'id' => $sender->id,
                'name' => $senderName,
            ] : null,
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'ChatMessageSent';
    }
}
