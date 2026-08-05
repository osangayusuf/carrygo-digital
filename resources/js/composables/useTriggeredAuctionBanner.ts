import { usePage } from '@inertiajs/vue3';
import { echo } from '@laravel/echo-vue';
import { computed, onUnmounted, ref, watch } from 'vue';
import { hasExpired } from '@/lib/utils';

export type TriggeredAuction = {
    id: number;
    name: string;
    expires_at: string | null;
    url: string;
};

type CountdownPayload = {
    expires_at?: string | null;
};

/** How long each auction stays on screen before the strip slides to the next. */
const ROTATE_INTERVAL_MS = 6000;

/**
 * Drives the red "closing soon" strip.
 *
 * The `triggeredAuctions` shared prop can carry several open auctions at once;
 * this rotates through them on a timer so every open auction gets airtime,
 * rather than only ever showing the first.
 *
 * Expiries are kept live in two ways:
 *   - an Echo subscription per auction, so an admin changing a countdown is
 *     reflected without a page load (see useAuctionCountdown for the why);
 *   - a reactive `hasExpired` check, so an auction drops out of the rotation the
 *     moment its timer runs out.
 */
export function useTriggeredAuctionBanner() {
    const page = usePage();

    const triggeredAuctions = computed(
        () => (page.props.triggeredAuctions as TriggeredAuction[]) ?? [],
    );

    // Server-pushed expiry overrides, keyed by auction id.
    const overrides = ref<Record<number, string>>({});

    /** Open auctions whose countdown has not yet run out, with live expiries. */
    const liveAuctions = computed(() =>
        triggeredAuctions.value
            .map((auction) => ({
                ...auction,
                expires_at: overrides.value[auction.id] ?? auction.expires_at,
            }))
            .filter((auction) => !hasExpired(auction.expires_at)),
    );

    const activeIndex = ref(0);

    // Keep the index in range as auctions expire or the prop changes.
    watch(liveAuctions, (list) => {
        if (list.length === 0) {
            activeIndex.value = 0;
        } else if (activeIndex.value >= list.length) {
            activeIndex.value = activeIndex.value % list.length;
        }
    });

    const activeAuction = computed(
        () => liveAuctions.value[activeIndex.value] ?? null,
    );

    const activeExpiresAt = computed(
        () => activeAuction.value?.expires_at ?? null,
    );

    const hasMultiple = computed(() => liveAuctions.value.length > 1);

    // --- rotation -----------------------------------------------------------

    const isPaused = ref(false);
    let rotateTimer: ReturnType<typeof setInterval> | null = null;

    function advance(): void {
        if (isPaused.value || liveAuctions.value.length < 2) {
            return;
        }

        activeIndex.value = (activeIndex.value + 1) % liveAuctions.value.length;
    }

    function stopRotating(): void {
        if (rotateTimer) {
            clearInterval(rotateTimer);
            rotateTimer = null;
        }
    }

    function startRotating(): void {
        stopRotating();
        rotateTimer = setInterval(advance, ROTATE_INTERVAL_MS);
    }

    watch(
        hasMultiple,
        (multiple) => {
            if (multiple) {
                startRotating();
            } else {
                stopRotating();
            }
        },
        { immediate: true },
    );

    /** Pause rotation while the user is interacting with the strip. */
    function pause(): void {
        isPaused.value = true;
    }

    function resume(): void {
        isPaused.value = false;
    }

    function goTo(index: number): void {
        if (index >= 0 && index < liveAuctions.value.length) {
            activeIndex.value = index;

            // Restart the timer so the manually chosen slide gets a full turn.
            if (hasMultiple.value) {
                startRotating();
            }
        }
    }

    // --- live expiry updates ------------------------------------------------

    let subscribedIds: number[] = [];

    function handle(payload: CountdownPayload, auctionId: number): void {
        if (payload?.expires_at) {
            overrides.value = {
                ...overrides.value,
                [auctionId]: payload.expires_at,
            };
        }
    }

    function unsubscribeAll(): void {
        if (typeof window === 'undefined') {
            return;
        }

        subscribedIds.forEach((id) => {
            echo()
                .channel(`auction.${id}`)
                .stopListening('.AuctionCountdownUpdated')
                .stopListening('.AuctionTriggered');
        });

        subscribedIds = [];
    }

    watch(
        () => triggeredAuctions.value.map((auction) => auction.id).join(','),
        () => {
            if (typeof window === 'undefined') {
                return;
            }

            unsubscribeAll();

            subscribedIds = triggeredAuctions.value.map(
                (auction) => auction.id,
            );

            subscribedIds.forEach((id) => {
                const listener = (payload: CountdownPayload) =>
                    handle(payload, id);

                echo()
                    .channel(`auction.${id}`)
                    .listen('.AuctionCountdownUpdated', listener)
                    .listen('.AuctionTriggered', listener);
            });
        },
        { immediate: true },
    );

    onUnmounted(() => {
        stopRotating();
        unsubscribeAll();
    });

    return {
        liveAuctions,
        activeAuction,
        activeExpiresAt,
        activeIndex,
        hasMultiple,
        pause,
        resume,
        goTo,
    };
}
