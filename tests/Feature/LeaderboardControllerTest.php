<?php

use App\Models\Auction;
use App\Models\Bid;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

test('leaderboard page lists live auctions with top bidders ranked by total bid amount per item', function () {
    $auction = Auction::factory()->triggered()->create(['name' => 'Gold Watch', 'bid_count' => 5]);
    $topBidder = User::factory()->create(['phone' => '08011112222']);
    $secondBidder = User::factory()->create(['phone' => '08033334444']);

    Bid::factory()->forAuction($auction)->forUser($topBidder)->create(['amount' => 200]);
    Bid::factory()->forAuction($auction)->forUser($topBidder)->create(['amount' => 100]);
    Bid::factory()->forAuction($auction)->forUser($secondBidder)->create(['amount' => 150]);

    Auction::factory()->closed()->create(['name' => 'Closed Item']);

    $this->get(route('leaderboard'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Leaderboard/Index')
            ->has('auctions.data', 1)
            ->where('auctions.data.0.name', 'Gold Watch')
            ->has('auctions.data.0.top_bidders', 2)
            ->where('auctions.data.0.top_bidders.0.msisdn', '08011112222')
            ->where('auctions.data.0.top_bidders.0.total_points', 300)
            ->where('auctions.data.0.top_bidders.1.msisdn', '08033334444')
            ->where('auctions.data.0.top_bidders.1.total_points', 150));
});

test('leaderboard limits top bidders to three per auction', function () {
    $auction = Auction::factory()->active()->create();

    foreach (range(1, 4) as $index) {
        $user = User::factory()->create(['phone' => "0800000000{$index}"]);
        Bid::factory()->forAuction($auction)->forUser($user)->create(['amount' => $index * 100]);
    }

    $this->get(route('leaderboard'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->has('auctions.data.0.top_bidders', 3)
            ->where('auctions.data.0.top_bidders.0.total_points', 400));
});

test('leaderboard filters auctions by name search', function () {
    Auction::factory()->triggered()->create(['name' => 'Unique Item']);
    Auction::factory()->triggered()->create(['name' => 'Other Item']);

    $this->get(route('leaderboard', ['search' => 'Unique']))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->where('search', 'Unique')
            ->has('auctions.data', 1)
            ->where('auctions.data.0.name', 'Unique Item'));
});

test('leaderboard shows auctions without bids with empty top bidders', function () {
    Auction::factory()->active()->create(['name' => 'No Bids Yet']);

    $this->get(route('leaderboard'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->has('auctions.data', 1)
            ->where('auctions.data.0.name', 'No Bids Yet')
            ->where('auctions.data.0.top_bidders', []));
});
