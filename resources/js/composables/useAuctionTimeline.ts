import { useHttp } from '@inertiajs/vue3';
import { echo } from '@laravel/echo-vue';
import { nextTick, onUnmounted, ref, watch } from 'vue';
import type { Ref } from 'vue';
import { timeline as timelineRoute } from '@/routes/auctions';
import type {
    AuctionTimelineEntry,
    AuctionTimelineResponse,
} from '@/types/auction-timeline';

export function useAuctionTimeline(auctionId: Ref<number | null>) {
    const entries = ref<AuctionTimelineEntry[]>([]);
    const isLoading = ref(false);
    const error = ref<string | null>(null);
    const feedEl = ref<HTMLElement | null>(null);

    const http = useHttp({});

    function scrollToBottom(): void {
        nextTick(() => {
            if (feedEl.value) {
                feedEl.value.scrollTop = feedEl.value.scrollHeight;
            }
        });
    }

    function appendEntry(entry: AuctionTimelineEntry): void {
        if (entries.value.some((e) => e.id === entry.id)) {
            return;
        }

        entries.value.push(entry);
        scrollToBottom();
    }

    function load(id: number): void {
        isLoading.value = true;
        error.value = null;

        http.get(timelineRoute.url(id), {
            onSuccess: (response: AuctionTimelineResponse) => {
                entries.value = response?.data ?? [];
                isLoading.value = false;
                scrollToBottom();
            },
            onError: () => {
                error.value = 'Could not load activity.';
                isLoading.value = false;
            },
        });
    }

    function reset(): void {
        entries.value = [];
        error.value = null;
        isLoading.value = false;
    }

    let subscribedAuctionId: number | null = null;

    function unsubscribe(): void {
        if (typeof window === 'undefined') {
            return;
        }

        if (subscribedAuctionId !== null) {
            echo().leave(`auction.${subscribedAuctionId}`);
            subscribedAuctionId = null;
        }
    }

    function subscribe(id: number): void {
        if (typeof window === 'undefined') {
            return;
        }

        unsubscribe();
        subscribedAuctionId = id;

        echo()
            .channel(`auction.${id}`)
            .listen(
                '.TimelineUpdated',
                (payload: { entry: AuctionTimelineEntry }) => {
                    if (payload?.entry) {
                        appendEntry(payload.entry);
                    }
                },
            );
    }

    watch(
        auctionId,
        (id) => {
            reset();
            unsubscribe();

            if (id) {
                load(id);
                subscribe(id);
            }
        },
        { immediate: true },
    );

    onUnmounted(() => {
        unsubscribe();
    });

    return {
        entries,
        isLoading,
        error,
        feedEl,
        scrollToBottom,
        reset,
    };
}
