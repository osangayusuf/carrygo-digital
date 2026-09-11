<?php

use App\Enums\AuctionStatus;
use App\Models\Auction;
use App\Models\Bid;
use App\Models\Review;
use App\Models\User;
use App\Services\AuctionListingService;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(LazilyRefreshDatabase::class);

test('only participants of a closed auction can submit a review', function () {
    $auction = Auction::factory()->create(['status' => AuctionStatus::CLOSED]);
    $participant = User::factory()->create();
    $nonParticipant = User::factory()->create();

    // Make the user a participant by creating a bid
    Bid::factory()->create(['auction_id' => $auction->id, 'user_id' => $participant->id]);

    // Guest cannot submit
    $this->post(route('auctions.reviews.store', $auction), [
        'rating' => 5,
        'comment' => 'Great product!',
    ])->assertRedirect(route('login'));

    // Non-participant gets 403
    $this->actingAs($nonParticipant)
        ->post(route('auctions.reviews.store', $auction), [
            'rating' => 5,
            'comment' => 'Great product!',
        ])->assertStatus(403);

    // Participant can submit
    $this->actingAs($participant)
        ->post(route('auctions.reviews.store', $auction), [
            'rating' => 5,
            'comment' => 'Great product!',
        ])->assertRedirect();

    $this->assertDatabaseHas('reviews', [
        'auction_id' => $auction->id,
        'user_id' => $participant->id,
        'rating' => 5,
        'comment' => 'Great product!',
        'is_visible' => false,
    ]);
});

test('cannot submit a review for non-closed auctions', function () {
    $auction = Auction::factory()->create(['status' => AuctionStatus::ACTIVE]);
    $participant = User::factory()->create();

    Bid::factory()->create(['auction_id' => $auction->id, 'user_id' => $participant->id]);

    $this->actingAs($participant)
        ->post(route('auctions.reviews.store', $auction), [
            'rating' => 5,
            'comment' => 'Great product!',
        ])->assertSessionHasErrors('comment');
});

test('participant can submit multiple reviews for the same auction', function () {
    $auction = Auction::factory()->create(['status' => AuctionStatus::CLOSED]);
    $participant = User::factory()->create();

    Bid::factory()->create(['auction_id' => $auction->id, 'user_id' => $participant->id]);
    Review::factory()->create(['auction_id' => $auction->id, 'user_id' => $participant->id]);

    $this->actingAs($participant)
        ->post(route('auctions.reviews.store', $auction), [
            'rating' => 5,
            'comment' => 'Great product, following up again!',
        ])->assertRedirect();

    expect(Review::where('auction_id', $auction->id)->where('user_id', $participant->id)->count())->toBe(2);
});

test('winner can submit multiple reviews for the same auction', function () {
    $winner = User::factory()->create();
    $auction = Auction::factory()->create([
        'status' => AuctionStatus::CLOSED,
        'winner_id' => $winner->id,
    ]);

    Bid::factory()->create(['auction_id' => $auction->id, 'user_id' => $winner->id]);
    Review::factory()->create(['auction_id' => $auction->id, 'user_id' => $winner->id]);

    $this->actingAs($winner)
        ->post(route('auctions.reviews.store', $auction), [
            'rating' => 4,
            'comment' => 'Following up with another review!',
        ])->assertRedirect();

    expect(Review::where('auction_id', $auction->id)->where('user_id', $winner->id)->count())->toBe(2);
});

test('regular participant cannot upload photos or videos', function () {
    $auction = Auction::factory()->create([
        'status' => AuctionStatus::CLOSED,
        'winner_id' => User::factory()->create()->id, // Winner is someone else
    ]);
    $participant = User::factory()->create();

    Bid::factory()->create(['auction_id' => $auction->id, 'user_id' => $participant->id]);

    Storage::fake('public');

    $this->actingAs($participant)
        ->post(route('auctions.reviews.store', $auction), [
            'rating' => 5,
            'comment' => 'Great product!',
            'photos' => [UploadedFile::fake()->image('delivery.jpg')],
        ])->assertSessionHasErrors('comment');
});

test('winner can upload photos and videos', function () {
    $winner = User::factory()->create();
    $auction = Auction::factory()->create([
        'status' => AuctionStatus::CLOSED,
        'winner_id' => $winner->id,
    ]);

    Bid::factory()->create(['auction_id' => $auction->id, 'user_id' => $winner->id]);

    Storage::fake('public');

    $photo = UploadedFile::fake()->image('proof.jpg');
    $video = UploadedFile::fake()->create('proof.mp4', 500, 'video/mp4');

    $this->actingAs($winner)
        ->post(route('auctions.reviews.store', $auction), [
            'rating' => 5,
            'comment' => 'Excellent delivery, highly recommended!',
            'photos' => [$photo],
            'video' => $video,
        ])->assertRedirect();

    $review = Review::where('auction_id', $auction->id)->where('user_id', $winner->id)->first();
    expect($review)->not->toBeNull();
    expect($review->is_visible)->toBeFalse();
    expect($review->photos)->toHaveCount(1);
    expect($review->video)->not->toBeNull();

    Storage::disk('public')->assertExists($review->photos[0]);
    Storage::disk('public')->assertExists($review->video);
});

test('validates social platform and handle', function () {
    $auction = Auction::factory()->create(['status' => AuctionStatus::CLOSED]);
    $participant = User::factory()->create();
    Bid::factory()->create(['auction_id' => $auction->id, 'user_id' => $participant->id]);

    $this->actingAs($participant)
        ->post(route('auctions.reviews.store', $auction), [
            'rating' => 5,
            'comment' => 'Great product!',
            'social_platform' => 'Instagram',
            'social_handle' => '@john_doe',
        ])->assertRedirect();

    $this->assertDatabaseHas('reviews', [
        'auction_id' => $auction->id,
        'user_id' => $participant->id,
        'social_platform' => 'Instagram',
        'social_handle' => '@john_doe',
        'is_visible' => false,
    ]);
});

test('social platform and handle are mutually required', function () {
    $auction = Auction::factory()->create(['status' => AuctionStatus::CLOSED]);
    $participant = User::factory()->create();
    Bid::factory()->create(['auction_id' => $auction->id, 'user_id' => $participant->id]);

    $this->actingAs($participant)
        ->post(route('auctions.reviews.store', $auction), [
            'rating' => 5,
            'comment' => 'Great product!',
            'social_platform' => 'Instagram',
        ])->assertSessionHasErrors(['social_handle']);

    $this->actingAs($participant)
        ->post(route('auctions.reviews.store', $auction), [
            'rating' => 5,
            'comment' => 'Great product!',
            'social_handle' => '@john_doe',
        ])->assertSessionHasErrors(['social_platform']);
});

test('social platform must be one of allowed values', function () {
    $auction = Auction::factory()->create(['status' => AuctionStatus::CLOSED]);
    $participant = User::factory()->create();
    Bid::factory()->create(['auction_id' => $auction->id, 'user_id' => $participant->id]);

    $this->actingAs($participant)
        ->post(route('auctions.reviews.store', $auction), [
            'rating' => 5,
            'comment' => 'Great product!',
            'social_platform' => 'MySpace',
            'social_handle' => '@john_doe',
        ])->assertSessionHasErrors(['social_platform']);
});

test('home controller returns reviews with winner_id in bid data', function () {
    $winner = User::factory()->create();
    $auction = Auction::factory()->create([
        'status' => AuctionStatus::CLOSED,
        'winner_id' => $winner->id,
    ]);
    Review::factory()->create([
        'auction_id' => $auction->id,
        'user_id' => $winner->id,
        'is_visible' => true,
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->loadDeferredProps(fn ($reload) => $reload
                ->has('reviews', 1)
                ->where('reviews.0.bid.winner_id', $winner->id)
            )
        );
});

test('auction listing service mapAuction helper includes winner_id', function () {
    $winner = User::factory()->create();
    $auction = Auction::factory()->create([
        'winner_id' => $winner->id,
    ]);

    $service = app(AuctionListingService::class);
    $mapped = $service->mapAuction($auction);

    expect($mapped)->toHaveKey('winner_id', $winner->id);
});
