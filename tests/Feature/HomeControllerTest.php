<?php

use App\Enums\AuctionStatus;
use App\Http\Controllers\HomeController;
use App\Models\Auction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

/**
 * @return array<string, mixed>
 */
function mapAuctionForTest(Auction $auction): array
{
    $controller = app(HomeController::class);
    $method = new ReflectionMethod($controller, 'mapAuction');

    return $method->invoke($controller, $auction);
}

test('mapAuction returns bid payload with frontend status codes', function (AuctionStatus $status, int $expectedStatus) {
    $auction = match ($status) {
        AuctionStatus::ACTIVE => Auction::factory()->active()->create(),
        AuctionStatus::TRIGGERED => Auction::factory()->triggered()->create(),
        AuctionStatus::CLOSED => Auction::factory()->closed()->create(),
        AuctionStatus::DRAFT => Auction::factory()->draft()->create(),
    };

    $result = mapAuctionForTest($auction->fresh());

    expect($result)
        ->toHaveKeys(['id', 'name', 'status', 'opening_points', 'current_points', 'expires_at'])
        ->and($result['status'])->toBe($expectedStatus);
})->with([
    'active' => [AuctionStatus::ACTIVE, 0],
    'triggered' => [AuctionStatus::TRIGGERED, 1],
    'closed' => [AuctionStatus::CLOSED, 2],
    'draft' => [AuctionStatus::DRAFT, 0],
]);

test('homepage passes navbar search query to inertia', function () {
    $this->get('/?search=Gucci')
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Home/Index')
            ->where('search', 'Gucci'));
});

test('auction search scope filters by name category or description', function () {
    Auction::factory()->active()->create([
        'name' => 'Gucci Leather Bag',
        'category' => 'Fashion',
    ]);

    Auction::factory()->active()->create([
        'name' => 'Samsung Galaxy Phone',
        'category' => 'Electronics',
    ]);

    $results = Auction::query()
        ->whereIn('status', [AuctionStatus::ACTIVE, AuctionStatus::TRIGGERED])
        ->search('Gucci')
        ->pluck('name');

    expect($results)->toHaveCount(1)->toContain('Gucci Leather Bag');
});

test('homepage category bids respect search keyword', function () {
    Auction::factory()->active()->create([
        'name' => 'Gucci Leather Bag',
        'category' => 'Fashion',
    ]);

    Auction::factory()->active()->create([
        'name' => 'Samsung Galaxy Phone',
        'category' => 'Electronics',
    ]);

    $controller = app(HomeController::class);
    $method = new ReflectionMethod($controller, 'resolveCategoryBids');
    $categoryBids = $method->invoke($controller, 'Gucci');

    expect($categoryBids)
        ->toHaveKey('Fashion')
        ->not->toHaveKey('Electronics')
        ->and($categoryBids['Fashion'])->toHaveCount(1)
        ->and($categoryBids['Fashion'][0]['name'])->toBe('Gucci Leather Bag');
});
