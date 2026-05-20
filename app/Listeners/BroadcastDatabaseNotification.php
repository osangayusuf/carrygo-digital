<?php

namespace App\Listeners;

use App\Events\NotificationCreated;
use App\Models\User;
use App\Services\NotificationFeedService;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\Events\NotificationSent;

class BroadcastDatabaseNotification
{
    public function __construct(
        private readonly NotificationFeedService $notificationFeed,
    ) {}

    public function handle(NotificationSent $event): void
    {
        if ($event->channel !== 'database') {
            return;
        }

        if (! $event->notifiable instanceof User) {
            return;
        }

        if (! $event->response instanceof DatabaseNotification) {
            return;
        }

        broadcast(new NotificationCreated(
            userId: $event->notifiable->getKey(),
            notification: $this->notificationFeed->map($event->response),
        ));
    }
}
