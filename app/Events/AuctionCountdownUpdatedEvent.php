<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Broadcast when an admin changes the countdown duration of an auction that has
 * already been triggered, shifting its expiry. Clients listening on the auction
 * channel should re-anchor their countdown to the new expires_at.
 */
class AuctionCountdownUpdatedEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @param  string  $expiresAt  ISO 8601 datetime string — sent directly to client for countdown.
     * @param  string  $status  Auction status value.
     */
    public function __construct(
        public readonly int $auctionId,
        public readonly string $expiresAt,
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

    public function broadcastAs(): string
    {
        return 'AuctionCountdownUpdated';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'expires_at' => $this->expiresAt,
            'status' => $this->status,
        ];
    }
}
