<?php

namespace App\Listeners;

use App\Enums\ActivityType;
use App\Models\User;
use App\Services\ActivityService;
use Illuminate\Auth\Events\Failed;

class LogFailedLogin
{
    public function __construct(private readonly ActivityService $activityService) {}

    public function handle(Failed $event): void
    {
        /** @var User|null $user */
        $user = $event->user;

        $this->activityService->log(
            type: ActivityType::LOGIN_FAILED,
            user: $user,
            metadata: ['credentials' => array_keys($event->credentials)],
        );
    }
}
