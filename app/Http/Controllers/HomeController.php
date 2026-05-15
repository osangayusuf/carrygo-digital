<?php

namespace App\Http\Controllers;

use App\Http\Resources\AuctionResource;
use App\Models\Auction;
use App\Models\Bid;
use Inertia\Inertia;

class HomeController extends Controller
{
    public function index()
    {
        return Inertia::render('Home/Index', [
            'stats' => [
                'winnersCount' => Auction::whereNotNull('winner_id')->count(),
                'totalBids' => Bid::count(),
            ],
            'categories' => Inertia::defer(function () {
                return Auction::whereIn('status', ['active', 'triggered'])
                    ->select('category')
                    ->distinct()
                    ->pluck('category');
            }),
            'liveAuctions' => Inertia::defer(function () {
                $auctions = Auction::whereIn('status', ['active', 'triggered'])
                    ->orderBy('created_at', 'desc')
                    ->paginate(20);
                
                return AuctionResource::collection($auctions);
            }),
        ]);
    }
}
