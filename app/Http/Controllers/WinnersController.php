<?php

namespace App\Http\Controllers;

use App\Services\WinnerListingService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WinnersController extends Controller
{
    public function index(Request $request, WinnerListingService $listing)
    {
        $search = $request->filled('search')
            ? trim($request->string('search')->toString())
            : null;

        return Inertia::render('Winners/Index', [
            'search' => $search,
            'winners' => $listing->paginateWinners($search),
        ]);
    }
}
