<?php

use App\Models\User;
use App\Services\PaystackService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

it('verifies valid paystack signature', function () {
    config(['services.paystack.secret' => 'secret_key']);
    $payload = json_encode(['event' => 'charge.success']);
    $signature = hash_hmac('sha512', $payload, 'secret_key');

    $request = Request::create('/webhooks/paystack', 'POST', [], [], [], [], $payload);
    $request->headers->set('x-paystack-signature', $signature);

    $service = new PaystackService;

    expect($service->verifyWebhookSignature($request))->toBeTrue();
});

it('rejects invalid paystack signature', function () {
    config(['services.paystack.secret' => 'secret_key']);
    $payload = json_encode(['event' => 'charge.success']);
    $signature = hash_hmac('sha512', $payload, 'wrong_key');

    $request = Request::create('/webhooks/paystack', 'POST', [], [], [], [], $payload);
    $request->headers->set('x-paystack-signature', $signature);

    $service = new PaystackService;

    expect($service->verifyWebhookSignature($request))->toBeFalse();
});

it('initializes paystack transaction', function () {
    Http::fake([
        'api.paystack.co/transaction/initialize' => Http::response([
            'status' => true,
            'data' => [
                'authorization_url' => 'https://checkout.paystack.com/url',
                'access_code' => 'abc',
                'reference' => 'ref',
            ],
        ], 200),
    ]);

    $user = User::factory()->create(['email' => 'test@example.com']);
    $service = new PaystackService;

    $result = $service->initializeTransaction($user, 5000);

    expect($result)->toBeArray()
        ->and($result['authorization_url'])->toBe('https://checkout.paystack.com/url');

    $this->assertDatabaseHas('paystack_transactions', [
        'user_id' => $user->id,
        'reference' => 'ref',
        'amount' => 500000,
        'status' => 'pending',
    ]);
});

it('verifies paystack transaction', function () {
    Http::fake([
        'api.paystack.co/transaction/verify/ref123' => Http::response([
            'status' => true,
            'data' => [
                'status' => 'success',
                'reference' => 'ref123',
                'amount' => 500000,
            ],
        ], 200),
    ]);

    $service = new PaystackService;
    $result = $service->verifyTransaction('ref123');

    expect($result)->toBeArray()
        ->and($result['status'])->toBe('success');
});
