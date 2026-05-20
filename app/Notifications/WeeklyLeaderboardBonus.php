<?php

namespace App\Notifications;

use App\Models\PointTransaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class WeeklyLeaderboardBonus extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly PointTransaction $transaction,
        public readonly int $rank,
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
            'type' => 'weekly_leaderboard_bonus',
            'title' => "Weekly leaderboard #{$this->rank}: +{$amount} bonus pts",
            'message' => "You placed #{$this->rank} on the weekly bidding leaderboard and earned {$amount} bonus points.",
            'icon' => 'leaderboard',
            'url' => '/tasks',
            'amount' => $amount,
            'rank' => $this->rank,
        ];
    }
}
