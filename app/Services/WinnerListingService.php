<?php

namespace App\Services;

use App\Enums\AuctionStatus;
use App\Models\Auction;
use App\Models\Bid;
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
            ->enabled()
            ->with([
                'winner',
                'reviews' => fn ($query) => $query->where('is_visible', true),
            ])
            ->withSum('bids as total_pts_bid', 'amount')
            ->withSum(['bids as winner_pts_total' => fn ($query) => $query->whereColumn('bids.user_id', 'auctions.winner_id')], 'amount')
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
     * Paginate previous auction winners for administrative review and auditing.
     *
     * @return LengthAwarePaginator<int, array<string, mixed>>
     */
    public function paginateAdminWinners(
        ?string $search = null,
        ?string $category = null,
        ?string $reviewStatus = null,
        int $perPage = 20
    ): LengthAwarePaginator {
        $query = Auction::query()
            ->where('status', AuctionStatus::CLOSED)
            ->whereNotNull('winner_id')
            ->with([
                'winner',
                'reviews',
            ])
            ->withSum('bids as total_pts_bid', 'amount')
            ->withSum(['bids as winner_pts_total' => fn ($q) => $q->whereColumn('bids.user_id', 'auctions.winner_id')], 'amount')
            ->orderByDesc('updated_at');

        if (filled($search)) {
            $like = '%'.addcslashes($search, '%_\\').'%';

            $query->where(function (Builder $q) use ($like): void {
                $q->where('name', 'like', $like)
                    ->orWhere('category', 'like', $like)
                    ->orWhereHas('winner', function (Builder $winnerQuery) use ($like): void {
                        $winnerQuery->where('name', 'like', $like)
                            ->orWhere('email', 'like', $like)
                            ->orWhere('phone', 'like', $like);
                    });
            });
        }

        if (filled($category)) {
            $query->where('category', $category);
        }

        if ($reviewStatus === 'with_review') {
            $query->whereHas('reviews', function (Builder $rq): void {
                $rq->whereColumn('user_id', 'auctions.winner_id');
            });
        } elseif ($reviewStatus === 'with_media') {
            $query->whereHas('reviews', function (Builder $rq): void {
                $rq->whereColumn('user_id', 'auctions.winner_id')
                    ->where(function (Builder $mq): void {
                        $mq->whereNotNull('photos')
                            ->orWhereNotNull('video');
                    });
            });
        } elseif ($reviewStatus === 'without_review') {
            $query->whereDoesntHave('reviews', function (Builder $rq): void {
                $rq->whereColumn('user_id', 'auctions.winner_id');
            });
        }

        return $query
            ->paginate($perPage)
            ->withQueryString()
            ->through(fn (Auction $auction) => $this->mapAdminWinner($auction));
    }

    /**
     * Compute overview statistics for the administrative winners dashboard.
     *
     * @return array<string, int|float>
     */
    public function getAdminWinnerStats(): array
    {
        $baseQuery = Auction::query()
            ->where('status', AuctionStatus::CLOSED)
            ->whereNotNull('winner_id');

        $totalWinners = (int) (clone $baseQuery)->count();
        $totalRetailValue = (float) (clone $baseQuery)->sum('price');

        $totalWinningPoints = (int) Bid::query()
            ->join('auctions', 'auctions.id', '=', 'bids.auction_id')
            ->where('auctions.status', AuctionStatus::CLOSED)
            ->whereNotNull('auctions.winner_id')
            ->whereColumn('bids.user_id', 'auctions.winner_id')
            ->sum('bids.amount');

        $totalReviews = (int) (clone $baseQuery)
            ->whereHas('reviews', fn ($q) => $q->whereColumn('user_id', 'auctions.winner_id'))
            ->count();

        return [
            'total_winners_count' => $totalWinners,
            'total_winning_points' => $totalWinningPoints,
            'total_retail_value' => $totalRetailValue,
            'total_reviews_count' => $totalReviews,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function mapWinner(Auction $auction): array
    {
        $winnerReview = $auction->reviews->first(fn ($r) => $r->user_id === $auction->winner_id);

        return [
            'id' => $auction->id,
            'msisdn' => $auction->winner?->phone ?? '',
            'winner_name' => $auction->winner?->name ?? 'Anonymous',
            'winning_pts' => (int) ($auction->winner_pts_total ?? 0),
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
            'review' => $winnerReview ? [
                'rating' => (float) $winnerReview->rating,
                'comment' => $winnerReview->comment,
                'social_platform' => $winnerReview->social_platform,
                'social_handle' => $winnerReview->social_handle,
                'photos' => $winnerReview->photos ? array_map(fn ($p) => asset('storage/'.$p), $winnerReview->photos) : null,
                'video' => $winnerReview->video ? asset('storage/'.$winnerReview->video) : null,
            ] : null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function mapAdminWinner(Auction $auction): array
    {
        $winnerReview = $auction->reviews->first(fn ($r) => $r->user_id === $auction->winner_id);

        return [
            'id' => $auction->id,
            'auction_name' => $auction->name,
            'category' => $auction->category,
            'price' => (float) $auction->price,
            'image' => $auction->image,
            'enabled' => (bool) $auction->enabled,
            'event' => (bool) $auction->event,
            'bid_count' => (int) $auction->bid_count,
            'winning_pts' => (int) ($auction->winner_pts_total ?? 0),
            'total_pts_bid' => (int) ($auction->total_pts_bid ?? 0),
            'closed_at' => $auction->updated_at?->toISOString() ?? '',
            'winner' => $auction->winner ? [
                'id' => $auction->winner->id,
                'name' => $auction->winner->name,
                'email' => $auction->winner->email,
                'phone' => $auction->winner->phone,
            ] : null,
            'review' => $winnerReview ? [
                'id' => $winnerReview->id,
                'rating' => (float) $winnerReview->rating,
                'comment' => $winnerReview->comment,
                'social_platform' => $winnerReview->social_platform,
                'social_handle' => $winnerReview->social_handle,
                'is_visible' => (bool) $winnerReview->is_visible,
                'photos' => $winnerReview->photos ? array_map(fn ($p) => asset('storage/'.$p), $winnerReview->photos) : null,
                'video' => $winnerReview->video ? asset('storage/'.$winnerReview->video) : null,
                'created_at' => $winnerReview->created_at?->toISOString() ?? '',
            ] : null,
        ];
    }
}
