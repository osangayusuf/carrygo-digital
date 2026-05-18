<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('profile page is displayed', function () {
    $user = User::factory()->create([
        'phone' => '08031234567',
    ]);

    $response = $this
        ->actingAs($user)
        ->get(route('profile'));

    $response->assertOk();
});

test('profile information can be updated', function () {
    $user = User::factory()->create([
        'phone' => '08031234567',
    ]);

    $response = $this
        ->actingAs($user)
        ->patch(route('profile.update'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '08099998888',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('profile'));

    $user->refresh();

    expect($user->name)->toBe('Test User');
    expect($user->email)->toBe('test@example.com');
    expect($user->phone)->toBe('08099998888');
    expect($user->email_verified_at)->toBeNull();
});

test('email verification status is unchanged when the email address is unchanged', function () {
    $user = User::factory()->create([
        'phone' => '08031234567',
    ]);

    $response = $this
        ->actingAs($user)
        ->patch(route('profile.update'), [
            'name' => 'Test User',
            'email' => $user->email,
            'phone' => $user->phone,
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('profile'));

    expect($user->refresh()->email_verified_at)->not->toBeNull();
});

test('user can delete their account', function () {
    $user = User::factory()->create([
        'phone' => '08031234567',
    ]);

    $response = $this
        ->actingAs($user)
        ->delete(route('profile.destroy'), [
            'password' => 'password',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('home'));

    $this->assertGuest();
    expect($user->fresh())->toBeNull();
});

test('correct password must be provided to delete account', function () {
    $user = User::factory()->create([
        'phone' => '08031234567',
    ]);

    $response = $this
        ->actingAs($user)
        ->from(route('profile'))
        ->delete(route('profile.destroy'), [
            'password' => 'wrong-password',
        ]);

    $response
        ->assertSessionHasErrors('password')
        ->assertRedirect(route('profile'));

    expect($user->fresh())->not->toBeNull();
});
