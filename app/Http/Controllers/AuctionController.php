<?php

namespace App\Http\Controllers;

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
        return Inertia::render('Auction/Show', [
            'auction' => $listing->mapAuction($auction),
            'topBidders' => $leaderboard->topBiddersForAuction($auction),
            'userPoints' => auth()->user()?->points_balance,
        ]);
    }
}
