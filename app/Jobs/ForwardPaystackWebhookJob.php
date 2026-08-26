<?php

namespace App\Jobs;

use App\Models\PaystackWebhookLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ForwardPaystackWebhookJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 5;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $paystackWebhookLogId,
        public ?string $signature
    ) {}

    /**
     * Retry backoff (seconds) between attempts.
     */
    public function backoff(): array
    {
        return [10, 30, 60, 300, 900];
    }

    /**
     * Execute the job.
     *
     * Forwards the exact raw body and original Paystack signature header to
     * Fanscorner's webhook endpoint. Fanscorner independently re-verifies
     * that signature against the shared secret, so forwarding the untouched
     * bytes (rather than a re-encoded payload) is what keeps that check
     * valid on Fanscorner's end.
     */
    public function handle(): void
    {
        $log = PaystackWebhookLog::find($this->paystackWebhookLogId);

        if (! $log || $log->forward_status === 'forwarded') {
            return;
        }

        $url = config('services.paystack.fanscorner_webhook_url');

        if (! $url) {
            Log::error('FANSCORNER_PAYSTACK_WEBHOOK_URL is not configured; cannot forward webhook.', [
                'log_id' => $log->id,
            ]);

            $log->update(['forward_status' => 'failed', 'status' => 'failed', 'error_message' => 'FANSCORNER_PAYSTACK_WEBHOOK_URL is not configured']);

            return;
        }

        $log->increment('forward_attempts');

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'x-paystack-signature' => $this->signature,
        ])
            ->withBody($log->raw_body, 'application/json')
            ->timeout(10)
            ->post($url);

        if ($response->successful()) {
            $log->update([
                'forward_status' => 'forwarded',
                'forwarded_at' => now(),
                'status' => 'processed',
            ]);

            return;
        }

        Log::warning('Forwarding Paystack webhook to Fanscorner failed.', [
            'log_id' => $log->id,
            'status' => $response->status(),
            'body' => $response->body(),
        ]);

        $log->update(['forward_status' => 'failed']);

        // Let ShouldQueue retry via $tries/backoff(); on final failure this
        // lands the job in failed_jobs for manual replay via `failed()` below.
        throw new \RuntimeException("Fanscorner webhook forward failed with HTTP {$response->status()}");
    }

    /**
     * Handle a job failure after all retries are exhausted.
     */
    public function failed(\Throwable $e): void
    {
        $log = PaystackWebhookLog::find($this->paystackWebhookLogId);

        $log?->update([
            'forward_status' => 'failed',
            'status' => 'failed',
            'error_message' => $e->getMessage(),
        ]);

        Log::error("ForwardPaystackWebhookJob permanently failed for log {$this->paystackWebhookLogId}: {$e->getMessage()}");
    }
}
