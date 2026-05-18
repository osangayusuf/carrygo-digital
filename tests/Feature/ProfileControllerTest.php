<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);
use Inertia\Testing\AssertableInertia as Assert;

test('guests are redirected to login when visiting profile', function () {
    $this->get(route('profile'))
        ->assertRedirect(route('login'));
});

test('authenticated users can view the profile page', function () {
    $user = User::factory()->create([
        'phone' => '08031234567',
    ]);

    $this->actingAs($user)
        ->get(route('profile'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Profile/Index')
            ->has('mustVerifyEmail')
        );
});

test('settings profile url redirects to profile', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/settings/profile')
        ->assertRedirect(route('profile'));
});
