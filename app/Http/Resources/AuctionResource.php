<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuctionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'category' => $this->category,
            'image' => $this->image,
            'status' => $this->status,
            'event' => $this->event,
            'current_points' => $this->current_points,
            'bid_count' => $this->bid_count,
            'opening_points' => $this->opening_points,
            'expires_at' => $this->expires_at?->toISOString(),
            'winner_id' => $this->winner_id,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
