<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Auction;
use App\Services\WinnerListingService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class WinnerController extends Controller
{
    public function __construct(private readonly WinnerListingService $winnerListingService) {}

    public function index(Request $request): Response
    {
        $search = $request->filled('search')
            ? trim($request->string('search')->toString())
            : null;

        $categoryFilter = $request->filled('category')
            ? $request->string('category')->toString()
            : null;

        $reviewStatusFilter = $request->filled('review_status')
            ? $request->string('review_status')->toString()
            : null;

        $winners = $this->winnerListingService->paginateAdminWinners(
            search: $search,
            category: $categoryFilter,
            reviewStatus: $reviewStatusFilter,
            perPage: 20
        );

        $stats = $this->winnerListingService->getAdminWinnerStats();

        $categories = Auction::distinct()
            ->orderBy('category')
            ->pluck('category')
            ->filter()
            ->values()
            ->all();

        return Inertia::render('Admin/Winners/Index', [
            'winners' => $winners,
            'stats' => $stats,
            'categories' => $categories,
            'filters' => [
                'search' => $search,
                'category' => $categoryFilter,
                'review_status' => $reviewStatusFilter,
            ],
        ]);
    }
}
