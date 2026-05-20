<?php

namespace App\Http\Controllers;

use App\Enums\ActivityType;
use App\Services\ActivityService;
use App\Services\RewardsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CheckinController extends Controller
{
    public function store(Request $request, RewardsService $rewards, ActivityService $activity): RedirectResponse
    {
        $result = $rewards->processCheckin($request->user());

        if (! $result['success']) {
            return back()->with('error', $result['message']);
        }

        $activity->log(
            ActivityType::DAILY_CHECK_IN,
            $request->user(),
            null,
            ['streak' => $result['streak'], 'points' => (int) $result['transaction']->amount],
        );

        return back()->with('success', $result['message']);
    }
}
