<?php

namespace App\Jobs;

use App\Enums\AuctionStatus;
use App\Models\Auction;
use App\Models\User;
use App\Notifications\AuctionWon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;

class CloseAuctionJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly int $auctionId) {}

    public function handle(): void
    {
        DB::transaction(function () {
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
                $winner?->notify(new AuctionWon($auction));
            }
        });
    }
}
