import { router, useForm, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';
import { store as storeBid } from '@/actions/App/Http/Controllers/BidController';
import type { Bid } from '@/types/auction';
import { login } from '@/routes/index';

const activeBid = ref<Bid | null>(null);
const isOpen = ref(false);

let form: ReturnType<typeof useForm<{ points: number }>> | null = null;

export function usePlaceBidModal() {
    const page = usePage();

    if (!form) {
        form = useForm({ points: 0 });
    }

    function open(bid: Bid, userPoints: number | null): void {
        if (!page.props.auth?.user) {
            sessionStorage.setItem('pendingBidId', bid.id.toString());
            router.get(login.url());

            return;
        }

        activeBid.value = bid;
        form!.points = userPoints ?? 0;
        form!.clearErrors();
        isOpen.value = true;
    }

    function close(): void {
        isOpen.value = false;
    }

    function submit(): void {
        if (!activeBid.value) {
            return;
        }

        form!.post(storeBid.url(activeBid.value.id), {
            preserveScroll: true,
            onSuccess: () => {
                isOpen.value = false;
            },
            onError: () => {
                isOpen.value = true;
            },
        });
    }

    function restorePendingBid(bids: Bid[], userPoints: number | null): void {
        if (!page.props.auth?.user) {
            return;
        }

        const pendingBidId = sessionStorage.getItem('pendingBidId');

        if (!pendingBidId) {
            return;
        }

        const bid = bids.find((b) => b.id.toString() === pendingBidId);

        if (bid) {
            open(bid, userPoints);
            sessionStorage.removeItem('pendingBidId');
        }
    }

    return {
        activeBid,
        isOpen,
        form,
        open,
        close,
        submit,
        restorePendingBid,
    };
}
