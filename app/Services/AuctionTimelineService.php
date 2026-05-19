<?php

namespace App\Services;

use App\Enums\AuctionStatus;
use App\Enums\AuctionTimelineEntryType;
use App\Events\AuctionTimelineUpdatedEvent;
use App\Models\Auction;
use App\Models\AuctionTimelineEntry;
use App\Models\Bid;
use App\Models\User;
use App\Support\MsisdnMasker;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AuctionTimelineService
{
    public function record(
        Auction $auction,
        AuctionTimelineEntryType $type,
        ?User $user = null,
        ?Bid $bid = null,
        array $payload = [],
        ?\DateTimeInterface $occurredAt = null,
        bool $broadcast = true,
    ): AuctionTimelineEntry {
        $entry = AuctionTimelineEntry::create([
            'auction_id' => $auction->id,
            'type' => $type,
            'user_id' => $user?->id,
            'bid_id' => $bid?->id,
            'payload' => $payload === [] ? null : $payload,
            'occurred_at' => $occurredAt ?? now(),
        ]);

        $entry->load(['user', 'bid']);

        if ($broadcast) {
            AuctionTimelineUpdatedEvent::dispatch($entry);
        }

        return $entry;
    }

    /**
     * @return Collection<int, AuctionTimelineEntry>
     */
    public function timelineForAuction(int $auctionId, int $limit = 50): Collection
    {
        return AuctionTimelineEntry::query()
            ->where('auction_id', $auctionId)
            ->with(['user', 'bid'])
            ->orderBy('occurred_at')
            ->orderBy('id')
            ->limit($limit)
            ->get();
    }

    public function recordBidPlaced(Auction $auction, Bid $bid, User $user): AuctionTimelineEntry
    {
        return $this->record(
            auction: $auction,
            type: AuctionTimelineEntryType::BidPlaced,
            user: $user,
            bid: $bid,
            payload: ['amount' => $bid->amount],
            occurredAt: $bid->created_at,
        );
    }

    public function recordAuctionTriggered(Auction $auction): AuctionTimelineEntry
    {
        return $this->record(
            auction: $auction,
            type: AuctionTimelineEntryType::AuctionTriggered,
            payload: [
                'expires_at' => $auction->expires_at?->toISOString(),
            ],
            occurredAt: $auction->triggered_at,
        );
    }

    public function recordLeaderChanged(Auction $auction, ?User $leader): AuctionTimelineEntry
    {
        return $this->record(
            auction: $auction,
            type: AuctionTimelineEntryType::LeaderChanged,
            user: $leader,
            payload: [
                'leader_user_id' => $leader?->id,
            ],
        );
    }

    public function recordAuctionClosed(Auction $auction): AuctionTimelineEntry
    {
        $winner = $auction->winner_id ? User::find($auction->winner_id) : null;

        return $this->record(
            auction: $auction,
            type: AuctionTimelineEntryType::AuctionClosed,
            user: $winner,
            payload: [
                'winner_user_id' => $auction->winner_id,
            ],
        );
    }

    public function detectAndRecordLeaderChange(Auction $auction, ?int $previousWinnerId, ?int $newWinnerId): void
    {
        if ($previousWinnerId === $newWinnerId) {
            return;
        }

        $leader = $newWinnerId ? User::find($newWinnerId) : null;

        $this->recordLeaderChanged($auction, $leader);
    }

    public function winningUserIdForAuction(int $auctionId): ?int
    {
        return Bid::query()
            ->where('auction_id', $auctionId)
            ->where('is_winning', true)
            ->value('user_id');
    }

    public function computeLeaderUserId(int $auctionId, ?int $upToBidId = null): ?int
    {
        $query = DB::table('bids')->where('auction_id', $auctionId);

        if ($upToBidId !== null) {
            $bid = Bid::find($upToBidId);
            if ($bid) {
                $query->where(function ($q) use ($bid): void {
                    $q->where('created_at', '<', $bid->created_at)
                        ->orWhere(function ($q2) use ($bid): void {
                            $q2->where('created_at', '=', $bid->created_at)
                                ->where('id', '<=', $bid->id);
                        });
                });
            }
        }

        $totals = $query
            ->selectRaw('user_id, SUM(amount) as total')
            ->groupBy('user_id')
            ->orderByDesc('total')
            ->get();

        if ($totals->isEmpty()) {
            return null;
        }

        $maxTotal = $totals->first()->total;
        $topUsers = $totals->filter(fn ($row) => $row->total == $maxTotal);

        if ($topUsers->count() !== 1) {
            return null;
        }

        return (int) $topUsers->first()->user_id;
    }

    public function actorLabel(?User $user): ?string
    {
        if (! $user) {
            return null;
        }

        return MsisdnMasker::mask($user->phone);
    }

    public function messageForEntry(AuctionTimelineEntry $entry, ?User $viewer): string
    {
        $isMine = $viewer && $entry->user_id === $viewer->id;

        return match ($entry->type) {
            AuctionTimelineEntryType::BidPlaced => $isMine
                ? sprintf('You bid %d pts', (int) ($entry->payload['amount'] ?? 0))
                : sprintf('%s bid %d pts', $this->actorLabel($entry->user), (int) ($entry->payload['amount'] ?? 0)),
            AuctionTimelineEntryType::AuctionTriggered => 'Threshold reached — countdown started!',
            AuctionTimelineEntryType::LeaderChanged => $entry->user_id === null
                ? 'Leadership is tied'
                : ($isMine
                    ? 'You take the lead'
                    : sprintf('%s takes the lead', $this->actorLabel($entry->user))),
            AuctionTimelineEntryType::AuctionClosed => $entry->user_id === null
                ? 'Auction ended with no winner'
                : ($isMine
                    ? 'You won the auction!'
                    : sprintf('%s won the auction!', $this->actorLabel($entry->user))),
        };
    }

    public function backfillAuction(Auction $auction, bool $fresh = false): int
    {
        if ($fresh) {
            AuctionTimelineEntry::where('auction_id', $auction->id)->delete();
        } elseif (AuctionTimelineEntry::where('auction_id', $auction->id)->exists()) {
            return 0;
        }

        $created = 0;
        $previousLeaderId = null;

        $bids = $auction->bids()->orderBy('created_at')->orderBy('id')->get();

        foreach ($bids as $bid) {
            $bid->load('user');

            $this->record(
                auction: $auction,
                type: AuctionTimelineEntryType::BidPlaced,
                broadcast: false,
                user: $bid->user,
                bid: $bid,
                payload: ['amount' => $bid->amount],
                occurredAt: $bid->created_at,
            );
            $created++;

            $newLeaderId = $this->computeLeaderUserId($auction->id, $bid->id);

            if ($newLeaderId !== $previousLeaderId) {
                $leader = $newLeaderId ? User::find($newLeaderId) : null;

                $this->record(
                    auction: $auction,
                    type: AuctionTimelineEntryType::LeaderChanged,
                    broadcast: false,
                    user: $leader,
                    payload: ['leader_user_id' => $newLeaderId],
                    occurredAt: $bid->created_at,
                );
                $created++;
                $previousLeaderId = $newLeaderId;
            }
        }

        if ($auction->triggered_at) {
            $this->record(
                auction: $auction,
                type: AuctionTimelineEntryType::AuctionTriggered,
                broadcast: false,
                payload: ['expires_at' => $auction->expires_at?->toISOString()],
                occurredAt: $auction->triggered_at,
            );
            $created++;
        }

        if ($auction->status === AuctionStatus::CLOSED) {
            $winner = $auction->winner_id ? User::find($auction->winner_id) : null;

            $this->record(
                auction: $auction,
                type: AuctionTimelineEntryType::AuctionClosed,
                broadcast: false,
                user: $winner,
                payload: ['winner_user_id' => $auction->winner_id],
                occurredAt: $auction->updated_at,
            );
            $created++;
        }

        return $created;
    }
}
