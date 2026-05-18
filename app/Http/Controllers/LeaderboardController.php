<?php

namespace App\Http\Controllers;

use App\Services\LeaderboardService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LeaderboardController extends Controller
{
    public function index(Request $request, LeaderboardService $listing)
    {
        $search = $request->filled('search')
            ? trim($request->string('search')->toString())
            : null;

        return Inertia::render('Leaderboard/Index', [
            'search' => $search,
            'auctions' => $listing->paginateAuctionLeaderboards($search),
        ]);
    }
}
