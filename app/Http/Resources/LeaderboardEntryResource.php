<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LeaderboardEntryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'rank' => $this->rank ?? null,
            'user_name' => $this->user_name,
            'total_pts_spent' => $this->total_pts_spent,
            'wins_count' => $this->wins_count ?? 0,
        ];
    }
}
