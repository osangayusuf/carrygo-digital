<?php

namespace App\Http\Controllers;

use App\Jobs\ForwardPaystackWebhookJob;
use App\Jobs\ProcessPaystackWebhookJob;
use App\Models\PaystackWebhookLog;
use App\Services\PaystackService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    /**
     * Handle incoming Paystack webhooks.
     *
     * Bidora and Fanscorner share a single Paystack account, which can only
     * be configured with one webhook URL -- this one. Events tagged
     * metadata.app=fanscorner (set by Fanscorner at transaction/initialize
     * time) are forwarded to Fanscorner's own webhook endpoint untouched;
     * everything else is processed locally as before.
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

        $rawBody = $request->getContent();
        $payload = $request->all();
        $event = $payload['event'] ?? null;
        $reference = $payload['data']['reference'] ?? null;
        $app = $payload['data']['metadata']['app'] ?? null;

        // 2. Determine which app this event belongs to. Anything unrecognized
        // falls through to Bidora's own processing (which already no-ops
        // safely when it can't match a user/transaction) rather than being
        // silently dropped.
        $destination = $app === 'fanscorner' ? 'fanscorner' : 'bidora';

        if ($app === null) {
            Log::warning('Paystack webhook missing metadata.app; routing to Bidora by default.', [
                'reference' => $reference,
                'event' => $event,
            ]);
        }

        // 3. Create Audit Log
        $log = PaystackWebhookLog::create([
            'event' => $event,
            'reference' => $reference,
            'destination' => $destination,
            'payload' => $payload,
            'raw_body' => $rawBody,
            'ip_address' => $request->ip(),
            'status' => 'pending',
        ]);

        // 4. Dispatch to the right job async
        if ($destination === 'fanscorner') {
            ForwardPaystackWebhookJob::dispatch($log->id, $request->header('x-paystack-signature'));
        } else {
            ProcessPaystackWebhookJob::dispatch($log->id);
        }

        // 5. Return 200 OK to acknowledge receipt
        return response()->json(['status' => 'success']);
    }
}
