<?php

namespace App\Services;

use App\Enums\AuctionStatus;
use App\Models\Auction;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;

class AuctionService
{
    public function __construct(private readonly AuctionTimelineService $timelineService) {}

    /**
     * Create a new draft auction, storing the uploaded image.
     *
     * @param array{
     *     category: string,
     *     name: string,
     *     price: float|int,
     *     description: string,
     *     opening_points: int,
     *     countdown_duration_seconds: int,
     *     image: UploadedFile,
     *     external_url?: string|null
     * } $data
     */
    public function create(array $data): Auction
    {
        $imagePath = $data['image']->store('auctions', 'public');

        return Auction::create([
            'category' => $data['category'],
            'name' => $data['name'],
            'price' => $data['price'],
            'description' => $data['description'],
            'opening_points' => $data['opening_points'],
            'countdown_duration_seconds' => $data['countdown_duration_seconds'],
            'image' => $imagePath,
            'external_url' => $data['external_url'] ?? null,
            'status' => AuctionStatus::DRAFT,
            'current_points' => 0,
            'bid_count' => 0,
        ]);
    }

    /**
     * Update an auction's attributes. Only draft auctions may be edited.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(Auction $auction, array $data): Auction
    {
        if ($auction->status !== AuctionStatus::DRAFT) {
            throw new InvalidArgumentException('Only draft auctions can be updated.');
        }

        if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
            if ($auction->image) {
                Storage::disk('public')->delete($auction->getRawOriginal('image'));
            }

            $data['image'] = $data['image']->store('auctions', 'public');
        }

        $auction->fill($data)->save();

        return $auction;
    }

    /**
     * Publish a draft auction, transitioning it to active status.
     * Validates that the auction has an image, a positive opening_points,
     * and a positive countdown_duration_seconds.
     */
    public function publish(Auction $auction): Auction
    {
        if ($auction->status !== AuctionStatus::DRAFT) {
            throw new InvalidArgumentException('Only draft auctions can be published.');
        }

        if (empty($auction->image)) {
            throw new InvalidArgumentException('Auction must have an image before publishing.');
        }

        if ($auction->opening_points <= 0) {
            throw new InvalidArgumentException('Auction opening_points must be greater than zero.');
        }

        if ($auction->countdown_duration_seconds <= 0) {
            throw new InvalidArgumentException('Auction countdown_duration_seconds must be greater than zero.');
        }

        $auction->status = AuctionStatus::ACTIVE;
        $auction->save();

        return $auction;
    }

    /**
     * Delete a draft auction and its associated image.
     * Only draft auctions may be deleted.
     */
    public function delete(Auction $auction): void
    {
        if ($auction->status !== AuctionStatus::DRAFT) {
            throw new InvalidArgumentException('Only draft auctions can be deleted.');
        }

        if ($auction->image) {
            Storage::disk('public')->delete($auction->getRawOriginal('image'));
        }

        $auction->delete();
    }

    /**
     * Manually close an auction (admin action). Sets status to closed and
     * resolves the winner from the current is_winning bid.
     */
    public function manualClose(Auction $auction): Auction
    {
        if ($auction->status === AuctionStatus::CLOSED) {
            throw new InvalidArgumentException('Auction is already closed.');
        }

        $winningBid = $auction->bids()->where('is_winning', true)->first();

        $auction->status = AuctionStatus::CLOSED;
        $auction->winner_id = $winningBid?->user_id;
        $auction->save();

        $this->timelineService->recordAuctionClosed($auction);

        return $auction;
    }
}
