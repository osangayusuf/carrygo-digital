<?php

use App\Enums\AuctionStatus;
use App\Jobs\CloseAuctionJob;
use App\Models\Auction;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

uses(LazilyRefreshDatabase::class);

beforeEach(function () {
    Storage::fake('public');
    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');
});

test('admin can view auctions list with prices', function () {
    $auction = Auction::factory()->draft()->create(['price' => 45000.00]);

    $response = $this->actingAs($this->admin)
        ->get(route('admin.auctions.index'));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Admin/Auctions/Index')
        ->has('auctions.data', 1)
        ->where('auctions.data.0.id', $auction->id)
        ->where('auctions.data.0.price', '45000.00')
    );
});

test('admin can create draft auction with price', function () {
    $response = $this->actingAs($this->admin)
        ->post(route('admin.auctions.store'), [
            'category' => 'Electronics',
            'name' => 'Brand New Laptop',
            'price' => 75000.50,
            'description' => 'A top tier high performance laptop.',
            'opening_points' => 100,
            'countdown_duration_seconds' => 60,
            'image' => UploadedFile::fake()->image('laptop.jpg'),
        ]);

    $response->assertRedirect(route('admin.auctions.index'));

    $this->assertDatabaseHas('auctions', [
        'name' => 'Brand New Laptop',
        'price' => 75000.50,
        'category' => 'Electronics',
    ]);
});

test('price field is required for creating draft auction', function () {
    $response = $this->actingAs($this->admin)
        ->post(route('admin.auctions.store'), [
            'category' => 'Electronics',
            'name' => 'Brand New Laptop',
            'description' => 'A top tier high performance laptop.',
            'opening_points' => 100,
            'countdown_duration_seconds' => 60,
            'image' => UploadedFile::fake()->image('laptop.jpg'),
        ]);

    $response->assertSessionHasErrors(['price']);
});

test('admin can update draft auction price', function () {
    $auction = Auction::factory()->draft()->create([
        'price' => 1000.00,
    ]);

    $response = $this->actingAs($this->admin)
        ->put(route('admin.auctions.update', $auction), [
            'price' => 2000.00,
        ]);

    $response->assertRedirect(route('admin.auctions.index'));

    expect($auction->refresh()->price)->toBe('2000.00');
});

test('admin can update draft auction when image is null', function () {
    $auction = Auction::factory()->draft()->create([
        'name' => 'Original Name',
    ]);

    $response = $this->actingAs($this->admin)
        ->put(route('admin.auctions.update', $auction), [
            'name' => 'Updated Name',
            'image' => null,
        ]);

    $response->assertRedirect(route('admin.auctions.index'));

    expect($auction->refresh()->name)->toBe('Updated Name');
});

test('admin can create draft auction with external url', function () {
    $response = $this->actingAs($this->admin)
        ->post(route('admin.auctions.store'), [
            'category' => 'Electronics',
            'name' => 'Brand New Laptop',
            'price' => 75000.50,
            'description' => 'A top tier high performance laptop.',
            'opening_points' => 100,
            'countdown_duration_seconds' => 60,
            'image' => UploadedFile::fake()->image('laptop.jpg'),
            'external_url' => 'https://example.com/laptop',
        ]);

    $response->assertRedirect(route('admin.auctions.index'));

    $this->assertDatabaseHas('auctions', [
        'name' => 'Brand New Laptop',
        'external_url' => 'https://example.com/laptop',
    ]);
});

test('admin can create and immediately publish an auction', function () {
    $response = $this->actingAs($this->admin)
        ->post(route('admin.auctions.store'), [
            'category' => 'Electronics',
            'name' => 'Published Laptop',
            'price' => 75000.50,
            'description' => 'A published laptop.',
            'opening_points' => 100,
            'countdown_duration_seconds' => 60,
            'image' => UploadedFile::fake()->image('laptop.jpg'),
            'publish' => true,
        ]);

    $response->assertRedirect(route('admin.auctions.index'));

    $this->assertDatabaseHas('auctions', [
        'name' => 'Published Laptop',
        'status' => AuctionStatus::ACTIVE->value,
    ]);
});

test('admin can update and publish an auction', function () {
    $auction = Auction::factory()->draft()->create([
        'name' => 'Draft Laptop',
    ]);

    $response = $this->actingAs($this->admin)
        ->put(route('admin.auctions.update', $auction), [
            'name' => 'Updated Published Laptop',
            'publish' => true,
        ]);

    $response->assertRedirect(route('admin.auctions.index'));

    $this->assertDatabaseHas('auctions', [
        'id' => $auction->id,
        'name' => 'Updated Published Laptop',
        'status' => AuctionStatus::ACTIVE->value,
    ]);
});

test('admin can update active auction', function () {
    $auction = Auction::factory()->active()->create([
        'name' => 'Active Item',
        'opening_points' => 500,
        'current_points' => 100,
    ]);

    $response = $this->actingAs($this->admin)
        ->put(route('admin.auctions.update', $auction), [
            'name' => 'Updated Active Item',
            'opening_points' => 400,
        ]);

    $response->assertRedirect(route('admin.auctions.index'));

    expect($auction->refresh()->name)->toBe('Updated Active Item');
    expect($auction->opening_points)->toBe(400);
    expect($auction->status)->toBe(AuctionStatus::ACTIVE);
});

test('admin can update triggered auction', function () {
    $auction = Auction::factory()->triggered()->create([
        'name' => 'Triggered Item',
    ]);

    $response = $this->actingAs($this->admin)
        ->put(route('admin.auctions.update', $auction), [
            'name' => 'Updated Triggered Item',
        ]);

    $response->assertRedirect(route('admin.auctions.index'));

    expect($auction->refresh()->name)->toBe('Updated Triggered Item');
    expect($auction->status)->toBe(AuctionStatus::TRIGGERED);
});

test('admin cannot update closed auction', function () {
    $auction = Auction::factory()->closed()->create([
        'name' => 'Closed Item',
    ]);

    $this->withoutExceptionHandling();

    expect(fn () => $this->actingAs($this->admin)
        ->put(route('admin.auctions.update', $auction), [
            'name' => 'Try Update Closed Item',
        ])
    )->toThrow(InvalidArgumentException::class, 'Closed auctions cannot be updated.');
});

test('updating active auction opening_points below current_points triggers countdown', function () {
    Queue::fake();

    $auction = Auction::factory()->active()->create([
        'opening_points' => 500,
        'current_points' => 300,
    ]);

    $response = $this->actingAs($this->admin)
        ->put(route('admin.auctions.update', $auction), [
            'opening_points' => 250,
        ]);

    $response->assertRedirect(route('admin.auctions.index'));

    expect($auction->refresh()->status)->toBe(AuctionStatus::TRIGGERED);
    expect($auction->triggered_at)->not->toBeNull();
    expect($auction->expires_at)->not->toBeNull();

    Queue::assertPushed(CloseAuctionJob::class);
});

test('admin can disable an active auction', function () {
    $auction = Auction::factory()->active()->create(['enabled' => true]);

    $response = $this->actingAs($this->admin)
        ->patch(route('admin.auctions.toggleEnabled', $auction));

    $response->assertRedirect(route('admin.auctions.index'));

    expect($auction->refresh()->enabled)->toBeFalse();
});

test('admin can re-enable a disabled auction', function () {
    $auction = Auction::factory()->active()->create(['enabled' => false]);

    $response = $this->actingAs($this->admin)
        ->patch(route('admin.auctions.toggleEnabled', $auction));

    $response->assertRedirect(route('admin.auctions.index'));

    expect($auction->refresh()->enabled)->toBeTrue();
});

test('admin auctions index still lists disabled auctions', function () {
    $auction = Auction::factory()->active()->create(['enabled' => false, 'name' => 'Disabled Item']);

    $response = $this->actingAs($this->admin)
        ->get(route('admin.auctions.index'));

    $response->assertInertia(fn (Assert $page) => $page
        ->component('Admin/Auctions/Index')
        ->has('auctions.data', 1)
        ->where('auctions.data.0.id', $auction->id)
    );
});

test('admin can filter auctions by enabled state', function () {
    Auction::factory()->active()->create(['enabled' => true, 'name' => 'Enabled Item']);
    $disabled = Auction::factory()->active()->create(['enabled' => false, 'name' => 'Disabled Item']);

    $response = $this->actingAs($this->admin)
        ->get(route('admin.auctions.index', ['enabled' => 'disabled']));

    $response->assertInertia(fn (Assert $page) => $page
        ->component('Admin/Auctions/Index')
        ->has('auctions.data', 1)
        ->where('auctions.data.0.id', $disabled->id)
    );
});

test('admin can filter auctions by event flag', function () {
    Auction::factory()->active()->create(['event' => false, 'name' => 'Regular Item']);
    $event = Auction::factory()->active()->create(['event' => true, 'name' => 'Event Item']);

    $response = $this->actingAs($this->admin)
        ->get(route('admin.auctions.index', ['event' => 'event']));

    $response->assertInertia(fn (Assert $page) => $page
        ->component('Admin/Auctions/Index')
        ->has('auctions.data', 1)
        ->where('auctions.data.0.id', $event->id)
    );
});

test('admin can mark an auction as an event item', function () {
    $auction = Auction::factory()->active()->create(['event' => false]);

    $response = $this->actingAs($this->admin)
        ->patch(route('admin.auctions.toggleEvent', $auction));

    $response->assertRedirect(route('admin.auctions.index'));

    expect($auction->refresh()->event)->toBeTrue();
});

test('admin can unmark an auction as an event item', function () {
    $auction = Auction::factory()->active()->create(['event' => true]);

    $response = $this->actingAs($this->admin)
        ->patch(route('admin.auctions.toggleEvent', $auction));

    $response->assertRedirect(route('admin.auctions.index'));

    expect($auction->refresh()->event)->toBeFalse();
});
