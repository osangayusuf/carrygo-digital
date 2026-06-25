<?php

namespace App\Listeners;

use App\Enums\ActivityType;
use App\Services\ActivityService;
use Illuminate\Auth\Events\Registered;

class LogUserRegistration
{
    /**
     * Create the event listener.
     */
    public function __construct(
        private readonly ActivityService $activityService
    ) {}

    /**
     * Handle the event.
     */
    public function handle(Registered $event): void
    {
        $this->activityService->log(
            type: ActivityType::USER_REGISTERED,
            user: $event->user
        );
    }
}
