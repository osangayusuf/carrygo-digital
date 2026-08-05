import { echo } from '@laravel/echo-vue';
import { onUnmounted, ref, watch } from 'vue';
import type { Ref } from 'vue';

type CountdownPayload = {
    expires_at?: string | null;
    status?: string;
};

/**
 * Keeps an auction's expiry in sync with the server.
 *
 * `expires_at` is a materialised timestamp: it is computed once when the auction
 * triggers, and recomputed if an admin later changes the countdown duration.
 * Without this subscription, an already-open page would keep counting down to a
 * stale deadline until the next full page load.
 *
 * Listens on the shared `auction.{id}` channel and uses `stopListening` rather
 * than `leave` on teardown, so it does not tear the channel out from under other
 * subscribers (e.g. the timeline feed).
 */
export function useAuctionCountdown(
    auctionId: Ref<number | null>,
    initialExpiresAt: Ref<string | null | undefined>,
) {
    const expiresAt = ref<string | null>(initialExpiresAt.value ?? null);

    // Server-pushed values win until the prop itself changes (e.g. Inertia visit).
    watch(initialExpiresAt, (value) => {
        expiresAt.value = value ?? null;
    });

    let subscribedAuctionId: number | null = null;

    function handle(payload: CountdownPayload): void {
        if (payload?.expires_at) {
            expiresAt.value = payload.expires_at;
        }
    }

    function unsubscribe(): void {
        if (typeof window === 'undefined' || subscribedAuctionId === null) {
            return;
        }

        echo()
            .channel(`auction.${subscribedAuctionId}`)
            .stopListening('.AuctionCountdownUpdated', handle)
            .stopListening('.AuctionTriggered', handle);

        subscribedAuctionId = null;
    }

    function subscribe(id: number): void {
        if (typeof window === 'undefined') {
            return;
        }

        unsubscribe();
        subscribedAuctionId = id;

        echo()
            .channel(`auction.${id}`)
            .listen('.AuctionCountdownUpdated', handle)
            .listen('.AuctionTriggered', handle);
    }

    watch(
        auctionId,
        (id) => {
            unsubscribe();

            if (id) {
                subscribe(id);
            }
        },
        { immediate: true },
    );

    onUnmounted(() => {
        unsubscribe();
    });

    return { expiresAt };
}
