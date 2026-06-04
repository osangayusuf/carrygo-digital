<?php

use App\Models\SocialProvider;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;

uses(RefreshDatabase::class);

test('it redirects to google oauth gateway', function () {
    $providerMock = Mockery::mock('Laravel\Socialite\Two\GoogleProvider');
    $providerMock->shouldReceive('redirect')->andReturn(redirect('https://accounts.google.com/o/oauth2/auth'));

    Socialite::shouldReceive('driver')
        ->with('google')
        ->andReturn($providerMock);

    $response = $this->get(route('auth.social.redirect', ['provider' => 'google']));

    $response->assertRedirect('https://accounts.google.com/o/oauth2/auth');
});

test('it redirects to facebook oauth gateway', function () {
    $providerMock = Mockery::mock('Laravel\Socialite\Two\FacebookProvider');
    $providerMock->shouldReceive('redirect')->andReturn(redirect('https://www.facebook.com/v19.0/dialog/oauth'));

    Socialite::shouldReceive('driver')
        ->with('facebook')
        ->andReturn($providerMock);

    $response = $this->get(route('auth.social.redirect', ['provider' => 'facebook']));

    $response->assertRedirect('https://www.facebook.com/v19.0/dialog/oauth');
});

test('it rejects unsupported oauth providers', function () {
    $response = $this->get('/auth/github/redirect');

    $response->assertStatus(404);
});

test('it creates a new user, links the social account, and redirects to onboarding', function () {
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

    $response->assertRedirect(route('onboarding'));

    $user = User::where('email', 'john@example.com')->first();
    expect($user)->not->toBeNull();
    expect($user->name)->toBe('John Doe');
    expect($user->phone)->toBeNull();
    expect($user->password)->toBeNull();
    expect($user->email_verified_at)->not->toBeNull();

    $socialProvider = SocialProvider::where('provider_name', 'google')
        ->where('provider_id', '1234567890')
        ->where('user_id', $user->id)
        ->first();

    expect($socialProvider)->not->toBeNull();
    expect($socialProvider->token)->toBe('mock-token');
});

test('it automatically merges a google login with an existing user matching the same email', function () {
    $existingUser = User::factory()->create([
        'name' => 'Existing User',
        'email' => 'john@example.com',
        'phone' => '08031234567',
        'email_verified_at' => null,
    ]);

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

    if ($existingUser->onboarding_completed_at === null) {
        $response->assertRedirect(route('onboarding'));
    } else {
        $response->assertRedirect(route('home'));
    }

    $existingUser->refresh();
    expect($existingUser->email_verified_at)->not->toBeNull();

    $socialProvider = SocialProvider::where('provider_name', 'google')
        ->where('provider_id', '1234567890')
        ->where('user_id', $existingUser->id)
        ->first();

    expect($socialProvider)->not->toBeNull();
});

test('it logs in a previously linked social user and redirects to home', function () {
    $user = User::factory()->create([
        'name' => 'Social User',
        'email' => 'john@example.com',
        'phone' => '08031234567',
        'onboarding_completed_at' => now(),
    ]);

    $user->socialProviders()->create([
        'provider_name' => 'google',
        'provider_id' => '1234567890',
        'token' => 'old-token',
    ]);

    $socialiteUser = new SocialiteUser;
    $socialiteUser->map([
        'id' => '1234567890',
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'token' => 'new-token',
        'refreshToken' => 'new-refresh-token',
        'expiresIn' => 3600,
    ]);

    $providerMock = Mockery::mock('Laravel\Socialite\Two\GoogleProvider');
    $providerMock->shouldReceive('user')->andReturn($socialiteUser);

    Socialite::shouldReceive('driver')
        ->with('google')
        ->andReturn($providerMock);

    $response = $this->get(route('auth.social.callback', ['provider' => 'google']));

    $response->assertRedirect(route('home'));

    $socialProvider = SocialProvider::where('provider_name', 'google')
        ->where('provider_id', '1234567890')
        ->first();

    expect($socialProvider->token)->toBe('new-token');
    expect(Auth::id())->toBe($user->id);
});

test('it handles oauth exceptions gracefully', function () {
    Socialite::shouldReceive('driver')
        ->with('google')
        ->andThrow(new Exception('Invalid grant or cancelled login'));

    $response = $this->get(route('auth.social.callback', ['provider' => 'google']));

    $response->assertRedirect(route('login'));
    $response->assertSessionHasErrors('email');
});
