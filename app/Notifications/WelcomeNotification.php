<?php

namespace App\Notifications;

use App\Models\PointTransaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WelcomeNotification extends Notification implements ShouldQueue
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
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Welcome to Bidora! 🎉')
            ->greeting("Hello {$notifiable->name},")
            ->line('Thank you for verifying your email address.')
            ->line("To welcome you, we have credited {$this->transaction->amount} points to your wallet!")
            ->line('You can use these points to place bids on exciting auctions.')
            ->action('Start Bidding', url('/'))
            ->line('Happy bidding!');
    }

    /**
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'welcome_bonus',
            'title' => 'Welcome to Bidora! Your account is verified.',
            'url' => '/wallet',
            'amount' => $this->transaction->amount,
            'message' => "Welcome to Bidora! {$this->transaction->amount} welcome points have been credited to your account.",
        ];
    }
}
