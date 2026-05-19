<?php

namespace App\Http\Resources;

use App\Enums\AuctionTimelineEntryType;
use App\Models\AuctionTimelineEntry;
use App\Services\AuctionTimelineService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin AuctionTimelineEntry */
class AuctionTimelineEntryResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $viewer = $request->user();
        $timeline = app(AuctionTimelineService::class);

        return [
            'id' => $this->id,
            'type' => $this->type->value,
            'occurred_at' => $this->occurred_at?->toISOString(),
            'actor_label' => $timeline->actorLabel($this->user),
            'is_mine' => $viewer && $this->user_id === $viewer->id,
            'is_system' => $this->type !== AuctionTimelineEntryType::BidPlaced,
            'message' => $timeline->messageForEntry($this->resource, $viewer),
            'meta' => $this->payload ?? [],
        ];
    }
}
