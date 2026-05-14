<?php

namespace App\Notifications;

use App\Models\PointTransaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentConfirmed extends Notification implements ShouldQueue
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
            ->subject("Payment Confirmed — {$this->transaction->amount} Points Credited")
            ->greeting('Payment received! ✅')
            ->line("₦{$this->transaction->naira_amount} has been successfully processed.")
            ->line("{$this->transaction->amount} points have been credited to your account.")
            ->line("Reference: {$this->transaction->provider_reference}")
            ->action('View Wallet', url('/wallet'));
    }

    /**
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'payment_confirmed',
            'amount' => $this->transaction->amount,
            'naira_amount' => $this->transaction->naira_amount,
            'reference' => $this->transaction->provider_reference,
            'message' => "₦{$this->transaction->naira_amount} deposited — {$this->transaction->amount} pts credited to your account.",
            'url' => '/wallet',
        ];
    }
}
