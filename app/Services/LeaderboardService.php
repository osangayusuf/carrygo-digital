<?php

namespace App\Services;

use App\Enums\AuctionStatus;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Auction;
use App\Models\Bid;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class LeaderboardService
{
    private const int TOP_BIDDERS_PER_AUCTION = 3;

    public const int AUCTION_SHOW_TOP_BIDDERS = 10;

    public function __construct(
        private readonly AuctionListingService $auctionListing,
    ) {}

    /**
     * @return list<array{msisdn: string, total_points: int}>
     */
    public function topBiddersForAuction(Auction $auction, int $limit = self::AUCTION_SHOW_TOP_BIDDERS): array
    {
        if ($limit < 1) {
            return [];
        }

        return $this->queryTopBiddersForAuction($auction->id, $limit);
    }

    /**
     * @return LengthAwarePaginator<int, array<string, mixed>>
     */
    public function paginateAuctionLeaderboards(?string $search = null, int $perPage = 12): LengthAwarePaginator
    {
        $query = Auction::query()
            ->whereIn('status', [AuctionStatus::ACTIVE, AuctionStatus::TRIGGERED])
            ->search($search)
            ->orderByDesc('bid_count')
            ->orderByDesc('created_at');

        $paginator = $query->paginate($perPage)->withQueryString();

        $topBiddersByAuction = $this->topBiddersForAuctions(
            $paginator->getCollection()->pluck('id')->all(),
        );

        $paginator->setCollection(
            $paginator->getCollection()->map(function (Auction $auction) use ($topBiddersByAuction): array {
                $mapped = $this->auctionListing->mapAuction($auction);
                $mapped['top_bidders'] = $topBiddersByAuction->get($auction->id, collect())->all();

                return $mapped;
            })
        );

        return $paginator;
    }

    /**
     * @param  list<int>  $auctionIds
     * @return Collection<int, Collection<int, array{msisdn: string, total_points: int}>>
     */
    private function topBiddersForAuctions(array $auctionIds): Collection
    {
        if ($auctionIds === []) {
            return collect();
        }

        $rows = Bid::query()
            ->join('users', 'users.id', '=', 'bids.user_id')
            ->whereIn('bids.auction_id', $auctionIds)
            ->groupBy('bids.auction_id', 'bids.user_id', 'users.phone')
            ->select([
                'bids.auction_id',
                'users.phone as msisdn',
            ])
            ->selectRaw('SUM(bids.amount) as total_points')
            ->orderByDesc('total_points')
            ->get();

        return $rows
            ->groupBy('auction_id')
            ->map(function (Collection $auctionRows): Collection {
                return $auctionRows
                    ->sortByDesc('total_points')
                    ->take(self::TOP_BIDDERS_PER_AUCTION)
                    ->values()
                    ->map(fn ($row): array => $this->mapBidderRow($row));
            });
    }

    /**
     * @return list<array{msisdn: string, total_points: int}>
     */
    private function queryTopBiddersForAuction(int $auctionId, int $limit): array
    {
        return Bid::query()
            ->join('users', 'users.id', '=', 'bids.user_id')
            ->where('bids.auction_id', $auctionId)
            ->groupBy('bids.user_id', 'users.phone')
            ->select(['users.phone as msisdn'])
            ->selectRaw('SUM(bids.amount) as total_points')
            ->orderByDesc('total_points')
            ->limit($limit)
            ->get()
            ->map(fn ($row): array => $this->mapBidderRow($row))
            ->all();
    }

    /**
     * @return list<array{rank: int, name: string, msisdn: string, total_bid_pts: int}>
     */
    public function currentWeekTopBidders(int $limit = 10): array
    {
        $weekStart = Carbon::now()->startOfWeek();

        $rows = DB::table('point_transactions')
            ->join('users', 'users.id', '=', 'point_transactions.user_id')
            ->where('point_transactions.type', TransactionType::BID_DEBIT->value)
            ->where('point_transactions.status', TransactionStatus::COMPLETED->value)
            ->where('point_transactions.created_at', '>=', $weekStart)
            ->groupBy('point_transactions.user_id', 'users.name', 'users.phone')
            ->select(['users.name', 'users.phone'])
            ->selectRaw('SUM(point_transactions.amount) as total_bid_pts')
            ->orderByDesc('total_bid_pts')
            ->limit($limit)
            ->get();

        return $rows->values()->map(function (object $row, int $index): array {
            $phone = (string) ($row->phone ?? '');
            $masked = strlen($phone) > 4
                ? substr($phone, 0, -4).'****'
                : '****';

            return [
                'rank' => $index + 1,
                'name' => (string) ($row->name ?? ''),
                'msisdn' => $masked,
                'total_bid_pts' => (int) $row->total_bid_pts,
            ];
        })->all();
    }

    /**
     * @return array{msisdn: string, total_points: int}
     */
    private function mapBidderRow(object $row): array
    {
        return [
            'msisdn' => (string) ($row->msisdn ?? ''),
            'total_points' => (int) $row->total_points,
        ];
    }
}
