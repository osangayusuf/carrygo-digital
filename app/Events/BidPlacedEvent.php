<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BidPlacedEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @param  int|null  $winningUserId  The user_id of the current leader, or null if tied.
     */
    public function __construct(
        public readonly int $auctionId,
        public readonly int $currentPoints,
        public readonly int $bidCount,
        public readonly ?int $winningUserId,
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
            'current_points' => $this->currentPoints,
            'bid_count' => $this->bidCount,
            'winning_user_id' => $this->winningUserId,
        ];
    }
}
