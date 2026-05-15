import { defineStore } from 'pinia';
import { ref } from 'vue';

export type AuctionStatus = 'draft' | 'active' | 'triggered' | 'closed';

export interface AuctionState {
    id: number;
    name: string;
    category: string;
    image: string;
    status: AuctionStatus;
    current_points: number;
    bid_count: number;
    expires_at: string | null;
    winner_id: number | null;
}

export interface MyBid {
    id: number;
    auction_id: number;
    user_id: number;
    amount: number;
    is_winning: boolean;
    created_at: string;
}

export const useAuctionStore = defineStore('auction', () => {
    /** Core auction state synced with the server and Echo events. */
    const auction = ref<AuctionState | null>(null);

    /** Current authenticated user's bids on this auction. */
    const myBids = ref<MyBid[]>([]);

    /** Timestamp (ms) of the user's last bid — used for 5-second cooldown UX. */
    const lastBidAt = ref<number | null>(null);

    /** Whether the current_points value just changed (used to trigger flash animation). */
    const pointsFlashing = ref(false);

    /**
     * Seed the store from Inertia page props on initial load.
     */
    function initFromProps(auctionData: AuctionState, bids: MyBid[]): void {
        auction.value = auctionData;
        myBids.value = bids;
    }

    /**
     * Called when a BidPlacedEvent is received via Echo.
     */
    function onBidPlaced(payload: {
        current_points: number;
        bid_count: number;
        winning_user_id: number | null;
    }): void {
        if (!auction.value) {
            return;
        }
        auction.value.current_points = payload.current_points;
        auction.value.bid_count = payload.bid_count;

        // Trigger flash animation.
        pointsFlashing.value = true;
        setTimeout(() => {
            pointsFlashing.value = false;
        }, 800);
    }

    /**
     * Called when an AuctionTriggeredEvent is received via Echo.
     */
    function onAuctionTriggered(payload: { expires_at: string; status: AuctionStatus }): void {
        if (!auction.value) {
            return;
        }
        auction.value.expires_at = payload.expires_at;
        auction.value.status = payload.status;
    }

    /**
     * Called when an AuctionClosedEvent is received via Echo.
     */
    function onAuctionClosed(payload: { winner_id: number | null; status: AuctionStatus }): void {
        if (!auction.value) {
            return;
        }
        auction.value.winner_id = payload.winner_id;
        auction.value.status = payload.status;
    }

    /**
     * Record that the user placed a bid at the current moment (for 5s cooldown UX).
     */
    function recordBidPlaced(): void {
        lastBidAt.value = Date.now();
    }

    return {
        auction,
        myBids,
        lastBidAt,
        pointsFlashing,
        initFromProps,
        onBidPlaced,
        onAuctionTriggered,
        onAuctionClosed,
        recordBidPlaced,
    };
});
