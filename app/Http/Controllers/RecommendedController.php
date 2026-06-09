<?php

namespace App\Http\Controllers;

use App\Enums\AuctionStatus;
use App\Models\Auction;
use App\Services\AuctionListingService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RecommendedController extends Controller
{
    public function index(Request $request, AuctionListingService $listing)
    {
        $user = auth()->user();
        $categories = [];

        if ($user) {
            // Get categories from auctions the user has bid on
            $categories = $user->bids()
                ->join('auctions', 'bids.auction_id', '=', 'auctions.id')
                ->whereIn('auctions.status', [AuctionStatus::ACTIVE, AuctionStatus::TRIGGERED])
                ->distinct()
                ->pluck('auctions.category')
                ->filter()
                ->unique()
                ->values()
                ->all();
        }

        $query = Auction::query()
            ->whereIn('status', [AuctionStatus::ACTIVE, AuctionStatus::TRIGGERED]);

        if ($user && ! empty($categories)) {
            // Recommendation based on user's bid history
            $query->whereIn('category', $categories)
                ->orderBy('created_at', 'desc');
        } else {
            // Guest or fallback recommendation: sort by popularity and progress
            // (current_points * 1.0 / opening_points) calculates progress ratio
            $query->orderByRaw('bid_count DESC')
                ->orderByRaw('(current_points * 1.0 / opening_points) DESC');
        }

        // Handle optional search filter
        $search = $request->filled('search')
            ? trim($request->string('search')->toString())
            : null;

        if ($search) {
            $query->search($search);
        }

        $paginated = $query->paginate(20)
            ->withQueryString()
            ->through(fn (Auction $auction) => $listing->mapAuction($auction));

        return Inertia::render('Recommended/Index', [
            'search' => $search,
            'userPoints' => $user?->points_balance,
            'bids' => $paginated,
            'categories' => $listing->categoriesForListing(),
        ]);
    }
}
