<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bid;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BidController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->filled('search')
            ? trim($request->string('search')->toString())
            : null;

        $query = Bid::query()
            ->with(['user', 'auction'])
            ->orderBy('id', 'desc');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', function ($uq) use ($search) {
                    $uq->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                })->orWhereHas('auction', function ($aq) use ($search) {
                    $aq->where('name', 'like', "%{$search}%");
                });
            });
        }

        $bids = $query->paginate(20)->withQueryString();

        return Inertia::render('Admin/Bids/Index', [
            'bids' => $bids,
            'filters' => [
                'search' => $search,
            ],
        ]);
    }
}
