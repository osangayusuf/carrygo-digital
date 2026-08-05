<?php

use App\Models\Auction;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

test('rejects a bid when the user has insufficient points', function () {
    $user = User::factory()->create(['points_balance' => 10]);
    $auction = Auction::factory()->active()->create();

    $this->actingAs($user)
        ->from(route('home'))
        ->post(route('auctions.bids.store', $auction), [
            'points' => 50,
        ])
        ->assertRedirect(route('home'))
        ->assertSessionHasErrors([
            'points' => 'You only have 10 points available.',
        ]);
});

test('returns validation errors for inertia bid requests', function () {
    $user = User::factory()->create(['points_balance' => 10]);
    $auction = Auction::factory()->active()->create();

    $response = $this->actingAs($user)
        ->from(route('home'))
        ->withHeaders([
            'X-Inertia' => 'true',
            'X-Requested-With' => 'XMLHttpRequest',
            'Accept' => 'text/html, application/xhtml+xml',
        ])
        ->post(route('auctions.bids.store', $auction), [
            'points' => 50,
        ]);

    $response->assertSessionHasErrors('points');
    $response->assertRedirect(route('home'));
});

test('places a bid successfully', function () {
    $user = User::factory()->create(['points_balance' => 100]);
    $auction = Auction::factory()->active()->create();

    $this->actingAs($user)
        ->from(route('home'))
        ->post(route('auctions.bids.store', $auction), [
            'points' => 50,
        ])
        ->assertRedirect(route('home'));

    expect($user->refresh()->points_balance)->toBe(50);
});
