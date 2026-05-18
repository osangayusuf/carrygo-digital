<?php

use App\Models\Auction;
use App\Models\Bid;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

test('winners page renders closed auctions with winners only', function () {
    $winner = User::factory()->create(['name' => 'Ada Winner', 'phone' => '08012345678']);
    $auction = Auction::factory()->closed()->create([
        'name' => 'Luxury Watch',
        'winner_id' => $winner->id,
        'bid_count' => 12,
    ]);

    Bid::factory()->forAuction($auction)->forUser($winner)->winning()->create(['amount' => 250]);
    Bid::factory()->forAuction($auction)->forUser($winner)->create(['amount' => 50]);

    Auction::factory()->active()->create(['name' => 'Still Live']);
    Auction::factory()->closed()->create(['name' => 'No Winner', 'winner_id' => null]);

    $this->get(route('winners'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Winners/Index')
            ->has('winners.data', 1)
            ->where('winners.data.0.winner_name', 'Ada Winner')
            ->where('winners.data.0.winning_pts', 250)
            ->where('winners.data.0.total_pts_bid', 300)
            ->where('winners.data.0.bid_count', 12)
            ->where('winners.data.0.bid.name', 'Luxury Watch'));
});

test('winners page filters by search keyword', function () {
    $winner = User::factory()->create(['name' => 'John Doe']);
    $auction = Auction::factory()->closed()->create(['name' => 'Gucci Bag', 'winner_id' => $winner->id]);
    Bid::factory()->forAuction($auction)->forUser($winner)->winning()->create(['amount' => 100]);

    $otherWinner = User::factory()->create();
    $other = Auction::factory()->closed()->create(['name' => 'Rolex', 'winner_id' => $otherWinner->id]);
    Bid::factory()->forAuction($other)->forUser($otherWinner)->winning()->create();

    $this->get(route('winners', ['search' => 'Gucci']))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->where('search', 'Gucci')
            ->has('winners.data', 1)
            ->where('winners.data.0.bid.name', 'Gucci Bag'));
});

test('winners page excludes closed auctions without winner_id', function () {
    $winner = User::factory()->create();
    Auction::factory()->closed()->create(['winner_id' => $winner->id, 'name' => 'Has Winner']);
    Auction::factory()->closed()->create(['winner_id' => null, 'name' => 'No Winner']);

    $this->get(route('winners'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page->has('winners.data', 1));
});
