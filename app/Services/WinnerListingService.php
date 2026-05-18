<?php

namespace App\Services;

use App\Enums\AuctionStatus;
use App\Models\Auction;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class WinnerListingService
{
    /**
     * @return LengthAwarePaginator<int, array<string, mixed>>
     */
    public function paginateWinners(?string $search = null, int $perPage = 20): LengthAwarePaginator
    {
        $query = Auction::query()
            ->where('status', AuctionStatus::CLOSED)
            ->whereNotNull('winner_id')
            ->with([
                'winner',
                'bids' => fn ($query) => $query->where('is_winning', true),
            ])
            ->withSum('bids as total_pts_bid', 'amount')
            ->orderByDesc('updated_at');

        if (filled($search)) {
            $like = '%'.addcslashes($search, '%_\\').'%';

            $query->where(function (Builder $q) use ($like): void {
                $q->where('name', 'like', $like)
                    ->orWhereHas('winner', function (Builder $winnerQuery) use ($like): void {
                        $winnerQuery->where('name', 'like', $like)
                            ->orWhere('phone', 'like', $like);
                    });
            });
        }

        return $query
            ->paginate($perPage)
            ->withQueryString()
            ->through(fn (Auction $auction) => $this->mapWinner($auction));
    }

    /**
     * @return array<string, mixed>
     */
    private function mapWinner(Auction $auction): array
    {
        $winningBid = $auction->bids->first();

        return [
            'id' => $auction->id,
            'msisdn' => $auction->winner?->phone ?? '',
            'winner_name' => $auction->winner?->name ?? 'Anonymous',
            'winning_pts' => (int) ($winningBid?->amount ?? 0),
            'total_pts_bid' => (int) ($auction->total_pts_bid ?? 0),
            'bid_count' => (int) $auction->bid_count,
            'created_at' => $auction->updated_at?->toISOString() ?? '',
            'bid' => [
                'id' => $auction->id,
                'name' => $auction->name,
                'image' => $auction->image,
                'url' => '/auctions/'.$auction->id,
                'price' => number_format((float) $auction->price, 2),
            ],
        ];
    }
}
