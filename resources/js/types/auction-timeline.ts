export type AuctionTimelineEntryType =
    | 'bid_placed'
    | 'auction_triggered'
    | 'leader_changed'
    | 'auction_closed';

export type AuctionTimelineEntry = {
    id: number;
    type: AuctionTimelineEntryType;
    occurred_at: string;
    actor_label: string | null;
    is_mine: boolean;
    is_system: boolean;
    message: string;
    meta: Record<string, unknown>;
};

export type AuctionTimelineResponse = {
    data: AuctionTimelineEntry[];
};
