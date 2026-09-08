<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { ref } from 'vue';
import ShareModal from '@/components/modals/ShareModal.vue';
import type { Review } from '@/pages/Home/Index.vue';

const props = defineProps<{
    reviews: Review[];
}>();

const isShareOpen = ref(false);
const selectedShareUrl = ref('');
const selectedShareName = ref('');
const selectedShareMessage = ref('');

function openShareModal(review: Review): void {
    if (!review.bid) {
        return;
    }

    const origin = typeof window !== 'undefined' ? window.location.origin : '';
    selectedShareUrl.value = `${origin}/auctions/${review.bid.id}`;
    selectedShareName.value = review.bid.name;

    const page = usePage();
    const authUser = page.props.auth?.user as
        | { id?: number | string }
        | undefined;
    const isWinner =
        authUser && Number(authUser.id) === Number(review.bid.winner_id);

    if (isWinner) {
        selectedShareMessage.value = `I won ${review.bid.name} on Bidora! Check out my review: "${review.comment}" - Bid here:`;
    } else {
        selectedShareMessage.value = `A verified bidder won ${review.bid.name} on Bidora! What they said: "${review.comment}" - Join me to bid:`;
    }

    isShareOpen.value = true;
}

function maskedPhone(msisdn: string | undefined): {
    prefix: string;
    suffix: string;
} {
    if (!msisdn) {
        return { prefix: '234', suffix: 'xxxx' };
    }

    const digits = msisdn.replace(/\D/g, '');

    return {
        prefix: `${digits.slice(0, 3)} ${digits.slice(3, 6)}`,
        suffix: digits.slice(-4),
    };
}
</script>

<template>
    <section class="bg-surface-container py-20 pb-24">
        <div class="mx-auto max-w-screen-2xl px-8">
            <div class="mb-12 text-center">
                <h2
                    class="mb-2 font-headline text-3xl font-extrabold tracking-tight"
                >
                    What Our Community Says
                </h2>
                <p class="font-medium text-secondary">
                    Hear from winners who have scored amazing bids on Bidora
                </p>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                <div
                    v-for="review in props.reviews"
                    :key="review.id"
                    class="flex h-full flex-col rounded-2xl bg-surface-container-lowest p-6 shadow-sm transition-shadow hover:shadow-md"
                >
                    <div class="mb-4 flex items-center justify-between">
                        <!-- Stars -->
                        <div class="flex items-center gap-1 text-amber-500">
                            <svg
                                v-for="i in 5"
                                :key="i"
                                class="h-4 w-4 shrink-0"
                                :class="
                                    i <= Math.round(Number(review.rating))
                                        ? 'fill-current stroke-amber-500 text-amber-500'
                                        : 'fill-none stroke-amber-500 text-amber-500'
                                "
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                stroke-width="1.5"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z"
                                />
                            </svg>
                        </div>

                        <!-- Share Button -->
                        <button
                            v-if="review.bid"
                            type="button"
                            class="flex h-7 w-7 cursor-pointer items-center justify-center rounded-full bg-surface-container text-on-surface-variant transition-colors hover:bg-surface-container-high"
                            title="Share testimonial"
                            @click="openShareModal(review)"
                        >
                            <span class="pi pi-share-alt text-[14px]"></span>
                        </button>
                    </div>

                    <p
                        class="mb-6 grow text-sm leading-relaxed text-on-surface"
                    >
                        "{{ review.comment }}"
                    </p>

                    <div class="mt-auto border-t border-surface-container pt-4">
                        <div class="mb-3 flex items-center gap-3">
                            <div
                                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-primary-container text-primary"
                            >
                                <span
                                    class="material-symbols-outlined text-sm"
                                    data-weight="fill"
                                    >person</span
                                >
                            </div>
                            <div>
                                <p class="text-sm leading-none font-bold">
                                    {{
                                        maskedPhone(review.user_id?.toString())
                                            .prefix
                                    }}
                                    <span class="text-secondary opacity-50"
                                        >***</span
                                    >
                                    {{
                                        maskedPhone(review.user_id?.toString())
                                            .suffix
                                    }}
                                </p>
                                <div class="mt-1 flex items-center gap-2">
                                    <p class="text-xs text-secondary">
                                        Verified Bidder
                                    </p>
                                    <div
                                        v-if="
                                            review.social_platform &&
                                            review.social_handle
                                        "
                                        class="flex items-center gap-1 rounded bg-primary/10 px-1.5 py-0.5 text-[10px] font-medium text-primary"
                                    >
                                        <span
                                            >{{ review.social_platform }}:</span
                                        >
                                        <span>{{
                                            review.social_handle.startsWith('@')
                                                ? review.social_handle
                                                : '@' + review.social_handle
                                        }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div
                            v-if="review.bid"
                            class="rounded-lg bg-surface-container p-2"
                        >
                            <p
                                class="truncate text-xs font-semibold text-secondary"
                            >
                                Item: {{ review.bid.name }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Share Modal -->
    <ShareModal
        :is-open="isShareOpen"
        :url="selectedShareUrl"
        :name="selectedShareName"
        :message="selectedShareMessage"
        @close="isShareOpen = false"
    />
</template>
