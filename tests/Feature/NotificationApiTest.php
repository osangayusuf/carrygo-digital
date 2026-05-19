<?php

use App\Models\Auction;
use App\Models\Bid;
use App\Models\User;
use App\Notifications\BidPlaced;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
});

test('GET /notifications returns unread notifications for auth user', function () {
    $this->user->notify(new BidPlaced(
        bid: Bid::factory()->create(['user_id' => $this->user->id]),
        auction: Auction::factory()->active()->create(),
    ));

    $this->actingAs($this->user)
        ->getJson('/notifications')
        ->assertSuccessful()
        ->assertJsonCount(1)
        ->assertJsonFragment(['type' => 'bid_placed']);
});

test('GET /notifications does not return read notifications', function () {
    $this->user->notify(new BidPlaced(
        bid: Bid::factory()->create(['user_id' => $this->user->id]),
        auction: Auction::factory()->active()->create(),
    ));

    $this->user->notifications()->update(['read_at' => now()]);

    $this->actingAs($this->user)
        ->getJson('/notifications')
        ->assertSuccessful()
        ->assertJsonCount(0);
});

test('GET /notifications requires authentication', function () {
    $this->getJson('/notifications')->assertUnauthorized();
});

test('PATCH /notifications/{id}/read marks one notification as read', function () {
    $this->user->notify(new BidPlaced(
        bid: Bid::factory()->create(['user_id' => $this->user->id]),
        auction: Auction::factory()->active()->create(),
    ));

    $notification = $this->user->unreadNotifications()->first();

    $this->actingAs($this->user)
        ->patchJson("/notifications/{$notification->id}/read")
        ->assertSuccessful();

    expect($this->user->unreadNotifications()->count())->toBe(0);
});

test('PATCH /notifications/{id}/read requires authentication', function () {
    $this->patchJson('/notifications/fake-id/read')->assertUnauthorized();
});

test('PATCH /notifications/read-all marks all unread notifications as read', function () {
    $auction = Auction::factory()->active()->create();

    $this->user->notify(new BidPlaced(
        bid: Bid::factory()->create(['user_id' => $this->user->id]),
        auction: $auction,
    ));
    $this->user->notify(new BidPlaced(
        bid: Bid::factory()->create(['user_id' => $this->user->id]),
        auction: $auction,
    ));

    expect($this->user->unreadNotifications()->count())->toBe(2);

    $this->actingAs($this->user)
        ->patchJson('/notifications/read-all')
        ->assertSuccessful();

    expect($this->user->fresh()->unreadNotifications()->count())->toBe(0);
});

test('PATCH /notifications/read-all requires authentication', function () {
    $this->patchJson('/notifications/read-all')->assertUnauthorized();
});

test('GET /notifications/feed returns navbar notification shape', function () {
    $auction = Auction::factory()->active()->create();

    $this->user->notify(new BidPlaced(
        bid: Bid::factory()->create(['user_id' => $this->user->id]),
        auction: $auction,
    ));

    $this->actingAs($this->user)
        ->getJson('/notifications/feed')
        ->assertSuccessful()
        ->assertJsonCount(1)
        ->assertJsonFragment([
            'url' => '/auctions/'.$auction->id,
            'read_at' => null,
        ]);
});

test('GET /notifications/feed requires authentication', function () {
    $this->getJson('/notifications/feed')->assertUnauthorized();
});

test('shared inertia notifications include url and read_at', function () {
    $auction = Auction::factory()->active()->create();

    $this->user->notify(new BidPlaced(
        bid: Bid::factory()->create(['user_id' => $this->user->id]),
        auction: $auction,
    ));

    $this->actingAs($this->user)
        ->get(route('home'))
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->has('notifications', 1)
            ->where('notifications.0.url', '/auctions/'.$auction->id)
            ->where('notifications.0.read_at', null)
        );
});

test('user cannot access another user\'s notifications', function () {
    $otherUser = User::factory()->create();
    $otherUser->notify(new BidPlaced(
        bid: Bid::factory()->create(['user_id' => $otherUser->id]),
        auction: Auction::factory()->active()->create(),
    ));

    $notification = $otherUser->notifications()->first();

    $this->actingAs($this->user)
        ->patchJson("/notifications/{$notification->id}/read")
        ->assertNotFound();
});
