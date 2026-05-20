<?php

namespace App\Http\Controllers;

use App\Enums\ActivityType;
use App\Services\ActivityService;
use App\Services\RewardsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class RewardClaimController extends Controller
{
    public function __invoke(Request $request, RewardsService $rewards, ActivityService $activity): RedirectResponse
    {
        try {
            $transaction = $rewards->claimAll($request->user());
        } catch (InvalidArgumentException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        $activity->log(
            ActivityType::REWARDS_CLAIMED,
            $request->user(),
            $transaction,
            [
                'bonus_claimed' => (int) ($transaction->metadata['bonus_claimed'] ?? 0),
                'spendable' => (int) $transaction->amount,
            ],
        );

        $spendable = (int) $transaction->amount;

        return back()->with('success', "Claimed {$spendable} spendable points!");
    }
}
