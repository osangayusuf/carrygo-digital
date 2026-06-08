<?php

use App\Enums\TransactionType;
use App\Models\PointTransaction;
use App\Models\User;
use App\Notifications\WelcomeNotification;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;

uses(RefreshDatabase::class);

test('points are not awarded on initial registration before email is verified', function () {
    Notification::fake();

    // Standard registration post request
    $response = $this->post(route('register.store'), [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'phone' => '08031234567',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $user = User::where('email', 'test@example.com')->first();
    expect($user)->not->toBeNull();
    expect($user->points_balance)->toEqual(0);

    Notification::assertNotSentTo($user, WelcomeNotification::class);
});

test('points are awarded and notification is sent when email is verified', function () {
    Notification::fake();
    config(['points.registration_points' => 100]);

    // Create an unverified user
    $user = User::factory()->create([
        'email_verified_at' => null,
        'points_balance' => 0,
    ]);

    // Manually trigger verified event
    event(new Verified($user));

    $user->refresh();
    expect($user->points_balance)->toEqual(100);

    // Verify transaction was logged
    $transaction = PointTransaction::where('user_id', $user->id)
        ->where('type', TransactionType::WELCOME_BONUS)
        ->first();

    expect($transaction)->not->toBeNull();
    expect($transaction->amount)->toEqual(100);

    // Verify notification was sent
    Notification::assertSentTo($user, WelcomeNotification::class, function ($notification) use ($transaction) {
        return $notification->transaction->id === $transaction->id;
    });
});

test('welcome points are only awarded once', function () {
    Notification::fake();

    $user = User::factory()->create([
        'email_verified_at' => null,
        'points_balance' => 0,
    ]);

    // First verification
    event(new Verified($user));

    $user->refresh();
    expect($user->points_balance)->toEqual(50); // Default

    // Second verification
    event(new Verified($user));

    $user->refresh();
    expect($user->points_balance)->toEqual(50); // Remains 50
    expect(PointTransaction::where('user_id', $user->id)->count())->toBe(1);
});

test('social login auto-verification fires the verified event and awards points', function () {
    Notification::fake();
    config(['points.registration_points' => 75]);

    $socialiteUser = new SocialiteUser;
    $socialiteUser->map([
        'id' => '1234567890',
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'token' => 'mock-token',
        'refreshToken' => 'mock-refresh-token',
        'expiresIn' => 3600,
    ]);

    $providerMock = Mockery::mock('Laravel\Socialite\Two\GoogleProvider');
    $providerMock->shouldReceive('user')->andReturn($socialiteUser);

    Socialite::shouldReceive('driver')
        ->with('google')
        ->andReturn($providerMock);

    $response = $this->get(route('auth.social.callback', ['provider' => 'google']));

    $user = User::where('email', 'john@example.com')->first();
    expect($user)->not->toBeNull();
    expect($user->email_verified_at)->not->toBeNull();
    expect($user->points_balance)->toEqual(75);

    // Verify transaction
    $transaction = PointTransaction::where('user_id', $user->id)
        ->where('type', TransactionType::WELCOME_BONUS)
        ->first();
    expect($transaction)->not->toBeNull();

    Notification::assertSentTo($user, WelcomeNotification::class);
});
