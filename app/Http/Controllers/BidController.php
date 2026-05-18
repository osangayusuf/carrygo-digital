<?php

namespace App\Http\Controllers;

use App\Http\Requests\PlaceBidRequest;
use App\Models\Auction;
use App\Services\BiddingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use InvalidArgumentException;

class BidController extends Controller
{
    public function __construct(private readonly BiddingService $biddingService) {}

    public function store(PlaceBidRequest $request, Auction $auction): RedirectResponse
    {
        try {
            $this->biddingService->placeBid(
                user: $request->user(),
                auction: $auction,
                points: $request->validated('points'),
            );
        } catch (InvalidArgumentException $exception) {
            throw ValidationException::withMessages([
                'points' => $exception->getMessage(),
            ]);
        }

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Bid placed successfully!',
        ]);

        return back();
    }
}
