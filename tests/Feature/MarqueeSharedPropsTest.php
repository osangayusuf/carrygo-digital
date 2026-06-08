<?php

use App\Enums\AuctionStatus;
use App\Models\Auction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

test('marquee items are shared to frontend via inertia', function () {
    $this->get('/')
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->has('marquee_items')
            ->where('marquee_items.0.text', 'Bid on premium luxury items with points!')
            ->where('marquee_items.1.text', 'Complete tasks in the Task Center to earn free points!')
            ->where('marquee_items.2.text', 'Check the leaderboard to see top bidders of the week!')
            ->where('marquee_items.3.text', 'New auction drops every Monday!')
        );
});

test('marquee items include live, active, and closed auctions with masked winner phones', function () {
    // 1. Create a live (triggered) auction
    Auction::factory()->create([
        'name' => 'Rolex Daytona',
        'price' => 12000000.00,
        'opening_points' => 120000,
        'current_points' => 125000,
        'status' => AuctionStatus::TRIGGERED,
        'expires_at' => now()->addHours(2),
    ]);

    // 2. Create an active auction
    Auction::factory()->create([
        'name' => 'Gucci Bag',
        'price' => 500000.00,
        'opening_points' => 5000,
        'current_points' => 2500,
        'status' => AuctionStatus::ACTIVE,
        'bid_count' => 12,
    ]);

    // 3. Create a closed auction with a winner
    $winner = User::factory()->create([
        'phone' => '+2348123456789',
    ]);
    Auction::factory()->create([
        'name' => 'Prada Heels',
        'status' => AuctionStatus::CLOSED,
        'winner_id' => $winner->id,
    ]);

    $this->get('/')
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->has('marquee_items', 5)
            ->where('marquee_items.0.text', 'LIVE AUCTION: Rolex Daytona @ ₦12,000,000')
            ->where('marquee_items.1.text', 'Gucci Bag – 50% progress!')
            ->where('marquee_items.2.text', 'Gucci Bag – 12 bids so far')
            ->where('marquee_items.3.text', 'Winner: +234 812***6789 won Prada Heels!')
            ->where('marquee_items.4.text', 'New auction drops every Monday!')
        );
});
