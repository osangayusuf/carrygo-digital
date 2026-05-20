<?php

namespace App\Notifications;

use App\Models\PointTransaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class AchievementUnlocked extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly PointTransaction $transaction,
        public readonly string $achievementLabel,
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
            'type' => 'achievement_unlocked',
            'title' => "Achievement unlocked: {$this->achievementLabel}",
            'message' => "You earned {$amount} bonus points for completing \"{$this->achievementLabel}\".",
            'icon' => 'military_tech',
            'url' => '/tasks',
            'amount' => $amount,
            'achievement_label' => $this->achievementLabel,
        ];
    }
}
