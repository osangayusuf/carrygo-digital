<?php

namespace App\Http\Controllers;

use App\Enums\AuctionStatus;
use App\Models\Auction;
use App\Models\Review;
use Inertia\Inertia;

class HomeController extends Controller
{
    public function index()
    {
        return Inertia::render('Home/Index', [
            'userPoints' => auth()->user()?->points_balance,

            'winnerPopup' => $this->resolveWinnerPopup(),

            'eventPopupBid' => $this->resolveEventPopupBid(),

            'categories' => Inertia::defer(function () {
                return Auction::whereIn('status', [AuctionStatus::ACTIVE, AuctionStatus::TRIGGERED])
                    ->select('category')
                    ->distinct()
                    ->pluck('category')
                    ->filter()
                    ->values();
            }),

            'bids' => Inertia::defer(function () {
                return Auction::whereIn('status', [AuctionStatus::ACTIVE, AuctionStatus::TRIGGERED])
                    ->orderBy('created_at', 'desc')
                    ->limit(8)
                    ->get()
                    ->map(fn (Auction $auction) => $this->mapAuction($auction));
            }),

            'trendingBids' => Inertia::defer(function () {
                return Auction::whereIn('status', [AuctionStatus::ACTIVE, AuctionStatus::TRIGGERED])
                    ->orderBy('bid_count', 'desc')
                    ->limit(10)
                    ->get()
                    ->map(fn (Auction $auction) => $this->mapAuction($auction));
            }),

            'recentlyAddedBids' => Inertia::defer(function () {
                return Auction::whereIn('status', [AuctionStatus::ACTIVE, AuctionStatus::TRIGGERED])
                    ->orderBy('created_at', 'desc')
                    ->limit(10)
                    ->get()
                    ->map(fn (Auction $auction) => $this->mapAuction($auction));
            }),

            'openBids' => Inertia::defer(function () {
                return Auction::where('status', AuctionStatus::TRIGGERED)
                    ->orderBy('expires_at', 'asc')
                    ->limit(4)
                    ->get()
                    ->map(fn (Auction $auction) => $this->mapAuction($auction));
            }),

            'luxuryBids' => Inertia::defer(function () {
                return Auction::whereIn('status', [AuctionStatus::ACTIVE, AuctionStatus::TRIGGERED])
                    ->orderBy('price', 'desc')
                    ->limit(10)
                    ->get()
                    ->map(fn (Auction $auction) => $this->mapAuction($auction));
            }),

            'categoryBids' => Inertia::defer(function () {
                $categories = Auction::whereIn('status', [AuctionStatus::ACTIVE, AuctionStatus::TRIGGERED])
                    ->select('category')
                    ->distinct()
                    ->pluck('category')
                    ->filter()
                    ->values();

                $selected = $categories->shuffle()->take(5);

                $result = [];

                foreach ($selected as $category) {
                    $result[$category] = Auction::whereIn('status', [AuctionStatus::ACTIVE, AuctionStatus::TRIGGERED])
                        ->where('category', $category)
                        ->orderBy('created_at', 'desc')
                        ->limit(10)
                        ->get()
                        ->map(fn (Auction $auction) => $this->mapAuction($auction));
                }

                return $result;
            }),

            'winners' => Inertia::defer(function () {
                return Auction::where('status', AuctionStatus::CLOSED)
                    ->whereNotNull('winner_id')
                    ->with('winner')
                    ->withSum('bids as total_points', 'amount')
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
                return Review::with(['user', 'auction'])
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
                        ] : null,
                    ]);
            }),
        ]);
    }

    private function mapAuction(Auction $auction): array
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
            'url' => '/auctions/'.$auction->id,
            'price' => number_format((float) $auction->price, 2),
            'opening_points' => $auction->opening_points,
            'rating' => null,
            'open_date' => $auction->created_at?->timestamp ?? 0,
            'status' => $status,
            'created_at' => $auction->created_at?->toISOString() ?? '',
            'current_points' => $auction->current_points,
            'expires_at' => $auction->expires_at?->toISOString(),
        ];
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
            ->with('winner')
            ->withSum('bids as total_points', 'amount')
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
            ->find($auctionId);

        if (! $auction) {
            return null;
        }

        return $this->mapAuction($auction);
    }
}
