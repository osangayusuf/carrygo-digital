<?php

use App\Models\Auction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

test('open bids page renders triggered auctions only', function () {
    Auction::factory()->triggered()->create(['name' => 'Countdown Bag']);
    Auction::factory()->active()->create(['name' => 'Active Only']);
    Auction::factory()->closed()->create(['name' => 'Closed Item']);

    $this->get(route('open-bids'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('OpenBids/Index')
            ->where('sort', 'ending_soon')
            ->has('bids.data', 1)
            ->where('bids.data.0.name', 'Countdown Bag')
            ->where('bids.data.0.status', 1));
});

test('open bids page filters by search keyword', function () {
    Auction::factory()->triggered()->create(['name' => 'Gucci Bag']);
    Auction::factory()->triggered()->create(['name' => 'Prada Shoes']);

    $this->get(route('open-bids', ['search' => 'Gucci']))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->where('search', 'Gucci')
            ->has('bids.data', 1)
            ->where('bids.data.0.name', 'Gucci Bag'));
});

test('open bids page sorts by ending soonest by default', function () {
    $endingLater = Auction::factory()->triggered()->create([
        'name' => 'Later',
        'expires_at' => now()->addHours(2),
    ]);

    $endingSoon = Auction::factory()->triggered()->create([
        'name' => 'Sooner',
        'expires_at' => now()->addMinutes(30),
    ]);

    $this->get(route('open-bids'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->where('bids.data.0.name', 'Sooner')
            ->where('bids.data.1.name', 'Later'));

    expect($endingSoon->expires_at?->lt($endingLater->expires_at))->toBeTrue();
});

test('open bids page supports price sort', function () {
    Auction::factory()->triggered()->create(['name' => 'Cheap', 'price' => 100]);
    Auction::factory()->triggered()->create(['name' => 'Luxury', 'price' => 90000]);

    $this->get(route('open-bids', ['sort' => 'price']))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->where('sort', 'price')
            ->where('bids.data.0.name', 'Luxury')
            ->where('bids.data.1.name', 'Cheap'));
});
