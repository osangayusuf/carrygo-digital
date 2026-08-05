<?php

use App\Models\Auction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

test('guests are redirected from event items page', function () {
    auth()->logout();

    $this->get(route('event-items'))->assertRedirect(route('login'));
});

test('event items page renders live event auctions only', function () {
    Auction::factory()->event()->active()->create(['name' => 'Upcoming Event']);
    Auction::factory()->event()->triggered()->create(['name' => 'Live Event']);
    Auction::factory()->active()->create(['name' => 'Regular Item', 'event' => false]);
    Auction::factory()->event()->closed()->create(['name' => 'Closed Event']);

    $this->get(route('event-items'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('EventItems/Index')
            ->has('bids.data', 2)
            ->where('sort', 'recent'));
});

test('event items page excludes closed event auctions', function () {
    Auction::factory()->event()->closed()->create(['name' => 'Closed Event']);
    Auction::factory()->event()->active()->create(['name' => 'Live Event']);

    $this->get(route('event-items'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->has('bids.data', 1)
            ->where('bids.data.0.name', 'Live Event'));
});

test('event items page excludes disabled auctions', function () {
    Auction::factory()->event()->active()->create(['name' => 'Visible', 'enabled' => true]);
    Auction::factory()->event()->active()->create(['name' => 'Disabled', 'enabled' => false]);

    $this->get(route('event-items'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->has('bids.data', 1)
            ->where('bids.data.0.name', 'Visible'));
});

test('event items page filters by live status', function () {
    Auction::factory()->event()->active()->create(['name' => 'Upcoming']);
    Auction::factory()->event()->triggered()->create(['name' => 'Live Now']);

    $this->get(route('event-items', ['status' => 'live']))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->where('status', 'live')
            ->has('bids.data', 1)
            ->where('bids.data.0.name', 'Live Now')
            ->where('bids.data.0.status', 1));
});

test('event items page filters by upcoming status', function () {
    Auction::factory()->event()->active()->create(['name' => 'Upcoming']);
    Auction::factory()->event()->triggered()->create(['name' => 'Live Now']);

    $this->get(route('event-items', ['status' => 'upcoming']))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->where('status', 'upcoming')
            ->has('bids.data', 1)
            ->where('bids.data.0.name', 'Upcoming')
            ->where('bids.data.0.status', 0));
});

test('event items page filters by search keyword', function () {
    Auction::factory()->event()->active()->create(['name' => 'Fendi Shirt']);
    Auction::factory()->event()->active()->create(['name' => 'Rolex Watch']);

    $this->get(route('event-items', ['search' => 'Fendi']))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->where('search', 'Fendi')
            ->has('bids.data', 1)
            ->where('bids.data.0.name', 'Fendi Shirt'));
});

test('event items page sorts by price descending', function () {
    Auction::factory()->event()->active()->create(['name' => 'Cheap Event', 'price' => 50]);
    Auction::factory()->event()->active()->create(['name' => 'Premium Event', 'price' => 50000]);

    $this->get(route('event-items', ['sort' => 'price']))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->where('sort', 'price')
            ->where('bids.data.0.name', 'Premium Event')
            ->where('bids.data.1.name', 'Cheap Event'));
});
