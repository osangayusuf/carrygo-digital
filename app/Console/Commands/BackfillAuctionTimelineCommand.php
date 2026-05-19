<?php

namespace App\Console\Commands;

use App\Models\Auction;
use App\Services\AuctionTimelineService;
use Illuminate\Console\Command;

class BackfillAuctionTimelineCommand extends Command
{
    protected $signature = 'auctions:backfill-timeline
                            {auction? : Optional auction ID to backfill only one auction}
                            {--fresh : Delete existing timeline entries before backfilling}';

    protected $description = 'Backfill auction timeline entries from historical bids and auction state';

    public function handle(AuctionTimelineService $timelineService): int
    {
        $auctionId = $this->argument('auction');
        $fresh = (bool) $this->option('fresh');

        $query = Auction::query()->with('bids');

        if ($auctionId !== null) {
            $auction = $query->find($auctionId);

            if (! $auction) {
                $this->error("Auction [{$auctionId}] not found.");

                return self::FAILURE;
            }

            $created = $timelineService->backfillAuction($auction, $fresh);
            $this->info("Auction [{$auction->id}]: created {$created} timeline entries.");

            return self::SUCCESS;
        }

        $total = 0;
        $skipped = 0;

        $query->orderBy('id')->chunkById(50, function ($auctions) use ($timelineService, $fresh, &$total, &$skipped): void {
            foreach ($auctions as $auction) {
                $created = $timelineService->backfillAuction($auction, $fresh);

                if ($created === 0) {
                    $skipped++;
                } else {
                    $total += $created;
                    $this->line("Auction [{$auction->id}]: {$created} entries");
                }
            }
        });

        $this->info("Done. Created {$total} entries. Skipped {$skipped} auctions with existing timelines.");

        return self::SUCCESS;
    }
}
