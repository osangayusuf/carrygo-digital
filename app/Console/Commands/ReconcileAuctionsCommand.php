<?php

namespace App\Console\Commands;

use App\Enums\AuctionStatus;
use App\Models\Auction;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ReconcileAuctionsCommand extends Command
{
    protected $signature = 'auctions:reconcile';

    protected $description = 'Close triggered auctions past their deadline and reconcile bid counts.';

    public function handle(): int
    {
        $this->closeMissedAuctions();
        $this->reconcileBidCounts();

        return self::SUCCESS;
    }

    /**
     * Find any triggered auctions whose expires_at has passed and close them.
     * This is the fallback for CloseAuctionJob failures.
     */
    private function closeMissedAuctions(): void
    {
        $overdue = Auction::where('status', AuctionStatus::TRIGGERED)
            ->where('expires_at', '<=', now())
            ->get();

        foreach ($overdue as $auction) {
            DB::transaction(function () use ($auction) {
                $auction = Auction::lockForUpdate()->find($auction->id);

                if (! $auction || $auction->status !== AuctionStatus::TRIGGERED) {
                    return;
                }

                $winningBid = $auction->bids()->where('is_winning', true)->first();

                $auction->status = AuctionStatus::CLOSED;
                $auction->winner_id = $winningBid?->user_id;
                $auction->save();

                $this->line("Closed overdue auction #{$auction->id}.");
            });
        }
    }

    /**
     * Reconcile bid_count for all non-draft auctions against the actual
     * count of bids in the bids table.
     */
    private function reconcileBidCounts(): void
    {
        DB::statement('
            UPDATE auctions
            SET bid_count = (
                SELECT COUNT(*)
                FROM bids
                WHERE bids.auction_id = auctions.id
            )
            WHERE status != ?
        ', [AuctionStatus::DRAFT->value]);
    }
}
