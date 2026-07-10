<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import AuctionHistoryFeed from '@/components/modals/AuctionHistoryFeed.vue';
import { usePlaceBidModal } from '@/composables/usePlaceBidModal';

const page = usePage();
const { activeBid, isOpen, form, close, submit } = usePlaceBidModal();

const userPoints = computed(
    () => (page.props.auth as { user?: { points_balance?: number } })?.user?.points_balance ?? null,
);
</script>

<template>
    <Teleport to="body">
        <div
            v-if="isOpen && activeBid"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4 backdrop-blur-sm"
        >
            <div class="absolute inset-0" @click="close"></div>
            <div
                class="relative flex max-h-[90vh] w-full max-w-lg flex-col overflow-hidden rounded-3xl bg-surface-container-lowest p-6 shadow-2xl"
            >
                <button
                    type="button"
                    class="absolute top-4 right-4 flex h-8 w-8 items-center justify-center rounded-full bg-surface-container text-on-surface-variant transition-colors hover:bg-surface-container-high"
                    @click="close"
                >
                    <span class="material-symbols-outlined text-[20px]">close</span>
                </button>

                <h2 class="mb-2 font-headline text-2xl font-extrabold text-on-surface">Place Your Bid</h2>
                <p class="mb-4 text-sm text-outline">
                    Bid on <span class="font-bold text-on-surface">{{ activeBid.name }}</span>
                </p>

                <AuctionHistoryFeed :auction-id="activeBid.id" />

                <form class="mt-auto" @submit.prevent="submit">
                    <div class="mb-4">
                        <label class="mb-2 block text-xs font-bold tracking-widest text-outline uppercase"
                            >Bid Points</label
                        >
                        <input
                            v-model="form.points"
                            type="number"
                            required
                            min="1"
                            class="w-full rounded-2xl border bg-surface-container-low px-4 py-3 text-lg font-bold text-black focus:ring-2 disabled:opacity-50"
                            :class="
                                form.errors.points
                                    ? 'border-error focus:border-error focus:ring-error/30'
                                    : 'border-surface-container focus:border-primary focus:ring-primary-container'
                            "
                            :disabled="form.processing"
                        />
                        <p v-if="form.errors.points" class="mt-2 text-xs font-bold text-error">
                            {{ form.errors.points }}
                        </p>
                    </div>

                    <div class="mb-6 rounded-xl border border-secondary/20 bg-secondary-container/30 p-4">
                        <div class="mb-2 flex items-center justify-between">
                            <span class="text-sm font-semibold text-on-surface-variant">Your Active Points</span>
                            <span class="text-sm font-black text-on-surface">{{ userPoints ?? 0 }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-semibold text-on-surface-variant">Points Threshold</span>
                            <span class="text-sm font-black text-on-surface">{{ activeBid.opening_points }}</span>
                        </div>
                    </div>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="flex w-full items-center justify-center rounded-2xl bg-primary py-4 font-bold text-on-primary transition-all hover:bg-on-primary-fixed active:scale-95 disabled:opacity-50"
                    >
                        <span v-if="form.processing" class="material-symbols-outlined mr-2 animate-spin"
                            >progress_activity</span
                        >
                        Confirm Bid
                    </button>

                    <p class="mt-3 text-center text-xs text-outline">
                        <span class="material-symbols-outlined align-middle text-[13px]">info</span>
                        Points bidden cannot be refunded and wallet credits are non-withdrawable.
                    </p>
                </form>
            </div>
        </div>
    </Teleport>
</template>
