<?php

namespace App\Notifications;

use App\Models\PointTransaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class BonusPointsAwarded extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly PointTransaction $transaction)
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
        return [
            'type' => 'bonus_points_awarded',
            'title' => "{$this->transaction->amount} bonus pts have been added to your account.",
            'amount' => $this->transaction->amount,
            'message' => "{$this->transaction->amount} bonus pts have been added to your account.",
            'url' => '/wallet',
        ];
    }
}
