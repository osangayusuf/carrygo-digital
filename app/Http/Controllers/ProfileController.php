<?php

namespace App\Http\Controllers;

use App\Models\Auction;
use App\Services\LeaderboardService;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Show the user's profile settings page.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();

        $wonAuctions = Auction::query()
            ->where('winner_id', $user->id)
            ->where('status', 'closed')
            ->select(['id', 'name', 'image', 'price', 'updated_at'])
            ->latest('updated_at')
            ->get();

        return Inertia::render('Profile/Index', [
            'mustVerifyEmail' => $user instanceof MustVerifyEmail,
            'status' => $request->session()->get('status'),
            'wonAuctions' => $wonAuctions,
        ]);
    }

    /**
     * Show the user's weekly leaderboard standing.
     */
    public function leaderboard(LeaderboardService $leaderboardService): Response
    {
        $topBidders = $leaderboardService->currentWeekTopBidders(
            (int) config('rewards.weekly_leaderboard.top_ranks', 10),
        );

        return Inertia::render('Profile/Leaderboard', [
            'topBidders' => $topBidders,
        ]);
    }
}
