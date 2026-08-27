<?php

use App\Jobs\ForwardPaystackWebhookJob;
use App\Models\PaystackWebhookLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

it('forwards the raw body and signature header to fanscorner and marks the log forwarded', function () {
    config(['services.paystack.fanscorner_webhook_url' => 'https://fanscorner.test/paystack/webhook']);

    $rawBody = json_encode([
        'event' => 'charge.success',
        'data' => ['reference' => 'psk_abc123', 'metadata' => ['app' => 'fanscorner']],
    ]);

    $log = PaystackWebhookLog::create([
        'event' => 'charge.success',
        'reference' => 'psk_abc123',
        'destination' => 'fanscorner',
        'payload' => json_decode($rawBody, true),
        'raw_body' => $rawBody,
        'status' => 'pending',
    ]);

    Http::fake([
        'fanscorner.test/*' => Http::response(['success' => true], 200),
    ]);

    (new ForwardPaystackWebhookJob($log->id, 'sig_123'))->handle();

    Http::assertSent(function ($request) use ($rawBody) {
        return $request->url() === 'https://fanscorner.test/paystack/webhook'
            && $request->header('x-paystack-signature')[0] === 'sig_123'
            && $request->body() === $rawBody;
    });

    $log->refresh();
    expect($log->forward_status)->toBe('forwarded')
        ->and($log->status)->toBe('processed')
        ->and($log->forward_attempts)->toBe(1)
        ->and($log->forwarded_at)->not->toBeNull();
});

it('marks the log failed and throws when fanscorner responds with an error', function () {
    config(['services.paystack.fanscorner_webhook_url' => 'https://fanscorner.test/paystack/webhook']);

    $log = PaystackWebhookLog::create([
        'event' => 'charge.success',
        'reference' => 'psk_abc123',
        'destination' => 'fanscorner',
        'payload' => [],
        'raw_body' => '{}',
        'status' => 'pending',
    ]);

    Http::fake([
        'fanscorner.test/*' => Http::response(['error' => 'server error'], 500),
    ]);

    expect(fn () => (new ForwardPaystackWebhookJob($log->id, 'sig_123'))->handle())
        ->toThrow(RuntimeException::class);

    expect($log->fresh()->forward_status)->toBe('failed');
});

it('skips re-forwarding a log that was already forwarded', function () {
    config(['services.paystack.fanscorner_webhook_url' => 'https://fanscorner.test/paystack/webhook']);

    $log = PaystackWebhookLog::create([
        'event' => 'charge.success',
        'reference' => 'psk_abc123',
        'destination' => 'fanscorner',
        'payload' => [],
        'raw_body' => '{}',
        'status' => 'processed',
        'forward_status' => 'forwarded',
    ]);

    Http::fake();

    (new ForwardPaystackWebhookJob($log->id, 'sig_123'))->handle();

    Http::assertNothingSent();
});

it('marks the log failed on final failure via the failed() handler', function () {
    $log = PaystackWebhookLog::create([
        'event' => 'charge.success',
        'reference' => 'psk_abc123',
        'destination' => 'fanscorner',
        'payload' => [],
        'raw_body' => '{}',
        'status' => 'pending',
    ]);

    (new ForwardPaystackWebhookJob($log->id, 'sig_123'))->failed(new RuntimeException('boom'));

    $log->refresh();
    expect($log->forward_status)->toBe('failed')
        ->and($log->status)->toBe('failed')
        ->and($log->error_message)->toBe('boom');
});
