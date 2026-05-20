<?php

namespace App\Notifications;

use App\Models\PointTransaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class BonusPointsClaimed extends Notification implements ShouldQueue
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
        $spendable = (int) $this->transaction->amount;
        $bonusClaimed = (int) ($this->transaction->metadata['bonus_claimed'] ?? 0);

        return [
            'type' => 'bonus_points_claimed',
            'title' => "Claimed {$spendable} spendable pts",
            'message' => "You converted {$bonusClaimed} bonus points into {$spendable} spendable points.",
            'icon' => 'account_balance_wallet',
            'url' => '/wallet',
            'amount' => $spendable,
            'bonus_claimed' => $bonusClaimed,
        ];
    }
}
