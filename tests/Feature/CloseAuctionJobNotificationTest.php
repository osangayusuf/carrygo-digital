<?php

use App\Jobs\CloseAuctionJob;
use App\Models\Auction;
use App\Models\Bid;
use App\Models\User;
use App\Notifications\AuctionWon;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(LazilyRefreshDatabase::class);

test('CloseAuctionJob sends AuctionWon notification to the winner', function () {
    Notification::fake();

    $winner = User::factory()->create(['points_balance' => 10000]);
    $auction = Auction::factory()->triggered()->create();

    Bid::factory()->create([
        'auction_id' => $auction->id,
        'user_id' => $winner->id,
        'amount' => 100,
        'is_winning' => true,
    ]);

    (new CloseAuctionJob($auction->id))->handle();

    Notification::assertSentTo($winner, AuctionWon::class);
});

test('AuctionWon notification contains correct auction data', function () {
    Notification::fake();

    $winner = User::factory()->create(['points_balance' => 10000]);
    $auction = Auction::factory()->triggered()->create(['name' => 'Test Item']);

    Bid::factory()->create([
        'auction_id' => $auction->id,
        'user_id' => $winner->id,
        'amount' => 100,
        'is_winning' => true,
    ]);

    (new CloseAuctionJob($auction->id))->handle();

    Notification::assertSentTo(
        $winner,
        AuctionWon::class,
        function (AuctionWon $notification) use ($auction) {
            $data = $notification->toDatabase($notification->auction);

            return $data['type'] === 'auction_won'
                && $data['auction_id'] === $auction->id;
        }
    );
});

test('CloseAuctionJob sends no AuctionWon notification when there is no winner', function () {
    Notification::fake();

    $auction = Auction::factory()->triggered()->create();

    // No bids → no is_winning = true
    (new CloseAuctionJob($auction->id))->handle();

    Notification::assertNothingSent();
});

test('CloseAuctionJob skips already closed auctions without notifying', function () {
    Notification::fake();

    $winner = User::factory()->create();
    $auction = Auction::factory()->closed()->withWinner($winner)->create();

    (new CloseAuctionJob($auction->id))->handle();

    Notification::assertNothingSent();
});
