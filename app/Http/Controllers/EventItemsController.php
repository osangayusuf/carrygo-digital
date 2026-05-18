<?php

namespace App\Http\Controllers;

use App\Services\AuctionListingService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EventItemsController extends Controller
{
    public function index(Request $request, AuctionListingService $listing)
    {
        $search = $request->filled('search')
            ? trim($request->string('search')->toString())
            : null;

        $sort = $request->filled('sort')
            ? trim($request->string('sort')->toString())
            : 'recent';

        $status = $request->filled('status')
            ? trim($request->string('status')->toString())
            : null;

        return Inertia::render('EventItems/Index', [
            'search' => $search,
            'sort' => $sort,
            'status' => $status,
            'userPoints' => auth()->user()?->points_balance,
            'bids' => $listing->paginateEventItems([
                'search' => $search,
                'sort' => $sort,
                'status' => $status,
            ]),
        ]);
    }
}
