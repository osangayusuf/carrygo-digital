<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Verified;

class MarkPostVerificationOnboarding
{
    /**
     * Handle the event.
     */
    public function handle(Verified $event): void
    {
        session(['post_verification_onboarding' => true]);
    }
}
