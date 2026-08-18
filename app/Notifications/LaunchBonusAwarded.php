<?php

namespace App\Notifications;

use App\Models\PointTransaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LaunchBonusAwarded extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly PointTransaction $transaction,
        public readonly int $slot,
    ) {
        $this->afterCommit = true;
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("You're one of our first 100 users! 🎉")
            ->greeting("Hello {$notifiable->name},")
            ->line("You're launch user #{$this->slot} on Bidora!")
            ->line("As a thank-you, we've credited {$this->transaction->amount} bonus points to your wallet.")
            ->action('Start Bidding', url('/'))
            ->line('Welcome aboard, and happy bidding!');
    }

    /**
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'launch_bonus',
            'title' => "You're one of our first 100 users!",
            'url' => '/wallet',
            'amount' => $this->transaction->amount,
            'message' => "As launch user #{$this->slot}, {$this->transaction->amount} bonus points have been credited to your account.",
        ];
    }
}
