<?php

namespace App\Listeners;

use App\Models\User;
use App\Notifications\AgentRegisteredNotification;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;

class NotifyAdminsOfAgentRegistration implements ShouldQueue
{
    /**
     * Handle the event.
     */
    public function handle(Registered $event): void
    {
        $user = $event->user;

        // Only notify if the registered user is an agent
        if ($user instanceof User && $user->isAgent()) {
            $admins = User::role('admin')->get();

            if ($admins->isNotEmpty()) {
                Notification::send($admins, new AgentRegisteredNotification($user));
            }
        }
    }
}
