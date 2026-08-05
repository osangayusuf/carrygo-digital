<?php

namespace App\Http\Controllers;

use App\Enums\AuctionStatus;
use App\Models\Auction;
use App\Services\AuctionListingService;
use App\Services\LeaderboardService;
use Inertia\Inertia;

class AuctionController extends Controller
{
    public function show(
        Auction $auction,
        AuctionListingService $listing,
        LeaderboardService $leaderboard,
    ) {
        $user = auth()->user();

        abort_if(! $auction->enabled && ! $user?->hasRole('admin'), 404);

        $hasBid = $user ? $auction->bids()->where('user_id', $user->id)->exists() : false;
        $hasReviewed = $user ? $auction->reviews()->where('user_id', $user->id)->exists() : false;
        $isWinner = $user ? ($auction->winner_id === $user->id) : false;
        $canReview = $hasBid && ! $hasReviewed && $auction->status === AuctionStatus::CLOSED;

        $reviews = $auction->reviews()
            ->visible()
            ->with('user:id,name')
            ->latest()
            ->get()
            ->map(fn ($review) => [
                'id' => $review->id,
                'rating' => (float) $review->rating,
                'comment' => $review->comment,
                'social_platform' => $review->social_platform,
                'social_handle' => $review->social_handle,
                'photos' => $review->photos ? array_map(fn ($p) => asset('storage/'.$p), $review->photos) : null,
                'video' => $review->video ? asset('storage/'.$review->video) : null,
                'created_at' => $review->created_at->toISOString(),
                'user_name' => $review->user?->name ?? 'Anonymous',
            ]);

        return Inertia::render('Auction/Show', [
            'auction' => $listing->mapAuction($auction),
            'topBidders' => $leaderboard->topBiddersForAuction($auction),
            'userPoints' => $user?->points_balance,
            'reviews' => $reviews,
            'userReviewState' => [
                'canReview' => $canReview,
                'isWinner' => $isWinner,
                'hasReviewed' => $hasReviewed,
            ],
        ]);
    }
}
