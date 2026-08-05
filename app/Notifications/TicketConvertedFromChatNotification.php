<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Notifies a customer, by email, that their live chat session has been converted into a
 * support ticket. This is the only signal a customer gets about the conversion if they've
 * already left the chat widget — the widget itself only gets a live system message.
 *
 * Mail-only for the same reasons as TicketReplyNotification: no customer notification
 * center and no customer-facing ticket page to link to exist yet.
 */
class TicketConvertedFromChatNotification extends Notification implements ShouldQueue
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
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Your chat has been converted to support ticket #{$this->ticket->id}")
            ->greeting("Hello {$notifiable->name},")
            ->line("Your recent live chat has been converted into support ticket #{$this->ticket->id} ({$this->ticket->subject}) so our team can continue looking into it.")
            ->line("You don't need to do anything right now — we'll email you here as soon as a support agent replies.")
            ->line('If you have more to add in the meantime, please start a new live chat with our support team.');
    }
}
