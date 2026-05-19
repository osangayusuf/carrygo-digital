<?php

use App\Enums\ActivityType;
use App\Enums\AuctionTimelineEntryType;
use App\Models\Auction;
use App\Models\AuctionTimelineEntry;
use App\Models\Bid;
use App\Models\User;
use App\Models\UserActivity;
use App\Services\BiddingService;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Queue;

uses(LazilyRefreshDatabase::class);

test('guest can fetch auction timeline', function () {
    $auction = Auction::factory()->active()->create();
    AuctionTimelineEntry::factory()->create([
        'auction_id' => $auction->id,
        'type' => AuctionTimelineEntryType::BidPlaced,
        'payload' => ['amount' => 50],
    ]);

    $this->getJson(route('auctions.timeline', $auction))
        ->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.type', 'bid_placed');
});

test('timeline masks bidder phone in message', function () {
    $user = User::factory()->create(['phone' => '08012345678']);
    $auction = Auction::factory()->active()->create();

    AuctionTimelineEntry::factory()->create([
        'auction_id' => $auction->id,
        'type' => AuctionTimelineEntryType::BidPlaced,
        'user_id' => $user->id,
        'payload' => ['amount' => 25],
    ]);

    $this->actingAs(User::factory()->create())
        ->getJson(route('auctions.timeline', $auction))
        ->assertSuccessful()
        ->assertJsonPath('data.0.actor_label', '080*****678');
});

test('placing a bid creates timeline and activity entries', function () {
    Queue::fake();

    $user = User::factory()->create(['points_balance' => 500]);
    $auction = Auction::factory()->active()->create(['opening_points' => 10_000]);

    app(BiddingService::class)->placeBid($user, $auction, 50);

    expect(AuctionTimelineEntry::where('auction_id', $auction->id)->where('type', AuctionTimelineEntryType::BidPlaced)->exists())->toBeTrue();

    expect(UserActivity::where('type', ActivityType::BID_PLACED->value)->where('user_id', $user->id)->exists())->toBeTrue();
});

test('triggering an auction adds auction_triggered timeline entry', function () {
    Queue::fake();

    $user = User::factory()->create(['points_balance' => 10_000]);
    $auction = Auction::factory()->active()->create(['opening_points' => 100]);

    app(BiddingService::class)->placeBid($user, $auction, 100);

    expect(AuctionTimelineEntry::where('auction_id', $auction->id)->where('type', AuctionTimelineEntryType::AuctionTriggered)->exists())->toBeTrue();
});

test('leader change is recorded when a higher bidder overtakes', function () {
    Queue::fake();

    $userA = User::factory()->create(['points_balance' => 10_000]);
    $userB = User::factory()->create(['points_balance' => 10_000]);
    $auction = Auction::factory()->active()->create(['opening_points' => 10_000]);

    app(BiddingService::class)->placeBid($userA, $auction, 50);
    app(BiddingService::class)->placeBid($userB, $auction, 100);

    expect(
        AuctionTimelineEntry::where('auction_id', $auction->id)
            ->where('type', AuctionTimelineEntryType::LeaderChanged)
            ->exists(),
    )->toBeTrue();
});

test('backfill command creates timeline from existing bids', function () {
    $auction = Auction::factory()->active()->create();
    $user = User::factory()->create();
    Bid::factory()->create([
        'auction_id' => $auction->id,
        'user_id' => $user->id,
        'amount' => 30,
    ]);

    $this->artisan('auctions:backfill-timeline', ['auction' => $auction->id])
        ->assertSuccessful();

    expect(AuctionTimelineEntry::where('auction_id', $auction->id)->count())->toBeGreaterThan(0);
});

test('backfill skips auctions that already have timeline unless fresh', function () {
    $auction = Auction::factory()->active()->create();
    AuctionTimelineEntry::factory()->create(['auction_id' => $auction->id]);

    $this->artisan('auctions:backfill-timeline', ['auction' => $auction->id])
        ->assertSuccessful();

    expect(AuctionTimelineEntry::where('auction_id', $auction->id)->count())->toBe(1);
});
