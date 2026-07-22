<?php

namespace App\Services;

use App\Enums\ActivityType;
use App\Enums\AuctionStatus;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Events\AuctionTriggeredEvent;
use App\Events\BidPlacedEvent;
use App\Jobs\CloseAuctionJob;
use App\Models\Auction;
use App\Models\Bid;
use App\Models\PointTransaction;
use App\Models\User;
use App\Notifications\AuctionTriggered;
use App\Notifications\BidPlaced;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class BiddingService
{
    public function __construct(
        private readonly ActivityService $activityService,
        private readonly AchievementService $achievementService,
        private readonly AuctionTimelineService $timelineService,
    ) {}

    /**
     * Place a bid on an auction inside a fully serialised DB transaction.
     */
    public function placeBid(User $user, Auction $auction, int $points): Bid
    {
        $bid = DB::transaction(function () use ($user, $auction, $points) {
            $auction = Auction::lockForUpdate()->findOrFail($auction->id);

            if (! in_array($auction->status, [AuctionStatus::ACTIVE, AuctionStatus::TRIGGERED])) {
                throw new InvalidArgumentException('Bids can only be placed on active or triggered auctions.');
            }

            $minBid = (int) config('points.min_bid_increment');
            if ($points < $minBid) {
                throw new InvalidArgumentException("Bid amount must be at least {$minBid} points.");
            }

            $user = User::lockForUpdate()->findOrFail($user->id);

            if ($user->points_balance < $points) {
                throw new InvalidArgumentException('Insufficient points balance.');
            }

            $previousWinnerId = $this->timelineService->winningUserIdForAuction($auction->id);

            $user->points_balance -= $points;
            $user->save();

            $bid = Bid::create([
                'auction_id' => $auction->id,
                'user_id' => $user->id,
                'amount' => $points,
                'is_winning' => false,
            ]);

            $this->recalculateIsWinning($auction->id);

            $newWinnerId = $this->timelineService->winningUserIdForAuction($auction->id);

            $wasActive = $auction->status === AuctionStatus::ACTIVE;

            $auction->increment('current_points', $points);
            $auction->increment('bid_count');
            $auction->refresh();

            $justTriggered = false;

            if ($wasActive && $auction->current_points >= $auction->opening_points) {
                $auction->status = AuctionStatus::TRIGGERED;
                $auction->triggered_at = now();
                $auction->expires_at = now()->addSeconds($auction->countdown_duration_seconds);
                $auction->save();

                CloseAuctionJob::dispatch($auction->id)->delay($auction->countdown_duration_seconds);

                $justTriggered = true;
            }

            PointTransaction::create([
                'user_id' => $user->id,
                'type' => TransactionType::BID_DEBIT,
                'amount' => $points,
                'exchange_rate' => 1.0,
                'status' => TransactionStatus::COMPLETED,
                'metadata' => [
                    'auction_id' => $auction->id,
                    'bid_id' => $bid->id,
                ],
            ]);

            $user->notify(new BidPlaced($bid, $auction));

            if ($justTriggered) {
                $bidderIds = Bid::where('auction_id', $auction->id)
                    ->distinct()
                    ->pluck('user_id');

                $bidders = User::whereIn('id', $bidderIds)->get();

                foreach ($bidders as $bidder) {
                    $bidder->notify(new AuctionTriggered($auction));
                }
            }

            $winningBid = Bid::where('auction_id', $auction->id)
                ->where('is_winning', true)
                ->first();

            DB::afterCommit(function () use ($auction, $bid, $user, $justTriggered, $winningBid, $previousWinnerId, $newWinnerId): void {
                $this->timelineService->recordBidPlaced($auction, $bid, $user);
                $this->timelineService->detectAndRecordLeaderChange($auction, $previousWinnerId, $newWinnerId);

                if ($justTriggered) {
                    $auction->refresh();
                    $this->timelineService->recordAuctionTriggered($auction);
                }

                BidPlacedEvent::dispatch(
                    $auction->id,
                    $auction->current_points,
                    $auction->bid_count,
                    $winningBid?->user_id,
                );

                if ($justTriggered) {
                    AuctionTriggeredEvent::dispatch(
                        $auction->id,
                        $auction->expires_at->toISOString(),
                        $auction->status->value,
                    );
                }
            });

            return $bid;
        });

        $this->activityService->log(
            ActivityType::BID_PLACED,
            $user,
            $auction,
            ['amount' => $bid->amount, 'bid_id' => $bid->id],
        );

        $this->achievementService->evaluateAfterBid($user->fresh(), $bid, $auction);

        return $bid;
    }

    private function recalculateIsWinning(int $auctionId): void
    {
        Bid::where('auction_id', $auctionId)->update(['is_winning' => false]);

        $leader = DB::table('bids')
            ->where('auction_id', $auctionId)
            ->selectRaw('user_id, SUM(amount) as total, MAX(created_at) as last_bid_at, MAX(id) as last_bid_id')
            ->groupBy('user_id')
            ->orderByDesc('total')
            ->orderBy('last_bid_at', 'asc')
            ->orderBy('last_bid_id', 'asc')
            ->first();

        if ($leader !== null) {
            Bid::where('auction_id', $auctionId)
                ->where('user_id', $leader->user_id)
                ->orderByDesc('created_at')
                ->first()
                ?->update(['is_winning' => true]);
        }
    }
}
