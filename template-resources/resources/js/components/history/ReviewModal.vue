<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { storeReview } from '@/actions/App/Http/Controllers/HistoryController';

const props = defineProps<{
    show: boolean;
    bid: any;
    mode?: 'view' | 'write';
}>();

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'success'): void;
}>();

const form = useForm({
    rating: 0,
    comment: '',
    social_platform: '',
    social_handle: '',
});

const hoveredRating = ref(0);

function close() {
    emit('close');
    form.reset();
}

function submit() {
    if (!props.bid) {
        return;
    }

    // We assume the user is auth'd since this page is for auth users
    form.post(storeReview.url(props.bid.id), {
        preserveScroll: true,
        onSuccess: () => {
            emit('success');
            form.reset();
        },
    });
}
</script>

<template>
    <Teleport to="body">
        <div
            v-if="show && bid"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4 backdrop-blur-sm"
        >
            <div class="absolute inset-0" @click="close"></div>

            <div
                class="relative flex max-h-[90vh] w-full max-w-md flex-col overflow-hidden rounded-3xl bg-surface-container-lowest shadow-2xl"
            >
                <div class="p-6 pb-2 border-b border-surface-container">
                    <button
                        type="button"
                        class="absolute top-4 right-4 flex h-8 w-8 items-center justify-center rounded-full bg-surface-container text-on-surface-variant transition-colors hover:bg-surface-container-high"
                        @click="close"
                    >
                        <span class="material-symbols-outlined text-[20px]">close</span>
                    </button>

                    <h2 class="font-headline text-2xl font-extrabold text-on-surface">
                        {{ mode === 'write' ? 'Write a Review' : 'Past Reviews' }}
                    </h2>
                    <p class="text-sm text-outline mt-1 mb-4">
                        {{ bid.name }}
                    </p>
                </div>

                <div class="flex-1 overflow-y-auto p-6 space-y-6">
                    <!-- Past Reviews Section -->
                    <div v-if="mode !== 'write' && bid.reviews && bid.reviews.length > 0">
                        <h3 class="mb-3 text-sm font-bold uppercase tracking-widest text-secondary">
                            Past Reviews
                        </h3>
                        <div class="space-y-4">
                            <div
                                v-for="review in bid.reviews"
                                :key="review.id"
                                class="rounded-xl border border-secondary/20 bg-secondary-container/10 p-4"
                            >
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-xs font-bold text-on-surface">{{ review.user_id.slice(0, -4) + '****' }}</span>
                                    <div class="flex text-amber-500">
                                        <svg
                                            v-for="i in 5" :key="i"
                                            class="w-4 h-4 shrink-0"
                                            :class="i <= Math.round(Number(review.rating)) ? 'fill-current text-amber-500 stroke-amber-500' : 'fill-none stroke-amber-500 text-amber-500'"
                                            xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 24 24"
                                            stroke-width="1.5"
                                        >
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z" />
                                        </svg>
                                    </div>
                                </div>
                                <div v-if="review.social_platform && review.social_handle" class="mb-2 flex items-center gap-1 text-xs text-primary font-medium">
                                    <span>{{ review.social_platform }}:</span>
                                    <span>{{ review.social_handle.startsWith('@') ? review.social_handle : '@' + review.social_handle }}</span>
                                </div>
                                <p class="text-sm text-on-surface-variant" style="white-space: pre-wrap;">
                                    {{ review.comment }}
                                </p>
                            </div>
                        </div>
                    </div>
                    <div v-else-if="mode !== 'write'">
                        <p class="text-sm text-outline italic">No reviews yet for this item.</p>
                    </div>

                    <!-- Write a Review Section -->
                    <div v-if="mode === 'write'">
                        <h3 class="mb-4 text-sm font-bold uppercase tracking-widest text-primary">
                            Write Your Own Review
                        </h3>
                        <form @submit.prevent="submit">
                            <!-- Star Rating Input -->
                            <div class="mb-4">
                                <label class="mb-2 block text-xs font-bold tracking-widest text-outline uppercase">
                                    Rating
                                </label>
                                <div class="flex items-center gap-1 text-amber-500 cursor-pointer">
                                    <svg
                                        v-for="i in 5"
                                        :key="i"
                                        class="w-8 h-8 shrink-0 transition-transform hover:scale-110"
                                        :class="(hoveredRating ? i <= hoveredRating : i <= form.rating) ? 'fill-current text-amber-500 stroke-amber-500' : 'fill-none stroke-amber-500 text-amber-500'"
                                        @mouseenter="hoveredRating = i"
                                        @mouseleave="hoveredRating = 0"
                                        @click="form.rating = i"
                                        xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 24 24"
                                        stroke-width="1.5"
                                    >
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z" />
                                    </svg>
                                </div>
                                <p v-if="form.errors.rating" class="mt-2 text-xs font-bold text-error">
                                    {{ form.errors.rating }}
                                </p>
                            </div>

                            <!-- Social Media (Optional) -->
                            <div class="mb-4 grid grid-cols-2 gap-4">
                                <div>
                                    <label class="mb-2 block text-xs font-bold text-outline uppercase">
                                        Social Platform <span class="text-secondary/50 font-normal tracking-normal lowercase">(Optional)</span>
                                    </label>
                                    <select
                                        v-model="form.social_platform"
                                        class="w-full rounded-2xl border border-surface-container bg-surface-container-low px-4 py-3 text-sm font-medium focus:border-primary focus:ring-2 focus:ring-primary-container disabled:opacity-50 appearance-none"
                                        :disabled="form.processing"
                                    >
                                        <option value="">Select Platform</option>
                                        <option value="X (Twitter)">X (Twitter)</option>
                                        <option value="Facebook">Facebook</option>
                                        <option value="Instagram">Instagram</option>
                                        <option value="TikTok">TikTok</option>
                                        <option value="Other">Other</option>
                                    </select>
                                    <p v-if="form.errors.social_platform" class="mt-2 text-xs font-bold text-error">
                                        {{ form.errors.social_platform }}
                                    </p>
                                </div>
                                <div>
                                    <label class="mb-2 block text-xs font-bold text-outline uppercase">
                                        Handle / Username <span class="text-secondary/50 font-normal tracking-normal lowercase">(Optional)</span>
                                    </label>
                                    <input
                                        type="text"
                                        v-model="form.social_handle"
                                        placeholder="@username"
                                        class="w-full rounded-2xl border border-surface-container bg-surface-container-low px-4 py-3 text-sm font-medium focus:border-primary focus:ring-2 focus:ring-primary-container disabled:opacity-50"
                                        :disabled="form.processing"
                                    />
                                    <p v-if="form.errors.social_handle" class="mt-2 text-xs font-bold text-error">
                                        {{ form.errors.social_handle }}
                                    </p>
                                </div>
                            </div>

                            <!-- Comment Input -->
                            <div class="mb-6">
                                <label class="mb-2 block text-xs font-bold tracking-widest text-outline uppercase">
                                    Comment
                                </label>
                                <textarea
                                    v-model="form.comment"
                                    required
                                    rows="3"
                                    placeholder="What did you think about this bid?"
                                    class="w-full rounded-2xl border border-surface-container bg-surface-container-low px-4 py-3 text-sm font-medium focus:border-primary focus:ring-2 focus:ring-primary-container disabled:opacity-50"
                                    :disabled="form.processing"
                                ></textarea>
                                <p v-if="form.errors.comment" class="mt-2 text-xs font-bold text-error">
                                    {{ form.errors.comment }}
                                </p>
                            </div>

                            <button
                                type="submit"
                                :disabled="form.processing || form.rating === 0"
                                class="flex w-full items-center justify-center rounded-2xl bg-primary py-4 font-bold text-on-primary transition-all hover:bg-on-primary-fixed active:scale-95 disabled:opacity-50"
                            >
                                <span v-if="form.processing" class="material-symbols-outlined mr-2 animate-spin">
                                    progress_activity
                                </span>
                                Submit Review
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </Teleport>
</template>
