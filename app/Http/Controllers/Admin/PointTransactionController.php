<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PointTransaction;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PointTransactionController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->filled('search')
            ? trim($request->string('search')->toString())
            : null;

        $typeFilter = $request->filled('type')
            ? $request->string('type')->toString()
            : null;

        $query = PointTransaction::query()
            ->with('user')
            ->orderBy('id', 'desc');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', function ($uq) use ($search) {
                    $uq->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                })->orWhere('provider_reference', 'like', "%{$search}%");
            });
        }

        if ($typeFilter) {
            $query->where('type', $typeFilter);
        }

        $transactions = $query->paginate(20)->withQueryString();

        return Inertia::render('Admin/PointTransactions/Index', [
            'transactions' => $transactions,
            'filters' => [
                'search' => $search,
                'type' => $typeFilter,
            ],
            'availableTypes' => ['deposit', 'bid_debit', 'bonus_award', 'bonus_claim'],
        ]);
    }
}
