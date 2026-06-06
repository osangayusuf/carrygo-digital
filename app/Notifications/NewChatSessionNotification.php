<?php

namespace App\Notifications;

use App\Models\ChatSession;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class NewChatSessionNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly ChatSession $session
    ) {
        $this->afterCommit = true;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'new_chat_session',
            'title' => 'New Chat Session',
            'message' => "New chat request from {$this->session->customer_name}.",
            'icon' => 'message-square',
            'url' => '/support/chat',
            'session_uuid' => $this->session->uuid,
        ];
    }
}
