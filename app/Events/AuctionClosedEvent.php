<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AuctionClosedEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @param  int|null  $winnerId  The winner's user_id, or null if no bids were placed.
     * @param  string  $status  Auction status value ('closed').
     */
    public function __construct(
        public readonly int $auctionId,
        public readonly ?int $winnerId,
        public readonly string $status,
    ) {}

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel("auction.{$this->auctionId}"),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'winner_id' => $this->winnerId,
            'status' => $this->status,
        ];
    }
}
