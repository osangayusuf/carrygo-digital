<?php

namespace App\Http\Controllers;

use App\Enums\ActivityType;
use App\Services\ActivityService;
use App\Services\RewardsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class SpinController extends Controller
{
    public function __invoke(Request $request, RewardsService $rewards, ActivityService $activity): JsonResponse
    {
        try {
            $result = $rewards->spinWheel($request->user());
        } catch (RuntimeException $exception) {
            return response()->json(['error' => $exception->getMessage()], 422);
        }

        $activity->log(
            ActivityType::SPIN_WHEEL,
            $request->user(),
            null,
            [
                'points_won' => $result['points_won'],
                'segment_index' => $result['segment_index'],
            ],
        );

        return response()->json($result);
    }
}
