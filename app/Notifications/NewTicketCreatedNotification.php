<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class NewTicketCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Ticket $ticket
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
            'type' => 'new_ticket_created',
            'title' => 'New Ticket Created',
            'message' => "Ticket #{$this->ticket->id} has been created: {$this->ticket->subject}",
            'icon' => 'ticket',
            'url' => "/support/tickets/{$this->ticket->id}",
            'ticket_id' => $this->ticket->id,
        ];
    }
}
