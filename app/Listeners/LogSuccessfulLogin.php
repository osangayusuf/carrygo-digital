<?php

namespace App\Listeners;

use App\Enums\ActivityType;
use App\Models\User;
use App\Services\ActivityService;
use Illuminate\Auth\Events\Login;

class LogSuccessfulLogin
{
    public function __construct(private readonly ActivityService $activityService) {}

    public function handle(Login $event): void
    {
        /** @var User $user */
        $user = $event->user;

        if (empty($user->referral_code)) {
            $user->referral_code = User::generateUniqueReferralCode();
            $user->save();
        }

        $this->activityService->log(ActivityType::LOGIN_SUCCESS, $user);
    }
}
