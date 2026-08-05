<?php

namespace App\Services;

use App\Enums\AuctionStatus;
use App\Models\Auction;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class AuctionListingService
{
    /**
     * @param  array{search?: string|null, category?: string|null, sort?: string|null}  $filters
     * @return LengthAwarePaginator<int, array<string, mixed>>
     */
    public function paginateActiveAuctions(array $filters, int $perPage = 20): LengthAwarePaginator
    {
        $query = $this->activeAuctionsQuery($filters['search'] ?? null);

        if (! empty($filters['category'])) {
            $query->where('category', $filters['category']);
        }

        $this->applySort($query, $filters['sort'] ?? 'recent');

        return $query
            ->paginate($perPage)
            ->withQueryString()
            ->through(fn (Auction $auction) => $this->mapAuction($auction));
    }

    /**
     * @param  array{search?: string|null, sort?: string|null}  $filters
     * @return LengthAwarePaginator<int, array<string, mixed>>
     */
    public function paginateOpenBids(array $filters, int $perPage = 20): LengthAwarePaginator
    {
        $query = Auction::query()
            ->where('status', AuctionStatus::TRIGGERED)
            ->enabled()
            ->search($filters['search'] ?? null);

        $this->applyOpenBidsSort($query, $filters['sort'] ?? 'ending_soon');

        return $query
            ->paginate($perPage)
            ->withQueryString()
            ->through(fn (Auction $auction) => $this->mapAuction($auction));
    }

    /**
     * @param  array{search?: string|null, sort?: string|null, status?: string|null}  $filters
     * @return LengthAwarePaginator<int, array<string, mixed>>
     */
    public function paginateEventItems(array $filters, int $perPage = 20): LengthAwarePaginator
    {
        $query = Auction::query()
            ->where('event', true)
            ->whereIn('status', [AuctionStatus::ACTIVE, AuctionStatus::TRIGGERED])
            ->enabled()
            ->search($filters['search'] ?? null);

        if (! empty($filters['status'])) {
            match ($filters['status']) {
                'live' => $query->where('status', AuctionStatus::TRIGGERED),
                'upcoming' => $query->where('status', AuctionStatus::ACTIVE),
                default => null,
            };
        }

        $this->applySort($query, $filters['sort'] ?? 'recent');

        return $query
            ->paginate($perPage)
            ->withQueryString()
            ->through(fn (Auction $auction) => $this->mapAuction($auction));
    }

    /**
     * @return list<string>
     */
    public function categoriesForListing(?string $search = null): array
    {
        return $this->activeAuctionsQuery($search)
            ->select('category')
            ->distinct()
            ->pluck('category')
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    public function mapAuction(Auction $auction): array
    {
        $status = match ($auction->status) {
            AuctionStatus::TRIGGERED => 1,
            AuctionStatus::CLOSED => 2,
            default => 0,
        };

        return [
            'id' => $auction->id,
            'name' => $auction->name,
            'image' => $auction->image,
            'description' => $auction->description,
            'url' => '/auctions/'.$auction->id,
            'price' => number_format((float) $auction->price, 2),
            'opening_points' => $auction->opening_points,
            'rating' => null,
            'open_date' => $auction->created_at?->timestamp ?? 0,
            'status' => $status,
            'created_at' => $auction->created_at?->toISOString() ?? '',
            'current_points' => $auction->current_points,
            'expires_at' => $auction->expires_at?->toISOString(),
            'bid_count' => $auction->bid_count,
            'winner_id' => $auction->winner_id,
            'external_url' => $auction->external_url,
        ];
    }

    /**
     * @return Builder<Auction>
     */
    private function activeAuctionsQuery(?string $search): Builder
    {
        return Auction::query()
            ->whereIn('status', [AuctionStatus::ACTIVE, AuctionStatus::TRIGGERED])
            ->enabled()
            ->search($search);
    }

    /**
     * @param  Builder<Auction>  $query
     */
    private function applySort(Builder $query, string $sort): void
    {
        match ($sort) {
            'closing_soon' => $query->orderByRaw('case when status = ? then 0 else 1 end', [AuctionStatus::TRIGGERED->value])
                ->orderBy('expires_at', 'asc')
                ->orderBy('updated_at', 'desc'),
            'price', 'value_desc' => $query->orderBy('price', 'desc'),
            'popular' => $query->orderBy('bid_count', 'desc'),
            'new' => $query->orderBy('created_at', 'desc'),
            default => $query->orderBy('updated_at', 'desc'),
        };
    }

    /**
     * @param  Builder<Auction>  $query
     */
    private function applyOpenBidsSort(Builder $query, string $sort): void
    {
        match ($sort) {
            'recent' => $query->orderBy('created_at', 'desc'),
            'popular' => $query->orderBy('bid_count', 'desc'),
            'price', 'value_desc' => $query->orderBy('price', 'desc'),
            default => $query->orderBy('expires_at', 'asc'),
        };
    }
}
