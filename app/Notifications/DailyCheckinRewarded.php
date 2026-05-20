<?php

namespace App\Notifications;

use App\Models\PointTransaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class DailyCheckinRewarded extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly PointTransaction $transaction,
        public readonly int $streak,
    ) {
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
        $amount = (int) $this->transaction->amount;

        return [
            'type' => 'daily_checkin_rewarded',
            'title' => "Day {$this->streak} check-in: +{$amount} bonus pts",
            'message' => "You checked in and earned {$amount} bonus points. Claim them in the Task Center.",
            'icon' => 'event_available',
            'url' => '/tasks',
            'amount' => $amount,
            'streak' => $this->streak,
        ];
    }
}
