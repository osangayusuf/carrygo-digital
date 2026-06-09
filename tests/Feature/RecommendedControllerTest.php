<?php

use App\Models\Auction;
use App\Models\Bid;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

test('guests can access recommended page without redirect', function () {
    $this->get(route('recommended'))->assertSuccessful();
});

test('recommended page falls back to hybrid sorting for guests', function () {
    // Create auctions with different interest levels
    Auction::factory()->active()->create([
        'name' => 'Low Interest',
        'bid_count' => 1,
        'opening_points' => 10,
        'current_points' => 1,
    ]);
    Auction::factory()->active()->create([
        'name' => 'High Interest',
        'bid_count' => 50,
        'opening_points' => 10,
        'current_points' => 1,
    ]);

    $this->get(route('recommended'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Recommended/Index')
            ->has('bids.data', 2)
            ->where('bids.data.0.name', 'High Interest'));
});

test('authenticated user gets personalized recommendations based on past bid categories', function () {
    $user = User::factory()->create();

    // Create auctions
    $fashionAuction = Auction::factory()->active()->create(['category' => 'Fashion', 'name' => 'Fashion Item']);
    $electronicsAuction = Auction::factory()->active()->create(['category' => 'Electronics', 'name' => 'Electronics Item']);

    // User bids on fashion
    Bid::factory()->create([
        'user_id' => $user->id,
        'auction_id' => $fashionAuction->id,
    ]);

    $this->actingAs($user)
        ->get(route('recommended'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->has('bids.data', 1)
            ->where('bids.data.0.name', 'Fashion Item'));
});
