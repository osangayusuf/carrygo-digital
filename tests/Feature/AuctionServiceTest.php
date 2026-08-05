<?php

use App\Enums\AuctionStatus;
use App\Enums\AuctionTimelineEntryType;
use App\Events\AuctionCountdownUpdatedEvent;
use App\Jobs\CloseAuctionJob;
use App\Models\Auction;
use App\Models\AuctionTimelineEntry;
use App\Models\User;
use App\Services\AuctionService;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

uses(LazilyRefreshDatabase::class);

beforeEach(function () {
    Storage::fake('public');
    $this->service = app(AuctionService::class);
});

test('creates a draft auction', function () {
    $data = [
        'category' => 'Electronics',
        'name' => 'Test Auction',
        'price' => 25000.00,
        'description' => 'A test auction description.',
        'opening_points' => 500,
        'countdown_duration_seconds' => 120,
        'image' => UploadedFile::fake()->image('item.jpg'),
    ];

    $auction = $this->service->create($data);

    expect($auction)
        ->toBeInstanceOf(Auction::class)
        ->status->toBe(AuctionStatus::DRAFT)
        ->name->toBe('Test Auction')
        ->price->toBe('25000.00')
        ->opening_points->toBe(500)
        ->current_points->toBe(0)
        ->bid_count->toBe(0);

    Storage::disk('public')->assertExists($auction->getRawOriginal('image'));
});

test('updates a draft auction', function () {
    $auction = Auction::factory()->draft()->create(['name' => 'Old Name', 'price' => 1000.00]);

    $this->service->update($auction, ['name' => 'New Name', 'price' => 1500.00, 'opening_points' => 999]);

    expect($auction->refresh())
        ->name->toBe('New Name')
        ->price->toBe('1500.00')
        ->opening_points->toBe(999);
});

test('replaces image when updating a draft auction', function () {
    $auction = Auction::factory()->draft()->create(['image' => 'auctions/old.jpg']);
    Storage::disk('public')->put('auctions/old.jpg', 'fake');

    $newImage = UploadedFile::fake()->image('new.jpg');
    $this->service->update($auction, ['image' => $newImage]);

    Storage::disk('public')->assertMissing('auctions/old.jpg');
    Storage::disk('public')->assertExists($auction->refresh()->getRawOriginal('image'));
});

test('cannot update a non-draft auction', function () {
    $auction = Auction::factory()->active()->create();

    expect(fn () => $this->service->update($auction, ['name' => 'Changed']))
        ->toThrow(InvalidArgumentException::class, 'Only draft auctions can be updated.');
});

test('publishes a valid draft auction', function () {
    $auction = Auction::factory()->draft()->create([
        'image' => 'auctions/item.jpg',
        'opening_points' => 100,
        'countdown_duration_seconds' => 60,
    ]);

    $this->service->publish($auction);

    expect($auction->refresh()->status)->toBe(AuctionStatus::ACTIVE);
});

test('cannot publish a non-draft auction', function () {
    $auction = Auction::factory()->active()->create();

    expect(fn () => $this->service->publish($auction))
        ->toThrow(InvalidArgumentException::class, 'Only draft auctions can be published.');
});

test('cannot publish a draft without an image', function () {
    $auction = Auction::factory()->draft()->create(['image' => null]);

    expect(fn () => $this->service->publish($auction))
        ->toThrow(InvalidArgumentException::class, 'Auction must have an image before publishing.');
});

test('cannot publish a draft with opening_points of zero', function () {
    $auction = Auction::factory()->draft()->create([
        'image' => 'auctions/item.jpg',
        'opening_points' => 0,
    ]);

    expect(fn () => $this->service->publish($auction))
        ->toThrow(InvalidArgumentException::class, 'Auction opening_points must be greater than zero.');
});

test('cannot publish a draft with countdown_duration_seconds of zero', function () {
    $auction = Auction::factory()->draft()->create([
        'image' => 'auctions/item.jpg',
        'opening_points' => 100,
        'countdown_duration_seconds' => 0,
    ]);

    expect(fn () => $this->service->publish($auction))
        ->toThrow(InvalidArgumentException::class, 'Auction countdown_duration_seconds must be greater than zero.');
});

test('manually closes an active auction', function () {
    $auction = Auction::factory()->active()->create();

    $this->service->manualClose($auction);

    expect($auction->refresh())
        ->status->toBe(AuctionStatus::CLOSED)
        ->winner_id->toBeNull();
});

test('manually close resolves winner from is_winning bid', function () {
    $winner = User::factory()->create();
    $auction = Auction::factory()->triggered()->create();
    $auction->bids()->create(['user_id' => $winner->id, 'amount' => 100, 'is_winning' => true]);

    $this->service->manualClose($auction);

    expect($auction->refresh()->winner_id)->toBe($winner->id);
});

test('deletes a draft auction and removes its image', function () {
    $auction = Auction::factory()->draft()->create(['image' => 'auctions/to-delete.jpg']);
    Storage::disk('public')->put('auctions/to-delete.jpg', 'fake');

    $this->service->delete($auction);

    expect(Auction::find($auction->id))->toBeNull();
    Storage::disk('public')->assertMissing('auctions/to-delete.jpg');
});

test('cannot delete a non-draft auction', function () {
    $auction = Auction::factory()->active()->create();

    expect(fn () => $this->service->delete($auction))
        ->toThrow(InvalidArgumentException::class, 'Only draft auctions can be deleted.');
});

test('cannot manually close an already closed auction', function () {
    $auction = Auction::factory()->closed()->create();

    expect(fn () => $this->service->manualClose($auction))
        ->toThrow(InvalidArgumentException::class, 'Auction is already closed.');
});

test('disables an auction regardless of status', function (string $state) {
    $auction = Auction::factory()->{$state}()->create(['enabled' => true]);

    $this->service->disable($auction);

    expect($auction->refresh()->enabled)->toBeFalse();
})->with(['draft', 'active', 'triggered', 'closed']);

test('enables an auction regardless of status', function (string $state) {
    $auction = Auction::factory()->{$state}()->create(['enabled' => false]);

    $this->service->enable($auction);

    expect($auction->refresh()->enabled)->toBeTrue();
})->with(['draft', 'active', 'triggered', 'closed']);

test('toggles event flag on regardless of status', function (string $state) {
    $auction = Auction::factory()->{$state}()->create(['event' => false]);

    $this->service->toggleEvent($auction);

    expect($auction->refresh()->event)->toBeTrue();
})->with(['draft', 'active', 'triggered', 'closed']);

test('toggles event flag off regardless of status', function (string $state) {
    $auction = Auction::factory()->{$state}()->create(['event' => true]);

    $this->service->toggleEvent($auction);

    expect($auction->refresh()->event)->toBeFalse();
})->with(['draft', 'active', 'triggered', 'closed']);

test('rebases expires_at from triggered_at when countdown is extended on a triggered auction', function () {
    Queue::fake();
    Event::fake([AuctionCountdownUpdatedEvent::class]);

    $triggeredAt = now()->subSeconds(40);

    $auction = Auction::factory()->triggered()->create([
        'countdown_duration_seconds' => 60,
        'triggered_at' => $triggeredAt,
        'expires_at' => $triggeredAt->copy()->addSeconds(60),
    ]);

    $this->service->update($auction, ['countdown_duration_seconds' => 90]);

    // Rebased on triggered_at, not on now(): elapsed time is preserved.
    expect($auction->refresh()->expires_at->timestamp)
        ->toBe($triggeredAt->copy()->addSeconds(90)->timestamp);

    Queue::assertPushed(CloseAuctionJob::class);
    Event::assertDispatched(AuctionCountdownUpdatedEvent::class);
});

test('rebases expires_at when countdown is shortened on a triggered auction', function () {
    Queue::fake();
    Event::fake([AuctionCountdownUpdatedEvent::class]);

    $triggeredAt = now()->subSeconds(20);

    $auction = Auction::factory()->triggered()->create([
        'countdown_duration_seconds' => 300,
        'triggered_at' => $triggeredAt,
        'expires_at' => $triggeredAt->copy()->addSeconds(300),
    ]);

    $this->service->update($auction, ['countdown_duration_seconds' => 120]);

    expect($auction->refresh()->expires_at->timestamp)
        ->toBe($triggeredAt->copy()->addSeconds(120)->timestamp);
});

test('clamps expires_at to now when the new countdown has already elapsed', function () {
    Queue::fake();
    Event::fake([AuctionCountdownUpdatedEvent::class]);

    $triggeredAt = now()->subSeconds(200);

    $auction = Auction::factory()->triggered()->create([
        'countdown_duration_seconds' => 300,
        'triggered_at' => $triggeredAt,
        'expires_at' => $triggeredAt->copy()->addSeconds(300),
    ]);

    $this->service->update($auction, ['countdown_duration_seconds' => 30]);

    expect($auction->refresh()->expires_at->isFuture())->toBeFalse();

    Queue::assertPushed(CloseAuctionJob::class);
});

test('does not touch expires_at when a triggered auction is updated without a countdown change', function () {
    Queue::fake();

    $auction = Auction::factory()->triggered()->create([
        'countdown_duration_seconds' => 60,
    ]);

    $originalExpiry = $auction->expires_at->timestamp;

    $this->service->update($auction, ['name' => 'Renamed item']);

    expect($auction->refresh())
        ->name->toBe('Renamed item')
        ->and($auction->expires_at->timestamp)->toBe($originalExpiry);

    Queue::assertNothingPushed();
});

test('records a timeline entry when the countdown is adjusted', function () {
    Queue::fake();

    $auction = Auction::factory()->triggered()->create([
        'countdown_duration_seconds' => 60,
    ]);

    $this->service->update($auction, ['countdown_duration_seconds' => 180]);

    $entry = AuctionTimelineEntry::where('auction_id', $auction->id)
        ->where('type', AuctionTimelineEntryType::CountdownAdjusted)
        ->first();

    expect($entry)->not->toBeNull()
        ->and($entry->payload['previous_duration_seconds'])->toBe(60)
        ->and($entry->payload['duration_seconds'])->toBe(180);
});
