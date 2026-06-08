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

    public function handle(AuctionTimelineService $timelineService, AchievementService $achievementService): void
    {
        DB::transaction(function () use ($timelineService, $achievementService): void {
            $auction = Auction::lockForUpdate()->find($this->auctionId);

            if (! $auction || $auction->status !== AuctionStatus::TRIGGERED) {
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
