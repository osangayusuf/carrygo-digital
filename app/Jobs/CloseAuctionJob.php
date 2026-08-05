<?php

namespace App\Jobs;

use App\Enums\AuctionStatus;
use App\Events\AuctionClosedEvent;
use App\Models\Auction;
use App\Models\User;
use App\Notifications\AuctionWon;
use App\Notifications\ReviewPrompt;
use App\Services\AchievementService;
use App\Services\AuctionTimelineService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;

class CloseAuctionJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly int $auctionId) {}

    public function handle(?AuctionTimelineService $timelineService = null, ?AchievementService $achievementService = null): void
    {
        $timelineService ??= app(AuctionTimelineService::class);
        $achievementService ??= app(AchievementService::class);

        DB::transaction(function () use ($timelineService, $achievementService): void {
            $auction = Auction::lockForUpdate()->find($this->auctionId);

            if (! $auction || $auction->status !== AuctionStatus::TRIGGERED) {
                return;
            }

            // The countdown may have been extended by an admin after this job was
            // queued, making this job stale. Never close ahead of the auction's
            // current expires_at — just drop this run. AuctionService queues a
            // fresh job for the new deadline whenever the countdown changes, and
            // ReconcileAuctionsCommand is the backstop.
            if ($auction->expires_at !== null && $auction->expires_at->isFuture()) {
                return;
            }

            $winningBid = $auction->bids()->where('is_winning', true)->first();

            $auction->status = AuctionStatus::CLOSED;
            $auction->winner_id = $winningBid?->user_id;
            $auction->save();

            if ($auction->winner_id) {
                $winner = User::find($auction->winner_id);

                if ($winner !== null) {
                    $winner->notify(new AuctionWon($auction));
                    $winner->notify(new ReviewPrompt($auction));
                    $achievementService->evaluateAfterWin($winner);
                }
            }

            DB::afterCommit(function () use ($auction, $timelineService): void {
                $timelineService->recordAuctionClosed($auction);

                AuctionClosedEvent::dispatch(
                    $auction->id,
                    $auction->winner_id,
                    $auction->status->value,
                );
            });
        });
    }
}
