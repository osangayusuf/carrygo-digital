<?php

namespace App\Notifications;

use App\Models\Auction;
use App\Models\Bid;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class BidPlaced extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Bid $bid,
        public readonly Auction $auction,
    ) {
        $this->afterCommit = true;
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'bid_placed',
            'title' => "Your bid of {$this->bid->amount} pts on \"{$this->auction->name}\" was placed.",
            'auction_id' => $this->auction->id,
            'bid_id' => $this->bid->id,
            'amount' => $this->bid->amount,
            'message' => "Your bid of {$this->bid->amount} pts on \"{$this->auction->name}\" was placed.",
            'url' => '/auctions/'.$this->auction->id,
        ];
    }
}
