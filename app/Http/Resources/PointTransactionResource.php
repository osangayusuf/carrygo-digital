<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PointTransactionResource extends JsonResource
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
            'type' => $this->type,
            'amount' => $this->amount,
            'naira_amount' => $this->naira_amount,
            'exchange_rate' => $this->exchange_rate,
            'status' => $this->status,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
