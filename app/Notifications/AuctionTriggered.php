<?php

namespace App\Notifications;

use App\Models\Auction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AuctionTriggered extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly Auction $auction)
    {
        $this->afterCommit = true;
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Auction Countdown Started — {$this->auction->name}")
            ->greeting('The countdown has started! ⏱️')
            ->line("The auction \"{$this->auction->name}\" has reached its opening threshold.")
            ->line("The timer ends at: {$this->auction->expires_at->format('H:i, d M Y')}.")
            ->action('View Auction', url('/auctions/'.$this->auction->id))
            ->line('Place your highest bid before the timer runs out!');
    }

    /**
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'auction_triggered',
            'auction_id' => $this->auction->id,
            'expires_at' => $this->auction->expires_at->toIso8601String(),
            'message' => "Countdown started for \"{$this->auction->name}\"! Timer ends at {$this->auction->expires_at->format('H:i')}.",
            'url' => '/auctions/'.$this->auction->id,
        ];
    }
}
