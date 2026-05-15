<?php

namespace App\Services;

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
    /**
     * Place a bid on an auction inside a fully serialised DB transaction.
     *
     * Steps (from §5 of the bidding platform plan):
     *  1. Lock the auction row for update.
     *  2. Validate auction status is active or triggered.
     *  3. Validate amount >= min_bid_increment.
     *  4. Re-fetch and lock the user row; validate sufficient points_balance.
     *  5. Debit points_balance.
     *  6. Create Bid (is_winning = false initially).
     *  7. Recalculate is_winning across all bids for this auction.
     *  8. Increment current_points and bid_count.
     *  9. If current_points just crossed opening_points → trigger countdown.
     * 10. Record bid_debit PointTransaction.
     */
    public function placeBid(User $user, Auction $auction, int $amount): Bid
    {
        return DB::transaction(function () use ($user, $auction, $amount) {
            $auction = Auction::lockForUpdate()->findOrFail($auction->id);

            if (! in_array($auction->status, [AuctionStatus::ACTIVE, AuctionStatus::TRIGGERED])) {
                throw new InvalidArgumentException('Bids can only be placed on active or triggered auctions.');
            }

            $minBid = (int) config('points.min_bid_increment');
            if ($amount < $minBid) {
                throw new InvalidArgumentException("Bid amount must be at least {$minBid} points.");
            }

            $user = User::lockForUpdate()->findOrFail($user->id);

            if ($user->points_balance < $amount) {
                throw new InvalidArgumentException('Insufficient points balance.');
            }

            $user->points_balance -= $amount;
            $user->save();

            $bid = Bid::create([
                'auction_id' => $auction->id,
                'user_id' => $user->id,
                'amount' => $amount,
                'is_winning' => false,
            ]);

            $this->recalculateIsWinning($auction->id);

            $wasActive = $auction->status === AuctionStatus::ACTIVE;

            $auction->increment('current_points', $amount);
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

            $pointTransaction = PointTransaction::create([
                'user_id' => $user->id,
                'type' => TransactionType::BID_DEBIT,
                'amount' => $amount,
                'exchange_rate' => 1.0,
                'status' => TransactionStatus::COMPLETED,
                'metadata' => [
                    'auction_id' => $auction->id,
                    'bid_id' => $bid->id,
                ],
            ]);

            // Notify the bidding user — afterCommit ensures this only queues after the transaction commits.
            $user->notify(new BidPlaced($bid, $auction));

            // If the auction just triggered, notify all distinct bidders (including the current user).
            if ($justTriggered) {
                $bidderIds = Bid::where('auction_id', $auction->id)
                    ->distinct()
                    ->pluck('user_id');

                $bidders = User::whereIn('id', $bidderIds)->get();

                foreach ($bidders as $bidder) {
                    $bidder->notify(new AuctionTriggered($auction));
                }
            }

            // Resolve the current winning user for the broadcast payload.
            $winningBid = Bid::where('auction_id', $auction->id)
                ->where('is_winning', true)
                ->first();

            // Dispatch broadcast events after the transaction commits so the DB state is visible to clients.
            DB::afterCommit(function () use ($auction, $justTriggered, $winningBid) {
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
    }

    /**
     * Recalculate is_winning for all bids on a given auction.
     *
     * Algorithm:
     *  - GROUP BY user_id, SUM(amount) to get each user's cumulative total.
     *  - Find the MAX cumulative total.
     *  - If exactly one user holds that MAX, set is_winning = true on their
     *    most recent bid and false on all others.
     *  - If two or more users are tied at the MAX, all is_winning remain false.
     */
    private function recalculateIsWinning(int $auctionId): void
    {
        $totals = DB::table('bids')
            ->where('auction_id', $auctionId)
            ->selectRaw('user_id, SUM(amount) as total')
            ->groupBy('user_id')
            ->orderByDesc('total')
            ->get();

        // Reset all bids for this auction
        Bid::where('auction_id', $auctionId)->update(['is_winning' => false]);

        if ($totals->isEmpty()) {
            return;
        }

        $maxTotal = $totals->first()->total;
        $topUsers = $totals->filter(fn ($row) => $row->total == $maxTotal);

        if ($topUsers->count() === 1) {
            $winnerId = $topUsers->first()->user_id;

            Bid::where('auction_id', $auctionId)
                ->where('user_id', $winnerId)
                ->orderByDesc('created_at')
                ->first()
                ?->update(['is_winning' => true]);
        }
    }
}
