<?php

use App\Models\Auction;
use App\Models\Bid;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(LazilyRefreshDatabase::class);

test('auction show page renders auction details', function () {
    $auction = Auction::factory()->active()->create();

    $this->get(route('auctions.show', $auction))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Auction/Show')
            ->where('auction.id', $auction->id)
            ->where('auction.name', $auction->name)
            ->where('auction.description', $auction->description)
        );
});

test('disabled auction show page 404s for guests', function () {
    $auction = Auction::factory()->active()->create(['enabled' => false]);

    $this->get(route('auctions.show', $auction))->assertNotFound();
});

test('disabled auction show page 404s for regular users', function () {
    $user = User::factory()->create();
    $auction = Auction::factory()->active()->create(['enabled' => false]);

    $this->actingAs($user)
        ->get(route('auctions.show', $auction))
        ->assertNotFound();
});

test('disabled auction show page is viewable by admins', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $auction = Auction::factory()->active()->create(['enabled' => false]);

    $this->actingAs($admin)
        ->get(route('auctions.show', $auction))
        ->assertSuccessful();
});

test('authenticated user sees points on auction show page', function () {
    $user = User::factory()->create(['points_balance' => 500]);
    $auction = Auction::factory()->active()->create();

    $this->actingAs($user)
        ->get(route('auctions.show', $auction))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->where('userPoints', 500)
        );
});

test('auction show page includes top bidders by cumulative points', function () {
    $auction = Auction::factory()->active()->create();
    $leader = User::factory()->create(['phone' => '08011111111']);
    $runnerUp = User::factory()->create(['phone' => '08022222222']);

    Bid::factory()->create(['auction_id' => $auction->id, 'user_id' => $leader->id, 'amount' => 150]);
    Bid::factory()->create(['auction_id' => $auction->id, 'user_id' => $leader->id, 'amount' => 50]);
    Bid::factory()->create(['auction_id' => $auction->id, 'user_id' => $runnerUp->id, 'amount' => 120]);

    $this->get(route('auctions.show', $auction))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->has('topBidders', 2)
            ->where('topBidders.0.msisdn', '08011111111')
            ->where('topBidders.0.total_points', 200)
            ->where('topBidders.1.msisdn', '08022222222')
            ->where('topBidders.1.total_points', 120)
        );
});

test('auction show page returns at most ten top bidders', function () {
    $auction = Auction::factory()->active()->create();

    foreach (range(1, 12) as $index) {
        $user = User::factory()->create(['phone' => "080000000{$index}"]);
        Bid::factory()->create([
            'auction_id' => $auction->id,
            'user_id' => $user->id,
            'amount' => $index * 10,
        ]);
    }

    $this->get(route('auctions.show', $auction))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page->has('topBidders', 10));
});

test('auction show page resolves tie of top bidders by who reached the total first', function () {
    $auction = Auction::factory()->active()->create();
    $userA = User::factory()->create(['phone' => '08011111111']);
    $userB = User::factory()->create(['phone' => '08022222222']);

    // 1. User A bids 100 first
    Bid::factory()->create(['auction_id' => $auction->id, 'user_id' => $userA->id, 'amount' => 100]);
    // 2. User B bids 50
    Bid::factory()->create(['auction_id' => $auction->id, 'user_id' => $userB->id, 'amount' => 50]);
    // 3. User B bids another 50 to tie User A
    Bid::factory()->create(['auction_id' => $auction->id, 'user_id' => $userB->id, 'amount' => 50]);

    // Both are at 100, but User A reached 100 first.
    // So User A should be listed before User B in topBidders.
    $this->get(route('auctions.show', $auction))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->has('topBidders', 2)
            ->where('topBidders.0.msisdn', '08011111111')
            ->where('topBidders.0.total_points', 100)
            ->where('topBidders.1.msisdn', '08022222222')
            ->where('topBidders.1.total_points', 100)
        );
});
