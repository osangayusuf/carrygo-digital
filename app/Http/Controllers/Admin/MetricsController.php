<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AuctionStatus;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Http\Controllers\Controller;
use App\Models\Auction;
use App\Models\Bid;
use App\Models\PointTransaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MetricsController extends Controller
{
    /**
     * Display general platform metrics and point purchase analytics.
     */
    public function index(Request $request): Response
    {
        $month = $request->input('month');
        $startDateInput = $request->input('start_date');
        $endDateInput = $request->input('end_date');

        $startDate = null;
        $endDate = null;
        $filterMode = 'month';

        if ($startDateInput && $endDateInput) {
            $startDate = Carbon::parse($startDateInput)->startOfDay();
            $endDate = Carbon::parse($endDateInput)->endOfDay();
            $filterMode = 'custom';
        } elseif ($month) {
            $startDate = Carbon::parse($month.'-01')->startOfMonth()->startOfDay();
            $endDate = Carbon::parse($month.'-01')->endOfMonth()->endOfDay();
            $filterMode = 'month';
        } else {
            // Default to current month
            $startDate = Carbon::now()->startOfMonth()->startOfDay();
            $endDate = Carbon::now()->endOfMonth()->endOfDay();
            $month = Carbon::now()->format('Y-m');
            $filterMode = 'month';
        }

        // 1. Points Purchased (Total points bought and corresponding Naira revenue)
        $purchases = PointTransaction::query()
            ->where('type', TransactionType::DEPOSIT)
            ->where('status', TransactionStatus::COMPLETED)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('SUM(amount) as total_points, SUM(naira_amount) as total_naira')
            ->first();

        $totalPointsBought = (int) ($purchases->total_points ?? 0);
        $totalNairaSpent = (float) ($purchases->total_naira ?? 0);

        // 2. Bidding & Spend
        $totalBidsPlaced = Bid::whereBetween('created_at', [$startDate, $endDate])->count();
        $totalPointsSpent = (int) PointTransaction::query()
            ->where('type', TransactionType::BID_DEBIT)
            ->where('status', TransactionStatus::COMPLETED)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('amount');

        // Current active/biddable auctions (no date constraint, showing system status)
        $activeAuctionsCount = Auction::whereIn('status', [AuctionStatus::ACTIVE, AuctionStatus::TRIGGERED])->count();

        // 3. User Registrations
        $newUsersCount = User::role('user')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        // 4. Auction Activity (completed in period, average bids)
        $completedAuctionsCount = Auction::where('status', AuctionStatus::CLOSED)
            ->whereBetween('updated_at', [$startDate, $endDate])
            ->count();

        $completedAuctionsBidsSum = Auction::where('status', AuctionStatus::CLOSED)
            ->whereBetween('updated_at', [$startDate, $endDate])
            ->sum('bid_count');

        $avgBidsPerAuction = $completedAuctionsCount > 0
            ? round($completedAuctionsBidsSum / $completedAuctionsCount, 1)
            : 0;

        // Daily points purchased breakdown for visual dashboard representation
        $dailyPurchases = PointTransaction::query()
            ->where('type', TransactionType::DEPOSIT)
            ->where('status', TransactionStatus::COMPLETED)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('DATE(created_at) as date, SUM(amount) as points, SUM(naira_amount) as naira')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(fn ($item) => [
                'date' => Carbon::parse($item->date)->format('M d'),
                'points' => (int) $item->points,
                'naira' => (float) $item->naira,
            ]);

        // Top points purchasers in the selected period
        $topPurchasers = PointTransaction::query()
            ->where('type', TransactionType::DEPOSIT)
            ->where('status', TransactionStatus::COMPLETED)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('user_id, SUM(amount) as points, SUM(naira_amount) as naira')
            ->groupBy('user_id')
            ->with('user:id,name,email')
            ->orderByDesc('points')
            ->take(5)
            ->get()
            ->map(fn ($item) => [
                'user_name' => $item->user?->name ?? 'Unknown User',
                'user_email' => $item->user?->email ?? 'N/A',
                'points' => (int) $item->points,
                'naira' => (float) $item->naira,
            ]);

        return Inertia::render('Admin/Metrics/Index', [
            'metrics' => [
                'totalPointsBought' => $totalPointsBought,
                'totalNairaSpent' => $totalNairaSpent,
                'totalBidsPlaced' => $totalBidsPlaced,
                'totalPointsSpent' => $totalPointsSpent,
                'activeAuctionsCount' => $activeAuctionsCount,
                'completedAuctionsCount' => $completedAuctionsCount,
                'avgBidsPerAuction' => $avgBidsPerAuction,
                'newUsersCount' => $newUsersCount,
                'dailyPurchases' => $dailyPurchases,
                'topPurchasers' => $topPurchasers,
            ],
            'filters' => [
                'month' => $month,
                'start_date' => $startDateInput,
                'end_date' => $endDateInput,
                'filter_mode' => $filterMode,
            ],
        ]);
    }
}
