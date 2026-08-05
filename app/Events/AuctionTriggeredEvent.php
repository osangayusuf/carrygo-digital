<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AuctionTriggeredEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @param  string  $expiresAt  ISO 8601 datetime string — sent directly to client for countdown.
     * @param  string  $status  Auction status value ('triggered').
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
        return 'AuctionTriggered';
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
