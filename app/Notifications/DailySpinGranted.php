<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class DailySpinGranted extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly int $spinsGranted)
    {
        $this->afterCommit = true;
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        $label = $this->spinsGranted === 1 ? '1 free spin' : "{$this->spinsGranted} free spins";

        return [
            'type' => 'daily_spin_granted',
            'title' => "Daily spin: {$label}",
            'message' => "You received {$label}. Head to the Task Center to use them.",
            'icon' => 'casino',
            'url' => '/tasks',
            'spins_granted' => $this->spinsGranted,
        ];
    }
}
