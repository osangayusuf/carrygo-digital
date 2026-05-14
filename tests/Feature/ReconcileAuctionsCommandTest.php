<?php

use App\Enums\AuctionStatus;
use App\Models\Auction;
use App\Models\Bid;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

test('closes triggered auctions past expires_at', function () {
    $auction = Auction::factory()->triggered()->create([
        'expires_at' => now()->subMinutes(2),
    ]);

    $this->artisan('auctions:reconcile')->assertSuccessful();

    expect($auction->refresh()->status)->toBe(AuctionStatus::CLOSED);
});

test('sets winner_id when closing an overdue triggered auction', function () {
    $winner = User::factory()->create();
    $auction = Auction::factory()->triggered()->create([
        'expires_at' => now()->subMinutes(2),
    ]);
    Bid::factory()->forAuction($auction)->forUser($winner)->winning()->create(['amount' => 100]);

    $this->artisan('auctions:reconcile')->assertSuccessful();

    expect($auction->refresh()->winner_id)->toBe($winner->id);
});

test('skips triggered auctions not yet past expires_at', function () {
    $auction = Auction::factory()->triggered()->create([
        'expires_at' => now()->addMinutes(5),
    ]);

    $this->artisan('auctions:reconcile')->assertSuccessful();

    expect($auction->refresh()->status)->toBe(AuctionStatus::TRIGGERED);
});

test('reconciles a mismatched bid_count on an active auction', function () {
    $auction = Auction::factory()->active()->create(['bid_count' => 99]);
    Bid::factory()->count(3)->forAuction($auction)->create();

    $this->artisan('auctions:reconcile')->assertSuccessful();

    expect($auction->refresh()->bid_count)->toBe(3);
});

test('does not reconcile bid_count for draft auctions', function () {
    $auction = Auction::factory()->draft()->create(['bid_count' => 99]);
    Bid::factory()->count(3)->forAuction($auction)->create();

    $this->artisan('auctions:reconcile')->assertSuccessful();

    // Draft auctions are excluded from bid_count reconciliation
    expect($auction->refresh()->bid_count)->toBe(99);
});
