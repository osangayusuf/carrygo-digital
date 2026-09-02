<?php

use App\Models\Auction;
use App\Models\Bid;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::firstOrCreate(['name' => 'admin']);
    Role::firstOrCreate(['name' => 'user']);
});

test('guests are redirected to the login page when accessing admin winners page', function () {
    $response = $this->get(route('admin.winners.index'));
    $response->assertRedirect(route('login'));
});

test('non-admin users are redirected away from admin winners page', function () {
    $user = User::factory()->create([
        'email_verified_at' => now(),
    ]);
    $user->assignRole('user');
    $this->actingAs($user);

    $response = $this->get(route('admin.winners.index'));
    $response->assertRedirect(route('home'));
});

test('admin users can access admin winners page and view stats & winners', function () {
    $admin = User::factory()->create([
        'email_verified_at' => now(),
    ]);
    $admin->assignRole('admin');
    $this->actingAs($admin);

    $winner = User::factory()->create([
        'name' => 'Ada Winner',
        'email' => 'ada@example.com',
        'phone' => '08012345678',
    ]);

    $auction = Auction::factory()->closed()->create([
        'name' => 'Luxury Watch',
        'category' => 'Electronics',
        'price' => 150000,
        'winner_id' => $winner->id,
        'bid_count' => 8,
    ]);

    Bid::factory()->forAuction($auction)->forUser($winner)->winning()->create(['amount' => 500]);
    Bid::factory()->forAuction($auction)->forUser($winner)->create(['amount' => 100]);

    Review::factory()->create([
        'auction_id' => $auction->id,
        'user_id' => $winner->id,
        'rating' => 5,
        'comment' => 'Received the item in good condition!',
        'photos' => ['reviews/photo1.jpg'],
    ]);

    // Live auction & closed auction without winner shouldn't be included
    Auction::factory()->active()->create(['name' => 'Live Auction']);
    Auction::factory()->closed()->create(['name' => 'No Winner', 'winner_id' => null]);

    $response = $this->get(route('admin.winners.index'));
    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Admin/Winners/Index')
        ->has('winners.data', 1)
        ->where('winners.data.0.auction_name', 'Luxury Watch')
        ->where('winners.data.0.winner.name', 'Ada Winner')
        ->where('winners.data.0.winner.email', 'ada@example.com')
        ->where('winners.data.0.winner.phone', '08012345678')
        ->where('winners.data.0.winning_pts', 500)
        ->where('winners.data.0.total_pts_bid', 600)
        ->where('winners.data.0.review.rating', 5)
        ->where('stats.total_winners_count', 1)
        ->where('stats.total_winning_points', 500)
        ->where('stats.total_retail_value', 150000)
        ->where('stats.total_reviews_count', 1)
        ->has('categories')
    );
});

test('admin winners page filters by search keyword across auction and winner fields', function () {
    $admin = User::factory()->create(['email_verified_at' => now()]);
    $admin->assignRole('admin');
    $this->actingAs($admin);

    $winner1 = User::factory()->create(['name' => 'Chidi Winner', 'email' => 'chidi@example.com', 'phone' => '08099998888']);
    $auction1 = Auction::factory()->closed()->create(['name' => 'iPhone 15 Pro', 'winner_id' => $winner1->id]);
    Bid::factory()->forAuction($auction1)->forUser($winner1)->winning()->create();

    $winner2 = User::factory()->create(['name' => 'Fatima Gold', 'email' => 'fatima@example.com', 'phone' => '08011112222']);
    $auction2 = Auction::factory()->closed()->create(['name' => 'Sony PlayStation 5', 'winner_id' => $winner2->id]);
    Bid::factory()->forAuction($auction2)->forUser($winner2)->winning()->create();

    // Search by winner phone
    $response = $this->get(route('admin.winners.index', ['search' => '08099998888']));
    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->has('winners.data', 1)
        ->where('winners.data.0.auction_name', 'iPhone 15 Pro')
    );

    // Search by auction title
    $response = $this->get(route('admin.winners.index', ['search' => 'PlayStation']));
    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->has('winners.data', 1)
        ->where('winners.data.0.auction_name', 'Sony PlayStation 5')
    );
});

test('admin winners page filters by category', function () {
    $admin = User::factory()->create(['email_verified_at' => now()]);
    $admin->assignRole('admin');
    $this->actingAs($admin);

    $winner = User::factory()->create();
    $auction1 = Auction::factory()->closed()->create(['category' => 'Gadgets', 'winner_id' => $winner->id]);
    Bid::factory()->forAuction($auction1)->forUser($winner)->winning()->create();

    $auction2 = Auction::factory()->closed()->create(['category' => 'Fashion', 'winner_id' => $winner->id]);
    Bid::factory()->forAuction($auction2)->forUser($winner)->winning()->create();

    $response = $this->get(route('admin.winners.index', ['category' => 'Gadgets']));
    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->has('winners.data', 1)
        ->where('winners.data.0.category', 'Gadgets')
    );
});

test('admin winners page filters by review and media proof status', function () {
    $admin = User::factory()->create(['email_verified_at' => now()]);
    $admin->assignRole('admin');
    $this->actingAs($admin);

    $winner1 = User::factory()->create();
    $auction1 = Auction::factory()->closed()->create(['name' => 'With Media Review', 'winner_id' => $winner1->id]);
    Bid::factory()->forAuction($auction1)->forUser($winner1)->winning()->create();
    Review::factory()->create([
        'auction_id' => $auction1->id,
        'user_id' => $winner1->id,
        'photos' => ['reviews/proof.png'],
    ]);

    $winner2 = User::factory()->create();
    $auction2 = Auction::factory()->closed()->create(['name' => 'Text Only Review', 'winner_id' => $winner2->id]);
    Bid::factory()->forAuction($auction2)->forUser($winner2)->winning()->create();
    Review::factory()->create([
        'auction_id' => $auction2->id,
        'user_id' => $winner2->id,
        'photos' => null,
        'video' => null,
    ]);

    $winner3 = User::factory()->create();
    $auction3 = Auction::factory()->closed()->create(['name' => 'No Review', 'winner_id' => $winner3->id]);
    Bid::factory()->forAuction($auction3)->forUser($winner3)->winning()->create();

    // Filter with_media
    $response = $this->get(route('admin.winners.index', ['review_status' => 'with_media']));
    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->has('winners.data', 1)
        ->where('winners.data.0.auction_name', 'With Media Review')
    );

    // Filter without_review
    $response = $this->get(route('admin.winners.index', ['review_status' => 'without_review']));
    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->has('winners.data', 1)
        ->where('winners.data.0.auction_name', 'No Review')
    );
});

test('admin winners page includes disabled closed auctions with winners', function () {
    $admin = User::factory()->create(['email_verified_at' => now()]);
    $admin->assignRole('admin');
    $this->actingAs($admin);

    $winner = User::factory()->create();
    Auction::factory()->closed()->create([
        'name' => 'Disabled But Won',
        'enabled' => false,
        'winner_id' => $winner->id,
    ]);

    $response = $this->get(route('admin.winners.index'));
    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->has('winners.data', 1)
        ->where('winners.data.0.auction_name', 'Disabled But Won')
    );
});
