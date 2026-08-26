<?php

use App\Jobs\ForwardPaystackWebhookJob;
use App\Jobs\ProcessPaystackWebhookJob;
use App\Models\PaystackWebhookLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

function postSignedPaystackWebhook(array $payload)
{
    $payloadJson = json_encode($payload);
    $signature = hash_hmac('sha512', $payloadJson, 'secret_key');

    return test()->call(
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
}

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
        ->and($log->destination)->toBe('bidora')
        ->and($log->status)->toBe('pending')
        ->and($log->ip_address)->toBe('127.0.0.1');

    // Verify job dispatched
    Queue::assertPushed(ProcessPaystackWebhookJob::class, function ($job) use ($log) {
        return $job->paystackWebhookLogId === $log->id;
    });
    Queue::assertNotPushed(ForwardPaystackWebhookJob::class);
});

it('forwards webhook tagged for fanscorner instead of processing it locally', function () {
    Queue::fake();
    config(['services.paystack.secret' => 'secret_key']);

    $payload = [
        'event' => 'charge.success',
        'data' => [
            'reference' => 'psk_abc123',
            'amount' => 200000,
            'metadata' => ['app' => 'fanscorner', 'user_id' => '2348012345678'],
        ],
    ];

    $response = postSignedPaystackWebhook($payload);

    $response->assertStatus(200);

    $log = PaystackWebhookLog::first();
    expect($log)->not->toBeNull()
        ->and($log->reference)->toBe('psk_abc123')
        ->and($log->destination)->toBe('fanscorner');

    Queue::assertPushed(ForwardPaystackWebhookJob::class, function ($job) use ($log) {
        return $job->paystackWebhookLogId === $log->id;
    });
    Queue::assertNotPushed(ProcessPaystackWebhookJob::class);
});

it('routes webhook to bidora and logs a warning when metadata.app is missing', function () {
    Queue::fake();
    config(['services.paystack.secret' => 'secret_key']);

    Illuminate\Support\Facades\Log::spy();

    $payload = [
        'event' => 'charge.success',
        'data' => [
            'reference' => 'ref_unknown',
            'amount' => 300000,
        ],
    ];

    $response = postSignedPaystackWebhook($payload);

    $response->assertStatus(200);

    $log = PaystackWebhookLog::first();
    expect($log->destination)->toBe('bidora');

    Queue::assertPushed(ProcessPaystackWebhookJob::class);

    Illuminate\Support\Facades\Log::shouldHaveReceived('warning')
        ->withArgs(fn ($message) => str_contains($message, 'missing metadata.app'))
        ->once();
});
