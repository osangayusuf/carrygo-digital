<?php

namespace App\Http\Controllers;

use App\Http\Requests\PlaceBidRequest;
use App\Models\Auction;
use App\Services\BiddingService;
use Illuminate\Http\JsonResponse;

class BidController extends Controller
{
    public function __construct(private readonly BiddingService $biddingService) {}

    public function store(PlaceBidRequest $request, Auction $auction): JsonResponse
    {
        $bid = $this->biddingService->placeBid(
            user: $request->user(),
            auction: $auction,
            amount: $request->validated('amount'),
        );

        return response()->json([
            'bid' => $bid,
            'auction' => $auction->refresh()->only([
                'id', 'status', 'current_points', 'bid_count', 'expires_at', 'winner_id',
            ]),
        ], 201);
    }
}
