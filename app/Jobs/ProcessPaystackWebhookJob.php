<?php

namespace App\Jobs;

use App\Models\PaystackTransaction;
use App\Models\PaystackWebhookLog;
use App\Models\User;
use App\Services\WalletService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ProcessPaystackWebhookJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $paystackWebhookLogId
    ) {}

    /**
     * Execute the job.
     */
    public function handle(WalletService $walletService): void
    {
        $log = PaystackWebhookLog::find($this->paystackWebhookLogId);

        if (! $log || $log->status !== 'pending') {
            return;
        }

        try {
            $payload = $log->payload;
            $event = $log->event;

            if ($event === 'charge.success') {
                $data = $payload['data'] ?? [];
                $reference = $data['reference'] ?? null;
                $amountInKobo = $data['amount'] ?? 0;
                $nairaAmount = $amountInKobo / 100;

                // Find PaystackTransaction
                $paystackTransaction = PaystackTransaction::where('reference', $reference)->first();

                if ($paystackTransaction) {
                    if ($paystackTransaction->status === 'success') {
                        $log->update(['status' => 'ignored', 'error_message' => 'PaystackTransaction already success']);

                        return;
                    }

                    // Attempt to find user from transaction or payload
                    $user = $paystackTransaction->user;
                } else {
                    // Fallback to finding user via email or metadata if transaction is not recorded
                    $userId = $data['metadata']['user_id'] ?? null;
                    $email = $data['customer']['email'] ?? null;

                    if ($userId) {
                        $user = User::find($userId);
                    } elseif ($email) {
                        $user = User::where('email', $email)->first();
                    }

                    if (! $user) {
                        throw new \Exception("User not found for reference {$reference}");
                    }

                    // Create transaction retrospectively
                    $paystackTransaction = PaystackTransaction::create([
                        'user_id' => $user->id,
                        'reference' => $reference,
                        'amount' => $amountInKobo,
                        'currency' => $data['currency'] ?? 'NGN',
                        'channel' => $data['channel'] ?? null,
                        'status' => 'pending',
                    ]);
                }

                // Credit the user
                $walletService->processDeposit($user, $nairaAmount, $reference, $data, $paystackTransaction->id);

                // Update PaystackTransaction
                $paystackTransaction->update([
                    'status' => 'success',
                    'paid_at' => now(),
                ]);
            }

            $log->update(['status' => 'processed']);
        } catch (\Exception $e) {
            $log->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            Log::error("ProcessPaystackWebhookJob failed for log {$this->paystackWebhookLogId}: {$e->getMessage()}");
            throw $e; // Re-throw to fail the job in queue
        }
    }
}
