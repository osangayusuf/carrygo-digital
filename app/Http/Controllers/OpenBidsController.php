<?php

namespace App\Http\Controllers;

use App\Services\AuctionListingService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OpenBidsController extends Controller
{
    public function index(Request $request, AuctionListingService $listing)
    {
        $search = $request->filled('search')
            ? trim($request->string('search')->toString())
            : null;

        $sort = $request->filled('sort')
            ? trim($request->string('sort')->toString())
            : 'ending_soon';

        return Inertia::render('OpenBids/Index', [
            'search' => $search,
            'sort' => $sort,
            'userPoints' => auth()->user()?->points_balance,
            'bids' => $listing->paginateOpenBids([
                'search' => $search,
                'sort' => $sort,
            ]),
        ]);
    }
}
