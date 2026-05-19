<?php

namespace App\Http\Controllers;

use App\Http\Resources\AuctionTimelineEntryResource;
use App\Models\Auction;
use App\Services\AuctionTimelineService;
use Illuminate\Http\JsonResponse;

class AuctionTimelineController extends Controller
{
    public function __construct(private readonly AuctionTimelineService $timelineService) {}

    public function index(Auction $auction): JsonResponse
    {
        $entries = $this->timelineService->timelineForAuction($auction->id);

        return response()->json([
            'data' => AuctionTimelineEntryResource::collection($entries),
        ]);
    }
}
