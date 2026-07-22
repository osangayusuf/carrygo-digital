<?php

use App\Models\Auction;
use App\Models\Bid;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

test('guest is redirected from leaderboard page to register with redirected param', function () {
    $this->get(route('leaderboard'))
        ->assertRedirect(route('register', ['redirected' => 1]));
});

test('leaderboard page lists live auctions with top bidders ranked by total bid amount per item', function () {
    $user = User::factory()->create();
    $auction = Auction::factory()->triggered()->create(['name' => 'Gold Watch', 'bid_count' => 5]);
    $topBidder = User::factory()->create(['phone' => '08011112222']);
    $secondBidder = User::factory()->create(['phone' => '08033334444']);

    Bid::factory()->forAuction($auction)->forUser($topBidder)->create(['amount' => 200]);
    Bid::factory()->forAuction($auction)->forUser($topBidder)->create(['amount' => 100]);
    Bid::factory()->forAuction($auction)->forUser($secondBidder)->create(['amount' => 150]);

    Auction::factory()->closed()->create(['name' => 'Closed Item']);

    $this->actingAs($user)
        ->get(route('leaderboard'))
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
    $user = User::factory()->create();
    $auction = Auction::factory()->active()->create();

    foreach (range(1, 4) as $index) {
        $u = User::factory()->create(['phone' => "0800000000{$index}"]);
        Bid::factory()->forAuction($auction)->forUser($u)->create(['amount' => $index * 100]);
    }

    $this->actingAs($user)
        ->get(route('leaderboard'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->has('auctions.data.0.top_bidders', 3)
            ->where('auctions.data.0.top_bidders.0.total_points', 400));
});

test('leaderboard filters auctions by name search', function () {
    $user = User::factory()->create();
    Auction::factory()->triggered()->create(['name' => 'Unique Item']);
    Auction::factory()->triggered()->create(['name' => 'Other Item']);

    $this->actingAs($user)
        ->get(route('leaderboard', ['search' => 'Unique']))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->where('search', 'Unique')
            ->has('auctions.data', 1)
            ->where('auctions.data.0.name', 'Unique Item'));
});

test('leaderboard shows auctions without bids with empty top bidders', function () {
    $user = User::factory()->create();
    Auction::factory()->active()->create(['name' => 'No Bids Yet']);

    $this->actingAs($user)
        ->get(route('leaderboard'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->has('auctions.data', 1)
            ->where('auctions.data.0.name', 'No Bids Yet')
            ->where('auctions.data.0.top_bidders', []));
});

test('leaderboard resolves tie of top bidders by who reached the total first', function () {
    $user = User::factory()->create();
    $auction = Auction::factory()->active()->create(['name' => 'Tied Item']);
    $userA = User::factory()->create(['phone' => '08011111111']);
    $userB = User::factory()->create(['phone' => '08022222222']);

    // 1. User A bids 100 first
    Bid::factory()->create(['auction_id' => $auction->id, 'user_id' => $userA->id, 'amount' => 100]);
    // 2. User B bids 50
    Bid::factory()->create(['auction_id' => $auction->id, 'user_id' => $userB->id, 'amount' => 50]);
    // 3. User B bids another 50 to tie User A
    Bid::factory()->create(['auction_id' => $auction->id, 'user_id' => $userB->id, 'amount' => 50]);

    // Both are at 100, but User A reached 100 first.
    // So User A should be listed before User B in top_bidders.
    $this->actingAs($user)
        ->get(route('leaderboard'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->has('auctions.data.0.top_bidders', 2)
            ->where('auctions.data.0.top_bidders.0.msisdn', '08011111111')
            ->where('auctions.data.0.top_bidders.0.total_points', 100)
            ->where('auctions.data.0.top_bidders.1.msisdn', '08022222222')
            ->where('auctions.data.0.top_bidders.1.total_points', 100)
        );
});
