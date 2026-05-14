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
    $auction = Auction::factory()->triggered()->create();
    Bid::factory()->forAuction($auction)->forUser($winner)->winning()->create(['amount' => 100]);

    (new CloseAuctionJob($auction->id))->handle();

    $auction->refresh();

    expect($auction->status)->toBe(AuctionStatus::CLOSED);
    expect($auction->winner_id)->toBe($winner->id);
});

test('sets winner_id to null when no is_winning bid exists', function () {
    $auction = Auction::factory()->triggered()->create();

    (new CloseAuctionJob($auction->id))->handle();

    $auction->refresh();

    expect($auction->status)->toBe(AuctionStatus::CLOSED);
    expect($auction->winner_id)->toBeNull();
});

test('does nothing if auction is already closed', function () {
    $auction = Auction::factory()->closed()->create(['winner_id' => null]);

    (new CloseAuctionJob($auction->id))->handle();

    expect($auction->refresh()->status)->toBe(AuctionStatus::CLOSED);
});

test('does nothing if auction is in active status', function () {
    $auction = Auction::factory()->active()->create();

    (new CloseAuctionJob($auction->id))->handle();

    expect($auction->refresh()->status)->toBe(AuctionStatus::ACTIVE);
});

test('does nothing if auction does not exist', function () {
    expect(fn () => (new CloseAuctionJob(99999))->handle())->not->toThrow(Exception::class);
});
