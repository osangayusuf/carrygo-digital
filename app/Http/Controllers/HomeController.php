<?php

namespace App\Http\Controllers;

use App\Enums\AuctionStatus;
use App\Models\Auction;
use App\Models\Review;
use App\Services\AuctionListingService;
use App\Services\LeaderboardService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HomeController extends Controller
{
    public function __construct(private readonly AuctionListingService $auctionListing) {}

    public function index(Request $request, LeaderboardService $leaderboard)
    {
        $search = $request->filled('search')
            ? trim($request->string('search')->toString())
            : null;

        return Inertia::render('Home/Index', [
            'search' => $search,
            'userPoints' => auth()->user()?->points_balance,

            'winnerPopup' => $this->resolveWinnerPopup(),

            'eventPopupBid' => $this->resolveEventPopupBid(),

            'categories' => Inertia::defer(fn () => $this->categoriesQuery()
                ->pluck('category')
                ->filter()
                ->values()),

            'bids' => Inertia::defer(fn () => $this->activeAuctionsQuery($search)
                ->orderBy('created_at', 'desc')
                ->limit(4)
                ->get()
                ->map(fn (Auction $auction) => $this->auctionListing->mapAuction($auction))),

            'trendingBids' => Inertia::defer(fn () => $this->activeAuctionsQuery($search)
                ->orderBy('bid_count', 'desc')
                ->limit(4)
                ->get()
                ->map(fn (Auction $auction) => $this->auctionListing->mapAuction($auction))),

            'recentlyAddedBids' => Inertia::defer(fn () => $this->activeAuctionsQuery($search)
                ->orderBy('created_at', 'desc')
                ->limit(4)
                ->get()
                ->map(fn (Auction $auction) => $this->auctionListing->mapAuction($auction))),

            'openBids' => Inertia::defer(fn () => Auction::query()
                ->where('status', AuctionStatus::TRIGGERED)
                ->enabled()
                ->search($search)
                ->orderBy('expires_at', 'asc')
                ->limit(4)
                ->get()
                ->map(fn (Auction $auction) => $this->auctionListing->mapAuction($auction))),

            'luxuryBids' => Inertia::defer(fn () => $this->activeAuctionsQuery($search)
                ->orderBy('price', 'desc')
                ->limit(4)
                ->get()
                ->map(fn (Auction $auction) => $this->auctionListing->mapAuction($auction))),

            'featuredBids' => Inertia::defer(fn () => $this->activeAuctionsQuery($search)
                ->orderBy('price', 'desc')
                ->limit(4)
                ->get()
                ->map(fn (Auction $auction) => $this->auctionListing->mapAuction($auction))),

            'categoryBids' => Inertia::defer(fn () => $this->resolveCategoryBids($search)),

            'homeTopBidders' => Inertia::defer(fn () => $leaderboard->currentWeekTopBidders(5)),

            'winners' => Inertia::defer(function () {
                return Auction::where('status', AuctionStatus::CLOSED)
                    ->whereNotNull('winner_id')
                    ->enabled()
                    ->with('winner')
                    ->withSum(['bids as total_points' => fn ($q) => $q->whereColumn('bids.user_id', 'auctions.winner_id')], 'amount')
                    ->orderBy('updated_at', 'desc')
                    ->limit(10)
                    ->get()
                    ->map(fn (Auction $auction) => [
                        'id' => $auction->id,
                        'msisdn' => $auction->winner?->phone ?? '',
                        'total_points' => (int) ($auction->total_points ?? 0),
                        'bidid' => $auction->id,
                        'created_at' => $auction->updated_at?->toISOString(),
                        'bid' => [
                            'id' => $auction->id,
                            'name' => $auction->name,
                            'image' => $auction->image,
                            'url' => '/auctions/'.$auction->id,
                            'price' => number_format((float) $auction->price, 2),
                        ],
                    ]);
            }),

            'reviews' => Inertia::defer(function () {
                return Review::visible()
                    ->with(['user', 'auction'])
                    ->latest()
                    ->limit(12)
                    ->get()
                    ->map(fn (Review $review) => [
                        'id' => $review->id,
                        'user_id' => $review->user?->phone ?? (string) $review->user_id,
                        'rating' => (int) round($review->rating),
                        'comment' => $review->comment,
                        'social_platform' => $review->social_platform,
                        'social_handle' => $review->social_handle,
                        'bidid' => $review->auction_id,
                        'created_at' => $review->created_at?->toISOString(),
                        'bid' => $review->auction ? [
                            'id' => $review->auction->id,
                            'name' => $review->auction->name,
                            'image' => $review->auction->image,
                            'url' => '/auctions/'.$review->auction->id,
                            'winner_id' => $review->auction->winner_id,
                        ] : null,
                    ]);
            }),
        ]);
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
     * @return Builder<Auction>
     */
    private function categoriesQuery(?string $search = null): Builder
    {
        $query = Auction::query()->select('category')->distinct();

        if ($search) {
            $query->search($search);
        }

        return $query;
    }

    /**
     * @return array<string, list<array<string, mixed>>>
     */
    private function resolveCategoryBids(?string $search): array
    {
        $categories = $this->categoriesQuery($search)
            ->pluck('category')
            ->filter()
            ->values();

        if (blank($search)) {
            $categories = $categories->shuffle()->take(5);
        }

        $result = [];

        foreach ($categories as $category) {
            $result[$category] = $this->activeAuctionsQuery($search)
                ->where('category', $category)
                ->orderBy('created_at', 'desc')
                ->limit(4)
                ->get()
                ->map(fn (Auction $auction) => $this->auctionListing->mapAuction($auction))
                ->all();
        }

        return $result;
    }

    private function resolveWinnerPopup(): ?array
    {
        if (! config('promotions.winner_popup.enabled')) {
            return null;
        }

        $auctionId = config('promotions.winner_popup.winner_id');

        if (! $auctionId) {
            return null;
        }

        $auction = Auction::where('status', AuctionStatus::CLOSED)
            ->whereNotNull('winner_id')
            ->enabled()
            ->with('winner')
            ->withSum(['bids as total_points' => fn ($q) => $q->whereColumn('bids.user_id', 'auctions.winner_id')], 'amount')
            ->find($auctionId);

        if (! $auction) {
            return null;
        }

        return [
            'id' => $auction->id,
            'msisdn' => $auction->winner?->phone ?? '',
            'total_points' => (int) ($auction->total_points ?? 0),
            'bidid' => $auction->id,
            'created_at' => $auction->updated_at?->toISOString(),
            'bid' => [
                'id' => $auction->id,
                'name' => $auction->name,
                'image' => $auction->image,
                'url' => '/auctions/'.$auction->id,
                'price' => number_format((float) $auction->price, 2),
            ],
        ];
    }

    private function resolveEventPopupBid(): ?array
    {
        if (! config('promotions.event_popup.enabled')) {
            return null;
        }

        $auctionId = config('promotions.event_popup.auction_id');

        if (! $auctionId) {
            return null;
        }

        $auction = Auction::whereIn('status', [AuctionStatus::ACTIVE, AuctionStatus::TRIGGERED])
            ->enabled()
            ->find($auctionId);

        if (! $auction) {
            return null;
        }

        return $this->auctionListing->mapAuction($auction);
    }
}
