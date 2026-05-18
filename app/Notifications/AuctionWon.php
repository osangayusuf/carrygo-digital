<?php

namespace App\Notifications;

use App\Models\Auction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AuctionWon extends Notification implements ShouldQueue
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
            ->subject("You Won — {$this->auction->name}")
            ->greeting('Congratulations! 🎉')
            ->line("You won the auction for \"{$this->auction->name}\".")
            ->line('Our team will be in touch shortly to arrange delivery.')
            ->action('View Auction', url('/auctions/'.$this->auction->id));
    }

    /**
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'auction_won',
            'title' => "Congratulations! You won the auction for \"{$this->auction->name}\".",
            'auction_id' => $this->auction->id,
            'message' => "Congratulations! You won the auction for \"{$this->auction->name}\".",
            'url' => '/auctions/'.$this->auction->id,
        ];
    }
}
