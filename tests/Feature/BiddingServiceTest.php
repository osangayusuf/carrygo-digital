<?php

use App\Enums\AuctionStatus;
use App\Enums\TransactionType;
use App\Jobs\CloseAuctionJob;
use App\Models\Auction;
use App\Models\Bid;
use App\Models\User;
use App\Services\BiddingService;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Queue;

uses(LazilyRefreshDatabase::class);

beforeEach(function () {
    Queue::fake();
    $this->service = app(BiddingService::class);
    $this->user = User::factory()->create(['points_balance' => 10000]);
    $this->auction = Auction::factory()->active()->create(['opening_points' => 500]);
});

test('places a valid bid and debits user points_balance', function () {
    $this->service->placeBid($this->user, $this->auction, 50);

    expect($this->user->refresh()->points_balance)->toBe('9950.00');
    expect(Bid::where('auction_id', $this->auction->id)->count())->toBe(1);
});

test('rejects a bid below min_bid_increment', function () {
    expect(fn () => $this->service->placeBid($this->user, $this->auction, 5))
        ->toThrow(InvalidArgumentException::class, 'Bid amount must be at least');
});

test('rejects a bid with insufficient points_balance', function () {
    $poorUser = User::factory()->create(['points_balance' => 5]);

    expect(fn () => $this->service->placeBid($poorUser, $this->auction, 50))
        ->toThrow(InvalidArgumentException::class, 'Insufficient points balance.');
});

test('rejects a bid on a closed auction', function () {
    $auction = Auction::factory()->closed()->create();

    expect(fn () => $this->service->placeBid($this->user, $auction, 50))
        ->toThrow(InvalidArgumentException::class, 'Bids can only be placed on active or triggered auctions.');
});

test('rejects a bid on a draft auction', function () {
    $auction = Auction::factory()->draft()->create();

    expect(fn () => $this->service->placeBid($this->user, $auction, 50))
        ->toThrow(InvalidArgumentException::class, 'Bids can only be placed on active or triggered auctions.');
});

test('bid_count increments by 1 after each bid', function () {
    $this->service->placeBid($this->user, $this->auction, 10);
    $this->service->placeBid($this->user, $this->auction, 10);

    expect($this->auction->refresh()->bid_count)->toBe(2);
});

test('current_points increments by bid amount', function () {
    $this->service->placeBid($this->user, $this->auction, 30);
    $this->service->placeBid($this->user, $this->auction, 20);

    expect($this->auction->refresh()->current_points)->toBe(50);
});

test('single bidder most recent bid has is_winning set to true', function () {
    $this->service->placeBid($this->user, $this->auction, 50);

    $winningBid = Bid::where('auction_id', $this->auction->id)
        ->where('is_winning', true)
        ->first();

    expect($winningBid)->not->toBeNull();
    expect($winningBid->user_id)->toBe($this->user->id);
});

test('second higher cumulative bidder takes is_winning from first bidder', function () {
    $userA = User::factory()->create(['points_balance' => 10000]);
    $userB = User::factory()->create(['points_balance' => 10000]);

    $this->service->placeBid($userA, $this->auction, 50);
    $this->service->placeBid($userB, $this->auction, 100);

    $winningBid = Bid::where('auction_id', $this->auction->id)
        ->where('is_winning', true)
        ->first();

    expect($winningBid->user_id)->toBe($userB->id);

    $losingBids = Bid::where('auction_id', $this->auction->id)
        ->where('user_id', $userA->id)
        ->where('is_winning', true)
        ->count();

    expect($losingBids)->toBe(0);
});

test('tied cumulative totals result in no is_winning bids', function () {
    $userA = User::factory()->create(['points_balance' => 10000]);
    $userB = User::factory()->create(['points_balance' => 10000]);

    $this->service->placeBid($userA, $this->auction, 50);
    $this->service->placeBid($userB, $this->auction, 50);

    $winningCount = Bid::where('auction_id', $this->auction->id)
        ->where('is_winning', true)
        ->count();

    expect($winningCount)->toBe(0);
});

test('crossing opening_points sets status to triggered with triggered_at and expires_at', function () {
    $auction = Auction::factory()->active()->create([
        'opening_points' => 30,
        'countdown_duration_seconds' => 120,
    ]);

    $this->service->placeBid($this->user, $auction, 30);

    $auction->refresh();

    expect($auction->status)->toBe(AuctionStatus::TRIGGERED);
    expect($auction->triggered_at)->not->toBeNull();
    expect($auction->expires_at)->not->toBeNull();
});

test('CloseAuctionJob is dispatched with correct delay when auction triggers', function () {
    $auction = Auction::factory()->active()->create([
        'opening_points' => 30,
        'countdown_duration_seconds' => 120,
    ]);

    $this->service->placeBid($this->user, $auction, 30);

    Queue::assertPushed(CloseAuctionJob::class);
});

test('CloseAuctionJob is not dispatched when auction has not yet triggered', function () {
    $auction = Auction::factory()->active()->create([
        'opening_points' => 1000,
        'countdown_duration_seconds' => 120,
    ]);

    $this->service->placeBid($this->user, $auction, 10);

    Queue::assertNotPushed(CloseAuctionJob::class);
});

test('records a bid_debit PointTransaction', function () {
    $this->service->placeBid($this->user, $this->auction, 50);

    $transaction = $this->user->pointTransactions()
        ->where('type', TransactionType::BID_DEBIT)
        ->first();

    expect($transaction)->not->toBeNull();
    expect((float) $transaction->amount)->toBe(50.0);
});

test('accepts bids on triggered auctions', function () {
    $auction = Auction::factory()->triggered()->create();

    $this->service->placeBid($this->user, $auction, 10);

    expect(Bid::where('auction_id', $auction->id)->count())->toBe(1);
});

test('winning recalculation uses the most recent bid when a user places multiple bids', function () {
    $this->service->placeBid($this->user, $this->auction, 10);
    $secondBid = $this->service->placeBid($this->user, $this->auction, 10);

    $winningBid = Bid::where('auction_id', $this->auction->id)
        ->where('is_winning', true)
        ->first();

    expect($winningBid->id)->toBe($secondBid->id);
});
