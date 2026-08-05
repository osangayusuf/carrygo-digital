<?php

namespace App\Notifications;

use App\Models\TicketMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Notifies a ticket's customer, by email, that support staff have replied.
 *
 * Mail-only: customers have no in-app notification center (that UI lives under the
 * agent/admin-only /support/* routes), so there's nowhere to render a database
 * notification yet. There's also no customer-facing ticket page to link to, so the
 * full reply is included inline rather than pointing at a "View Ticket" action.
 */
class TicketReplyNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly TicketMessage $message
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
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $ticket = $this->message->ticket;

        return (new MailMessage)
            ->subject("New reply on your support ticket #{$ticket->id}")
            ->greeting("Hello {$notifiable->name},")
            ->line("{$this->message->sender->name} from our support team replied to your ticket #{$ticket->id} ({$ticket->subject}).")
            ->line('Their reply:')
            ->line('"'.$this->message->body.'"')
            ->line('If you have more questions, please start a new live chat with our support team.');
    }
}
