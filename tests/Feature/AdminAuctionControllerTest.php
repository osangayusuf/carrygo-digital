<?php

use App\Enums\AuctionStatus;
use App\Models\Auction;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
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
