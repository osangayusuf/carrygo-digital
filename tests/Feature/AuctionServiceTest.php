<?php

use App\Enums\AuctionStatus;
use App\Models\Auction;
use App\Models\User;
use App\Services\AuctionService;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
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
        ->opening_points->toBe(500)
        ->current_points->toBe(0)
        ->bid_count->toBe(0);

    Storage::disk('public')->assertExists($auction->image);
});

test('updates a draft auction', function () {
    $auction = Auction::factory()->draft()->create(['name' => 'Old Name']);

    $this->service->update($auction, ['name' => 'New Name', 'opening_points' => 999]);

    expect($auction->refresh())
        ->name->toBe('New Name')
        ->opening_points->toBe(999);
});

test('replaces image when updating a draft auction', function () {
    $auction = Auction::factory()->draft()->create(['image' => 'auctions/old.jpg']);
    Storage::disk('public')->put('auctions/old.jpg', 'fake');

    $newImage = UploadedFile::fake()->image('new.jpg');
    $this->service->update($auction, ['image' => $newImage]);

    Storage::disk('public')->assertMissing('auctions/old.jpg');
    Storage::disk('public')->assertExists($auction->refresh()->image);
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
