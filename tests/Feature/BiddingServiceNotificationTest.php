<?php

use App\Models\Auction;
use App\Models\User;
use App\Notifications\AuctionTriggered;
use App\Notifications\BidPlaced;
use App\Services\BiddingService;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(LazilyRefreshDatabase::class);

beforeEach(function () {
    Notification::fake();
    $this->service = app(BiddingService::class);
    $this->user = User::factory()->create(['points_balance' => 10000]);
    $this->auction = Auction::factory()->active()->create(['opening_points' => 500]);
});

test('placing a bid queues a BidPlaced notification for the bidding user', function () {
    $this->service->placeBid($this->user, $this->auction, 50);

    Notification::assertSentTo($this->user, BidPlaced::class);
});

test('BidPlaced notification contains correct data', function () {
    $this->service->placeBid($this->user, $this->auction, 50);

    Notification::assertSentTo(
        $this->user,
        BidPlaced::class,
        function (BidPlaced $notification) {
            $data = $notification->toDatabase($this->user);

            return $data['type'] === 'bid_placed'
                && $data['auction_id'] === $this->auction->id
                && $data['amount'] === 50;
        }
    );
});

test('BidPlaced notification is not sent when bid is rejected', function () {
    $poorUser = User::factory()->create(['points_balance' => 5]);

    expect(fn () => $this->service->placeBid($poorUser, $this->auction, 50))
        ->toThrow(InvalidArgumentException::class);

    Notification::assertNothingSent();
});

test('crossing opening_points sends AuctionTriggered to all bidders', function () {
    $userA = User::factory()->create(['points_balance' => 10000]);
    $userB = User::factory()->create(['points_balance' => 10000]);

    $auction = Auction::factory()->active()->create(['opening_points' => 20]);

    // First bid: doesn't trigger yet
    $this->service->placeBid($userA, $auction, 10);
    Notification::assertNotSentTo($userA, AuctionTriggered::class);

    // Second bid: crosses threshold — both bidders should be notified
    $this->service->placeBid($userB, $auction, 10);

    Notification::assertSentTo($userA, AuctionTriggered::class);
    Notification::assertSentTo($userB, AuctionTriggered::class);
});

test('AuctionTriggered notification contains expires_at', function () {
    $auction = Auction::factory()->active()->create([
        'opening_points' => 10,
        'countdown_duration_seconds' => 120,
    ]);

    $this->service->placeBid($this->user, $auction, 10);

    Notification::assertSentTo(
        $this->user,
        AuctionTriggered::class,
        function (AuctionTriggered $notification) {
            $data = $notification->toDatabase($this->user);

            return $data['type'] === 'auction_triggered'
                && isset($data['expires_at']);
        }
    );
});

test('AuctionTriggered is not sent when auction has not yet triggered', function () {
    $auction = Auction::factory()->active()->create(['opening_points' => 1000]);

    $this->service->placeBid($this->user, $auction, 10);

    Notification::assertNotSentTo($this->user, AuctionTriggered::class);
});
