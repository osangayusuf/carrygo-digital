<?php

namespace App\Listeners;

use App\Enums\UserRole;
use Illuminate\Auth\Events\Registered;

class AssignDefaultRole
{
    /**
     * Handle the event.
     */
    public function handle(Registered $event): void
    {
        $event->user->assignRole(UserRole::USER->value);
    }
}
