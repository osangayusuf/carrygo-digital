<?php

namespace App\Http\Resources;

use App\Enums\TransactionType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PointTransactionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $type = $this->type instanceof TransactionType ? $this->type : TransactionType::from((string) $this->type);

        return [
            'id' => $this->id,
            'type' => $type->value,
            'type_label' => $this->typeLabel($type),
            'direction' => $this->direction($type),
            'amount' => (float) $this->amount,
            'naira_amount' => $this->naira_amount !== null ? (float) $this->naira_amount : null,
            'exchange_rate' => (float) $this->exchange_rate,
            'status' => $this->status->value ?? (string) $this->status,
            'provider_reference' => $this->provider_reference,
            'auction_id' => $this->auctionId($type),
            'metadata' => $this->metadata,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }

    private function typeLabel(TransactionType $type): string
    {
        return match ($type) {
            TransactionType::DEPOSIT => 'Deposit',
            TransactionType::BID_DEBIT => 'Bid',
            TransactionType::BONUS_AWARD => 'Bonus Award',
            TransactionType::BONUS_CLAIM => 'Bonus Claim',
        };
    }

    private function direction(TransactionType $type): string
    {
        return match ($type) {
            TransactionType::BID_DEBIT => 'debit',
            default => 'credit',
        };
    }

    private function auctionId(TransactionType $type): ?int
    {
        if ($type !== TransactionType::BID_DEBIT) {
            return null;
        }

        $auctionId = $this->metadata['auction_id'] ?? null;

        return $auctionId !== null ? (int) $auctionId : null;
    }
}
