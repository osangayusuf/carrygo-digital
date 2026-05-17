<?php

use App\Enums\AuctionStatus;
use App\Http\Controllers\HomeController;
use App\Models\Auction;
use Illuminate\Foundation\Testing\RefreshDatabase;

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
