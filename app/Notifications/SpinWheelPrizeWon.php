<?php

namespace App\Notifications;

use App\Models\PointTransaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class SpinWheelPrizeWon extends Notification implements ShouldQueue
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
        $amount = (int) $this->transaction->amount;

        return [
            'type' => 'spin_wheel_prize_won',
            'title' => "Spin wheel: +{$amount} bonus pts",
            'message' => "You won {$amount} bonus points on the spin wheel. Claim them when ready.",
            'icon' => 'casino',
            'url' => '/tasks',
            'amount' => $amount,
        ];
    }
}
