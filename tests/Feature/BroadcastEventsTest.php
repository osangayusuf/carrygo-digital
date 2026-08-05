<?php

use App\Enums\AuctionStatus;
use App\Events\AuctionClosedEvent;
use App\Events\AuctionCountdownUpdatedEvent;
use App\Events\AuctionTriggeredEvent;
use App\Events\BidPlacedEvent;
use App\Jobs\CloseAuctionJob;
use App\Models\Auction;
use App\Models\User;
use App\Services\BiddingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;

uses(RefreshDatabase::class);

// ──────────────────────────────────────────────────────────────────────────────
// BidPlacedEvent
// ──────────────────────────────────────────────────────────────────────────────

it('dispatches BidPlacedEvent after a valid bid is placed', function () {
    Event::fake([BidPlacedEvent::class, AuctionTriggeredEvent::class]);

    $user = User::factory()->create(['points_balance' => 1000]);
    $auction = Auction::factory()->active()->create(['opening_points' => 9999]);

    app(BiddingService::class)->placeBid($user, $auction, 10);

    Event::assertDispatched(BidPlacedEvent::class, function (BidPlacedEvent $event) use ($auction) {
        return $event->auctionId === $auction->id
            && $event->currentPoints === 10
            && $event->bidCount === 1;
    });
});

it('broadcasts BidPlacedEvent on the correct public auction channel', function () {
    $auction = Auction::factory()->active()->create(['opening_points' => 9999]);
    $user = User::factory()->create(['points_balance' => 1000]);

    $event = new BidPlacedEvent(
        auctionId: $auction->id,
        currentPoints: 10,
        bidCount: 1,
        winningUserId: $user->id,
    );

    expect($event->broadcastOn())->toHaveCount(1)
        ->and($event->broadcastOn()[0]->name)->toBe("auction.{$auction->id}");

    expect($event->broadcastWith())->toMatchArray([
        'current_points' => 10,
        'bid_count' => 1,
        'winning_user_id' => $user->id,
    ]);
});

// ──────────────────────────────────────────────────────────────────────────────
// AuctionTriggeredEvent
// ──────────────────────────────────────────────────────────────────────────────

it('dispatches AuctionTriggeredEvent when opening_points threshold is crossed', function () {
    Event::fake([BidPlacedEvent::class, AuctionTriggeredEvent::class]);

    $user = User::factory()->create(['points_balance' => 1000]);
    // Set opening_points to 10 so a single 10-point bid triggers it.
    $auction = Auction::factory()->active()->create([
        'opening_points' => 10,
        'countdown_duration_seconds' => 60,
    ]);

    app(BiddingService::class)->placeBid($user, $auction, 10);

    Event::assertDispatched(AuctionTriggeredEvent::class, function (AuctionTriggeredEvent $event) use ($auction) {
        return $event->auctionId === $auction->id
            && $event->status === AuctionStatus::TRIGGERED->value;
    });
});

it('does not dispatch AuctionTriggeredEvent when threshold is not crossed', function () {
    Event::fake([BidPlacedEvent::class, AuctionTriggeredEvent::class]);

    $user = User::factory()->create(['points_balance' => 1000]);
    $auction = Auction::factory()->active()->create(['opening_points' => 9999]);

    app(BiddingService::class)->placeBid($user, $auction, 10);

    Event::assertNotDispatched(AuctionTriggeredEvent::class);
});

it('broadcasts AuctionTriggeredEvent on the correct public auction channel', function () {
    $auction = Auction::factory()->triggered()->create();

    $event = new AuctionTriggeredEvent(
        auctionId: $auction->id,
        expiresAt: $auction->expires_at->toISOString(),
        status: AuctionStatus::TRIGGERED->value,
    );

    expect($event->broadcastOn())->toHaveCount(1)
        ->and($event->broadcastOn()[0]->name)->toBe("auction.{$auction->id}");

    expect($event->broadcastWith())->toMatchArray([
        'expires_at' => $auction->expires_at->toISOString(),
        'status' => 'triggered',
    ]);
});

// ──────────────────────────────────────────────────────────────────────────────
// AuctionClosedEvent
// ──────────────────────────────────────────────────────────────────────────────

it('dispatches AuctionClosedEvent when CloseAuctionJob runs', function () {
    Event::fake([AuctionClosedEvent::class]);

    $winner = User::factory()->create(['points_balance' => 1000]);
    $auction = Auction::factory()->triggered()->create(['countdown_duration_seconds' => 60]);

    // Manually set a winning bid so the job resolves a winner.
    $auction->bids()->create([
        'user_id' => $winner->id,
        'amount' => 100,
        'is_winning' => true,
    ]);

    (new CloseAuctionJob($auction->id))->handle();

    Event::assertDispatched(AuctionClosedEvent::class, function (AuctionClosedEvent $event) use ($auction, $winner) {
        return $event->auctionId === $auction->id
            && $event->winnerId === $winner->id
            && $event->status === AuctionStatus::CLOSED->value;
    });
});

it('dispatches AuctionClosedEvent with null winner_id when no bids were placed', function () {
    Event::fake([AuctionClosedEvent::class]);

    $auction = Auction::factory()->triggered()->create();

    (new CloseAuctionJob($auction->id))->handle();

    Event::assertDispatched(AuctionClosedEvent::class, function (AuctionClosedEvent $event) use ($auction) {
        return $event->auctionId === $auction->id
            && $event->winnerId === null;
    });
});

it('broadcasts AuctionClosedEvent on the correct public auction channel', function () {
    $auction = Auction::factory()->closed()->create();
    $winner = User::factory()->create();

    $event = new AuctionClosedEvent(
        auctionId: $auction->id,
        winnerId: $winner->id,
        status: AuctionStatus::CLOSED->value,
    );

    expect($event->broadcastOn())->toHaveCount(1)
        ->and($event->broadcastOn()[0]->name)->toBe("auction.{$auction->id}");

    expect($event->broadcastWith())->toMatchArray([
        'winner_id' => $winner->id,
        'status' => 'closed',
    ]);
});

// ---------------------------------------------------------------------------
// AuctionCountdownUpdatedEvent
// ---------------------------------------------------------------------------

it('broadcasts AuctionCountdownUpdatedEvent on the correct public auction channel', function () {
    $auction = Auction::factory()->triggered()->create();

    $event = new AuctionCountdownUpdatedEvent(
        $auction->id,
        $auction->expires_at->toISOString(),
        $auction->status->value,
    );

    expect($event->broadcastOn())->toHaveCount(1)
        ->and($event->broadcastOn()[0]->name)->toBe("auction.{$auction->id}")
        ->and($event->broadcastAs())->toBe('AuctionCountdownUpdated')
        ->and($event->broadcastWith())->toHaveKey('expires_at');
});
