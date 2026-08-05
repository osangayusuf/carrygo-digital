<?php

namespace App\Services;

use App\Enums\AuctionStatus;
use App\Events\AuctionCountdownUpdatedEvent;
use App\Events\AuctionTriggeredEvent;
use App\Jobs\CloseAuctionJob;
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
     * Update an auction's attributes. Draft, active, and triggered auctions may be edited.
     * Closed auctions cannot be updated.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(Auction $auction, array $data): Auction
    {
        if ($auction->status === AuctionStatus::CLOSED) {
            throw new InvalidArgumentException('Closed auctions cannot be updated.');
        }

        if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
            if ($auction->image) {
                Storage::disk('public')->delete($auction->getRawOriginal('image'));
            }

            $data['image'] = $data['image']->store('auctions', 'public');
        }

        $wasActive = $auction->status === AuctionStatus::ACTIVE;
        $wasTriggered = $auction->status === AuctionStatus::TRIGGERED;
        $previousDuration = $auction->countdown_duration_seconds;
        $previousExpiresAt = $auction->expires_at?->toISOString();

        $auction->fill($data)->save();

        if ($wasTriggered && $auction->countdown_duration_seconds !== $previousDuration) {
            $this->rebaseCountdown($auction, $previousExpiresAt, $previousDuration);

            return $auction;
        }

        if ($wasActive && $auction->current_points >= $auction->opening_points) {
            $auction->status = AuctionStatus::TRIGGERED;
            $auction->triggered_at = now();
            $auction->expires_at = now()->addSeconds($auction->countdown_duration_seconds);
            $auction->save();

            CloseAuctionJob::dispatch($auction->id)->delay($auction->countdown_duration_seconds);

            $this->timelineService->recordAuctionTriggered($auction);

            AuctionTriggeredEvent::dispatch(
                $auction->id,
                $auction->expires_at->toISOString(),
                $auction->status->value,
            );
        }

        return $auction;
    }

    /**
     * Re-anchor a triggered auction's expiry after its countdown duration changed.
     *
     * The new expiry is rebased on triggered_at rather than on "now", so elapsed
     * time is preserved: changing 60s -> 90s adds exactly 30s to the deadline
     * regardless of when the edit lands. If the new duration has already fully
     * elapsed, the expiry is clamped to now and the auction closes immediately.
     *
     * A fresh CloseAuctionJob is dispatched for the new deadline. Any previously
     * queued job is left in place but is harmless — CloseAuctionJob re-checks
     * expires_at and drops the run if the auction has not yet expired.
     */
    private function rebaseCountdown(Auction $auction, ?string $previousExpiresAt, int $previousDuration): void
    {
        $anchor = $auction->triggered_at ?? now();

        $newExpiresAt = $anchor->copy()->addSeconds($auction->countdown_duration_seconds);

        if ($newExpiresAt->isPast()) {
            $newExpiresAt = now();
        }

        $auction->expires_at = $newExpiresAt;
        $auction->save();

        $delay = max(0, (int) ceil(now()->diffInSeconds($newExpiresAt, false)));

        CloseAuctionJob::dispatch($auction->id)->delay($delay);

        $this->timelineService->recordCountdownAdjusted($auction, $previousExpiresAt, $previousDuration);

        AuctionCountdownUpdatedEvent::dispatch(
            $auction->id,
            $auction->expires_at->toISOString(),
            $auction->status->value,
        );
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

    /**
     * Enable an auction, making it visible to regular users again.
     * Works regardless of the auction's status.
     */
    public function enable(Auction $auction): Auction
    {
        $auction->enabled = true;
        $auction->save();

        return $auction;
    }

    /**
     * Disable an auction, hiding it from regular users and blocking new bids.
     * Works regardless of the auction's status.
     */
    public function disable(Auction $auction): Auction
    {
        $auction->enabled = false;
        $auction->save();

        return $auction;
    }

    /**
     * Flip whether an auction is flagged as an event item.
     * Works regardless of the auction's status.
     */
    public function toggleEvent(Auction $auction): Auction
    {
        $auction->event = ! $auction->event;
        $auction->save();

        return $auction;
    }
}
