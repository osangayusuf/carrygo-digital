<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class WinnerAuctionResource extends AuctionResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return array_merge(parent::toArray($request), [
            'winner_name' => $this->whenLoaded('winner', fn () => $this->winner->name),
            'winning_pts' => $this->current_points,
            'total_pts_bid' => $this->total_pts_bid ?? 0, // This should be queried/added dynamically in controller if needed
            'won_at' => $this->expires_at?->toISOString(),
        ]);
    }
}
