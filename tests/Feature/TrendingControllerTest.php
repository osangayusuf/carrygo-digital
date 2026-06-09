<?php

use App\Models\Auction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

test('guests can access trending page without redirect', function () {
    auth()->logout();

    $this->get(route('trending'))->assertSuccessful();
});

test('trending page renders with paginated bids', function () {
    Auction::factory()->active()->count(3)->create();

    $this->get(route('trending'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Trending/Index')
            ->has('bids.data', 3)
            ->where('sort', 'recent')
            ->has('categories'));
});

test('trending page filters by search keyword', function () {
    Auction::factory()->active()->create(['name' => 'Gucci Leather Bag', 'category' => 'Fashion']);
    Auction::factory()->active()->create(['name' => 'Samsung Phone', 'category' => 'Electronics']);

    $this->get(route('trending', ['search' => 'Gucci']))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->where('search', 'Gucci')
            ->has('bids.data', 1)
            ->where('bids.data.0.name', 'Gucci Leather Bag'));
});

test('trending page filters by category', function () {
    Auction::factory()->active()->create(['name' => 'Chair', 'category' => 'Furniture']);
    Auction::factory()->active()->create(['name' => 'Phone', 'category' => 'Electronics']);

    $this->get(route('trending', ['category' => 'Electronics']))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->where('category', 'Electronics')
            ->has('bids.data', 1)
            ->where('bids.data.0.name', 'Phone'));
});

test('trending page sorts by price descending', function () {
    Auction::factory()->active()->create(['name' => 'Cheap Item', 'price' => 100]);
    Auction::factory()->active()->create(['name' => 'Luxury Item', 'price' => 50000]);

    $this->get(route('trending', ['sort' => 'price']))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->where('sort', 'price')
            ->where('bids.data.0.name', 'Luxury Item')
            ->where('bids.data.1.name', 'Cheap Item'));
});

test('trending bid payload uses frontend status codes', function () {
    $auction = Auction::factory()->triggered()->create(['name' => 'Triggered Item']);

    $this->get(route('trending'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->where('bids.data.0.status', 1)
            ->where('bids.data.0.name', 'Triggered Item'));
});

test('trending page excludes draft and closed auctions', function () {
    Auction::factory()->active()->create(['name' => 'Visible']);
    Auction::factory()->draft()->create(['name' => 'Draft']);
    Auction::factory()->closed()->create(['name' => 'Closed']);

    $this->get(route('trending'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->has('bids.data', 1)
            ->where('bids.data.0.name', 'Visible'));
});
