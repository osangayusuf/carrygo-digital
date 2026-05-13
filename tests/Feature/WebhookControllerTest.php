<?php

use App\Jobs\ProcessPaystackWebhookJob;
use App\Models\PaystackWebhookLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

it('rejects webhook with invalid signature', function () {
    config(['services.paystack.secret' => 'secret_key']);
    $payload = ['event' => 'charge.success'];

    $response = $this->postJson('/api/webhooks/paystack', $payload, [
        'x-paystack-signature' => 'invalid_signature',
    ]);

    $response->assertStatus(403);
});

it('creates log and dispatches job for valid webhook', function () {
    Queue::fake();
    config(['services.paystack.secret' => 'secret_key']);

    $payload = [
        'event' => 'charge.success',
        'data' => [
            'reference' => 'ref_123',
            'amount' => 500000,
        ],
    ];

    $payloadJson = json_encode($payload);
    $signature = hash_hmac('sha512', $payloadJson, 'secret_key');

    $response = $this->call(
        'POST',
        '/api/webhooks/paystack',
        [],
        [],
        [],
        [
            'HTTP_x-paystack-signature' => $signature,
            'CONTENT_TYPE' => 'application/json',
            'REMOTE_ADDR' => '127.0.0.1',
        ],
        $payloadJson
    );

    $response->assertStatus(200);

    // Verify log created
    $log = PaystackWebhookLog::first();
    expect($log)->not->toBeNull()
        ->and($log->event)->toBe('charge.success')
        ->and($log->reference)->toBe('ref_123')
        ->and($log->status)->toBe('pending')
        ->and($log->ip_address)->toBe('127.0.0.1');

    // Verify job dispatched
    Queue::assertPushed(ProcessPaystackWebhookJob::class, function ($job) use ($log) {
        return $job->paystackWebhookLogId === $log->id;
    });
});
