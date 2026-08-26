# Implementation Plan: Shared Paystack Account — Webhook Forwarding (Bidora → Fanscorner)

## Goal

One Paystack account is shared by two independently-deployed apps:

- **Bidora** (`app/Http/Controllers/WebhookController.php`) — Laravel, wallet top-ups.
- **Fanscorner** (`app/controllers/PaystackController.php`) — legacy PHP MVC, points purchases.

A Paystack account can only be configured with a single webhook URL. That URL will point at
**Bidora**. Bidora must inspect every incoming event, keep the ones that are its own, and
forward the rest to Fanscorner's existing webhook endpoint, byte-for-byte, so Fanscorner can
independently verify and process them exactly as it does today.

Decisions locked in for this plan (confirmed with the user):

1. **Routing signal:** an explicit `metadata.app` field (`"bidora"` or `"fanscorner"`) set by
   each app at `transaction/initialize` time, echoed back by Paystack on every subsequent event
   for that reference.
2. **Forward target:** Fanscorner's existing public `/paystack/webhook` endpoint — no new
   internal endpoint. It already re-verifies `X-Paystack-Signature` against the shared secret,
   so forwarding introduces no new trust surface as long as we forward the **raw, untouched**
   request body and the **original** signature header (do not re-serialize the JSON).
3. **Delivery guarantee:** ack Paystack immediately, then forward asynchronously via a queued
   job with retry/backoff (mirrors the existing `ProcessPaystackWebhookJob` pattern).
4. **Unrouted events** (no recognizable `metadata.app`): treated as Bidora's own — falls
   through to the existing `ProcessPaystackWebhookJob`, which already no-ops safely when it
   can't match a user/transaction — plus a logged warning so it's visible, not silent.

---

## Why raw-body forwarding matters

Fanscorner computes `hash_hmac('sha512', $raw_body, PAYSTACK_SECRET_KEY)` over the *exact*
bytes Paystack sent. If Bidora decodes the JSON (`$request->all()`) and re-encodes it before
forwarding, key order / whitespace / float formatting can change and the signature Fanscorner
recomputes will no longer match — Fanscorner would reject a legitimate event. So Bidora must
capture and forward `$request->getContent()` verbatim, plus the original
`X-Paystack-Signature` header, not a recomputed one.

---

## Changes — Fanscorner

### 1. Tag transactions with `metadata.app` at initialize time

**File:** [PaystackController.php](file:///Users/osanga/Herd/fanscorner/app/controllers/PaystackController.php)

In `initialize()`, add a `metadata` block to `$paystack_payload`:

```php
$paystack_payload = [
    "email" => $email,
    "amount" => (int)$package['amount_kobo'],
    "reference" => $reference,
    "callback_url" => PAYSTACK_CALLBACK_URL,
    "metadata" => [
        "app" => "fanscorner",
        "user_id" => $phoneNumber,
    ],
];
```

No other change needed in `webhook()` — it keeps looking up `payment_transactions` by
`reference` exactly as today, and stays independently signature-verified regardless of whether
the request arrived directly from Paystack or via Bidora's forward.

### 2. Move `PAYSTACK_SECRET_KEY` out of committed `config.php`

**File:** [config.php:112](file:///Users/osanga/Herd/fanscorner/config.php:112)

This is currently a hardcoded, committed test secret. Since this secret is about to become the
single shared credential both apps rely on for signature verification, it should not live in
source control. Recommend loading it from an environment variable (mirroring how Bidora already
does this via `PAYSTACK_SECRET_KEY`/`PAYSTACK_PUBLIC_KEY` in `.env`), and rotating the key in the
Paystack dashboard once it's out of git history. This is a prerequisite, not optional, once the
key is shared infrastructure between two apps.

---

## Changes — Bidora

### 3. Tag transactions with `metadata.app` at initialize time

**File:** [PaystackService.php](app/Services/PaystackService.php)

In `initializeTransaction()`, extend the `metadata` array already being sent:

```php
'metadata' => [
    'app' => 'bidora',
    'user_id' => $user->id,
],
```

### 4. Add Fanscorner's webhook URL to config

**Files:** [config/services.php](config/services.php), `.env` / `.env.example`

```php
'paystack' => [
    'public' => env('PAYSTACK_PUBLIC_KEY'),
    'secret' => env('PAYSTACK_SECRET_KEY'),
    'fanscorner_webhook_url' => env('FANSCORNER_PAYSTACK_WEBHOOK_URL'),
],
```

Add `FANSCORNER_PAYSTACK_WEBHOOK_URL=` to `.env.example` with a comment, and set the real value
(e.g. `https://<fanscorner-domain>/paystack/webhook`) in each environment's `.env`.

### 5. Extend `PaystackWebhookLog` to track routing + raw body

**New migration**, adding to the existing `paystack_webhook_logs` table:

- `destination` (string, nullable) — `bidora` | `fanscorner` | `unrouted`
- `raw_body` (longtext) — the exact bytes received, needed to forward without re-encoding
- `forward_status` (string, nullable) — `pending` | `forwarded` | `failed`
- `forward_attempts` (unsigned int, default 0)
- `forwarded_at` (timestamp, nullable)

`app/Models/PaystackWebhookLog.php` — add the new columns to `$fillable`/casts.

### 6. Update `WebhookController::handlePaystack`

**File:** [WebhookController.php](app/Http/Controllers/WebhookController.php)

After signature verification (unchanged) and before dispatching a job, determine routing and
persist the raw body:

```php
$rawBody = $request->getContent();
$payload = $request->all();
$event = $payload['event'] ?? null;
$reference = $payload['data']['reference'] ?? null;
$app = $payload['data']['metadata']['app'] ?? null;

$destination = match ($app) {
    'fanscorner' => 'fanscorner',
    'bidora' => 'bidora',
    default => 'bidora', // unrouted -> treat as our own, log a warning below
};

if ($app === null) {
    Log::warning('Paystack webhook missing metadata.app; routing to Bidora by default.', [
        'reference' => $reference,
        'event' => $event,
    ]);
}

$log = PaystackWebhookLog::create([
    'event' => $event,
    'reference' => $reference,
    'payload' => $payload,
    'raw_body' => $rawBody,
    'ip_address' => $request->ip(),
    'status' => 'pending',
    'destination' => $destination,
]);

if ($destination === 'fanscorner') {
    ForwardPaystackWebhookJob::dispatch($log->id, $request->header('x-paystack-signature'));
} else {
    ProcessPaystackWebhookJob::dispatch($log->id);
}

return response()->json(['status' => 'success']);
```

### 7. New job: `ForwardPaystackWebhookJob`

**New file:** `app/Jobs/ForwardPaystackWebhookJob.php`

```php
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

    public function __construct(
        public int $paystackWebhookLogId,
        public ?string $signature
    ) {}

    public function backoff(): array
    {
        return [10, 30, 60, 300, 900]; // seconds
    }

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
            $log->update(['forward_status' => 'failed']);
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

        // Let ShouldQueue retry via $tries/backoff(); on final failure Laravel
        // marks the job failed and it lands in failed_jobs for manual replay.
        $log->update(['forward_status' => 'failed']);

        throw new \RuntimeException("Fanscorner webhook forward failed with HTTP {$response->status()}");
    }

    public function failed(\Throwable $e): void
    {
        $log = PaystackWebhookLog::find($this->paystackWebhookLogId);
        $log?->update(['forward_status' => 'failed', 'status' => 'failed', 'error_message' => $e->getMessage()]);

        Log::error("ForwardPaystackWebhookJob permanently failed for log {$this->paystackWebhookLogId}: {$e->getMessage()}");
    }
}
```

Notes:
- Guards against double-forwarding on job retry after a false-negative (log already marked
  `forwarded`).
- `withBody($log->raw_body, ...)` sends the exact original bytes — no re-encoding.
- Exhausted retries leave the row `forward_status = 'failed'` and land in Laravel's
  `failed_jobs` table for visibility/manual `php artisan queue:retry`.

### 8. Tests

- `WebhookControllerTest`: add cases for `metadata.app = fanscorner` → asserts
  `ForwardPaystackWebhookJob` is dispatched (not `ProcessPaystackWebhookJob`), and for missing
  `metadata.app` → asserts it falls through to `ProcessPaystackWebhookJob` and a warning is
  logged.
- New `ForwardPaystackWebhookJobTest`: `Http::fake()` a success case (log marked `forwarded`)
  and a failure case (job throws, retry/backoff configured, `failed()` sets `forward_status =
  'failed'`).

---

## Rollout sequence

1. **Ship Fanscorner's `metadata.app` change first** — additive, no behavior change, safe to
   deploy independently.
2. **Ship Bidora's routing + forwarding code**, with `FANSCORNER_PAYSTACK_WEBHOOK_URL` set in
   staging only at first.
3. **End-to-end test in Paystack test mode**: keep both webhook URLs registered in the Paystack
   dashboard for now (test mode isn't live traffic), trigger a Fanscorner points purchase,
   confirm Bidora receives → tags `destination = fanscorner` → forwards → Fanscorner's own log
   (`ActivityEvents::ACTION_PAYMENT_WEBHOOK_RECEIVED`) shows the event and points are credited.
   Also trigger a Bidora wallet top-up and confirm it's still processed locally, unaffected.
4. **Flip the Paystack dashboard's live webhook URL** to Bidora's `/api/webhooks/paystack` only
   (this is a manual step in the Paystack dashboard — outside the codebase, you'll need to do
   this yourself).
5. **Burn-in period**: watch `paystack_webhook_logs.forward_status` for failures. Keep
   Fanscorner's endpoint reachable (do not firewall it off yet) in case of manual replay needs.

---

## Open items to confirm before/while building

- **Network reachability:** confirm Bidora's outbound HTTP calls can actually reach Fanscorner's
  webhook URL (same hosting provider? public internet? any firewall/IP allowlisting on
  Fanscorner's side that would block Bidora's egress IP?).
- **Shared secret rotation:** once `PAYSTACK_SECRET_KEY` is pulled out of Fanscorner's
  `config.php`, confirm both apps' `.env` values are updated to the same (rotated) key before
  cutover — a mismatch here would make Fanscorner reject every forwarded event.
- **Fanscorner's `PAYSTACK_CALLBACK_URL`** is currently hardcoded to
  `http://localhost:3001/points/callback` ([config.php:116](file:///Users/osanga/Herd/fanscorner/config.php:116)) — unrelated to webhooks, but
  worth flagging since it looks like a leftover dev value.
