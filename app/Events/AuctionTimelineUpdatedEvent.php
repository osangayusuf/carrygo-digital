<?php

namespace App\Events;

use App\Http\Resources\AuctionTimelineEntryResource;
use App\Models\AuctionTimelineEntry;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AuctionTimelineUpdatedEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public readonly AuctionTimelineEntry $entry) {}

    /**
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('auction.'.$this->entry->auction_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'TimelineUpdated';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        $this->entry->loadMissing(['user', 'bid']);

        return [
            'entry' => (new AuctionTimelineEntryResource($this->entry))->resolve(),
        ];
    }
}
