<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->withoutVite();
});

test('verified user without completed onboarding can view onboarding page', function () {
    $user = User::factory()->create([
        'onboarding_completed_at' => null,
    ]);

    $this->actingAs($user)
        ->get(route('onboarding'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Onboarding/Index')
            ->has('user')
            ->has('balances')
            ->has('walletConfig')
        );
});

test('user with completed onboarding is redirected from onboarding to home', function () {
    $user = User::factory()->create([
        'onboarding_completed_at' => now(),
    ]);

    $this->actingAs($user)
        ->get(route('onboarding'))
        ->assertRedirect(route('home', absolute: false));
});

test('completing onboarding sets onboarding_completed_at and redirects home', function () {
    $user = User::factory()->create([
        'onboarding_completed_at' => null,
    ]);

    $this->actingAs($user)
        ->post(route('onboarding.complete'))
        ->assertRedirect(route('home', absolute: false));

    expect($user->fresh()->onboarding_completed_at)->not->toBeNull();
});

test('unverified user cannot access onboarding', function () {
    $user = User::factory()->unverified()->create();

    $this->actingAs($user)
        ->get(route('onboarding'))
        ->assertRedirect(route('verification.notice', absolute: false));
});

test('guest cannot access onboarding', function () {
    $this->get(route('onboarding'))
        ->assertRedirect(route('login', absolute: false));
});
