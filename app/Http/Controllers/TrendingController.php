<?php

namespace App\Http\Controllers;

use App\Services\AuctionListingService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TrendingController extends Controller
{
    public function index(Request $request, AuctionListingService $listing)
    {
        $search = $request->filled('search')
            ? trim($request->string('search')->toString())
            : null;

        $category = $request->filled('category')
            ? trim($request->string('category')->toString())
            : null;

        $sort = $request->filled('sort')
            ? trim($request->string('sort')->toString())
            : 'recent';

        return Inertia::render('Trending/Index', [
            'search' => $search,
            'category' => $category,
            'sort' => $sort,
            'userPoints' => auth()->user()?->points_balance,
            'categories' => $listing->categoriesForListing(),
            'bids' => $listing->paginateActiveAuctions([
                'search' => $search,
                'category' => $category,
                'sort' => $sort,
            ]),
        ]);
    }
}
