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
        );
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
