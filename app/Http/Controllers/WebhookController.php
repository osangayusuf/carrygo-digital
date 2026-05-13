<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessPaystackWebhookJob;
use App\Models\PaystackWebhookLog;
use App\Services\PaystackService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    /**
     * Handle incoming Paystack webhooks.
     */
    public function handlePaystack(Request $request, PaystackService $paystackService)
    {
        // 1. Verify Signature
        if (! $paystackService->verifyWebhookSignature($request)) {
            Log::warning('Invalid Paystack webhook signature.', [
                'ip' => $request->ip(),
                'payload' => $request->all(),
            ]);

            abort(403, 'Invalid signature');
        }

        $payload = $request->all();
        $event = $payload['event'] ?? null;
        $reference = $payload['data']['reference'] ?? null;

        // 2. Create Audit Log
        $log = PaystackWebhookLog::create([
            'event' => $event,
            'reference' => $reference,
            'payload' => $payload,
            'ip_address' => $request->ip(),
            'status' => 'pending',
        ]);

        // 3. Dispatch Job to process async
        ProcessPaystackWebhookJob::dispatch($log->id);

        // 4. Return 200 OK to acknowledge receipt
        return response()->json(['status' => 'success']);
    }
}
