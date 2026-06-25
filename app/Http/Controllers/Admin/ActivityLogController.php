<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ActivityType;
use App\Http\Controllers\Controller;
use App\Models\Bid;
use App\Models\User;
use App\Models\UserActivity;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ActivityLogController extends Controller
{
    /**
     * Display the platform user activity audit log.
     */
    public function index(Request $request): Response
    {
        $search = $request->filled('search')
            ? trim($request->string('search')->toString())
            : null;

        $typeFilter = $request->filled('type')
            ? $request->string('type')->toString()
            : null;

        $ipFilter = $request->filled('ip')
            ? trim($request->string('ip')->toString())
            : null;

        $query = UserActivity::query()
            ->with('user:id,name,email')
            ->orderBy('created_at', 'desc');

        if ($search) {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($typeFilter) {
            $query->where('type', $typeFilter);
        }

        if ($ipFilter) {
            $query->where('ip_address', $ipFilter);
        }

        $activities = $query->paginate(30)->withQueryString();

        // --- Suspicious Activity Alerts ---

        // 1. Shared IP Alerts: IPs with 3+ unique user accounts in the last 24 hours
        $sharedIps = UserActivity::query()
            ->whereNotNull('user_id')
            ->where('created_at', '>=', Carbon::now()->subHours(24))
            ->select('ip_address')
            ->groupBy('ip_address')
            ->havingRaw('COUNT(DISTINCT user_id) >= 3')
            ->pluck('ip_address');

        $sharedIpAlerts = [];
        foreach ($sharedIps as $ip) {
            $userIds = UserActivity::query()
                ->where('ip_address', $ip)
                ->whereNotNull('user_id')
                ->where('created_at', '>=', Carbon::now()->subHours(24))
                ->distinct()
                ->pluck('user_id');

            $users = User::whereIn('id', $userIds)->select('id', 'name', 'email', 'is_active')->get();

            $sharedIpAlerts[] = [
                'ip' => $ip,
                'users' => $users,
                'count' => $users->count(),
            ];
        }

        // 2. High Bid Frequency: 20+ bids in 1 minute by a single user
        $dbDriver = DB::connection()->getDriverName();
        $timeGroupSql = $dbDriver === 'sqlite'
            ? "strftime('%Y-%m-%d %H:%M', created_at)"
            : "DATE_FORMAT(created_at, '%Y-%m-%d %H:%i')";

        $highBidRateUsers = Bid::query()
            ->selectRaw("user_id, {$timeGroupSql} as minute, COUNT(*) as bid_count")
            ->groupBy('user_id', 'minute')
            ->havingRaw('COUNT(*) >= 20')
            ->orderByDesc('bid_count')
            ->get();

        $highBidAlerts = [];
        foreach ($highBidRateUsers as $row) {
            $user = User::select('id', 'name', 'email', 'is_active')->find($row->user_id);
            if ($user) {
                $highBidAlerts[] = [
                    'user' => $user,
                    'minute' => $row->minute,
                    'bid_count' => (int) $row->bid_count,
                ];
            }
        }

        return Inertia::render('Admin/ActivityLog/Index', [
            'activities' => $activities,
            'filters' => [
                'search' => $search,
                'type' => $typeFilter,
                'ip' => $ipFilter,
            ],
            'availableTypes' => array_column(ActivityType::cases(), 'value'),
            'alerts' => [
                'sharedIps' => $sharedIpAlerts,
                'highBidRate' => $highBidAlerts,
            ],
        ]);
    }
}
