<?php

use App\Enums\AuctionStatus;
use App\Jobs\CloseAuctionJob;
use App\Models\Auction;
use App\Models\Bid;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

test('closes a triggered auction and sets winner_id from is_winning bid', function () {
    $winner = User::factory()->create();
    $auction = Auction::factory()->triggeredAndExpired()->create();
    Bid::factory()->forAuction($auction)->forUser($winner)->winning()->create(['amount' => 100]);

    app()->call([new CloseAuctionJob($auction->id), 'handle']);

    $auction->refresh();

    expect($auction->status)->toBe(AuctionStatus::CLOSED);
    expect($auction->winner_id)->toBe($winner->id);
});

test('sets winner_id to null when no is_winning bid exists', function () {
    $auction = Auction::factory()->triggeredAndExpired()->create();

    app()->call([new CloseAuctionJob($auction->id), 'handle']);

    $auction->refresh();

    expect($auction->status)->toBe(AuctionStatus::CLOSED);
    expect($auction->winner_id)->toBeNull();
});

test('does nothing if auction is already closed', function () {
    $auction = Auction::factory()->closed()->create(['winner_id' => null]);

    app()->call([new CloseAuctionJob($auction->id), 'handle']);

    expect($auction->refresh()->status)->toBe(AuctionStatus::CLOSED);
});

test('does nothing if auction is in active status', function () {
    $auction = Auction::factory()->active()->create();

    app()->call([new CloseAuctionJob($auction->id), 'handle']);

    expect($auction->refresh()->status)->toBe(AuctionStatus::ACTIVE);
});

test('does nothing if auction does not exist', function () {
    expect(fn () => app()->call([new CloseAuctionJob(99999), 'handle']))->not->toThrow(Exception::class);
});

test('does not close a triggered auction whose expiry is still in the future', function () {
    // Simulates a stale job: queued with the original delay, then the admin
    // extended the countdown so expires_at moved further out.
    $auction = Auction::factory()->triggered()->create([
        'countdown_duration_seconds' => 300,
    ]);

    app()->call([new CloseAuctionJob($auction->id), 'handle']);

    expect($auction->refresh()->status)->toBe(AuctionStatus::TRIGGERED);
    expect($auction->winner_id)->toBeNull();
});
