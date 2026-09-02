<script setup lang="ts">
import { Head, useForm, router, Link } from '@inertiajs/vue3';
import {
    Star,
    Search,
    Eye,
    EyeOff,
    Play,
    X,
    Trash2,
    AlertTriangle,
} from 'lucide-vue-next';
import { ref } from 'vue';
import AdminLayout from '@/layouts/AdminLayout.vue';
import {
    index as reviewsIndex,
    toggleVisibility as toggleVisibilityRoute,
    destroy as destroyRoute,
} from '@/routes/admin/reviews';

type Review = {
    id: number;
    auction_id: number | null;
    user_id: number | null;
    rating: number;
    comment: string;
    social_platform: string | null;
    social_handle: string | null;
    is_visible: boolean;
    created_at: string;
    user: { name: string; email: string; phone: string } | null;
    auction: { name: string } | null;
    photos: string[] | null;
    video: string | null;
};

const expandedImage = ref<string | null>(null);
const expandedVideo = ref<string | null>(null);
const reviewToDelete = ref<Review | null>(null);
const isDeleting = ref(false);

const props = defineProps<{
    reviews: {
        data: Review[];
        links: any[];
        current_page: number;
        last_page: number;
    };
    filters: {
        search: string | null;
        visibility: string | null;
    };
}>();

const searchForm = useForm({
    search: props.filters.search || '',
    visibility: props.filters.visibility || '',
});

const handleSearch = () => {
    searchForm.get(reviewsIndex.url(), {
        preserveState: true,
    });
};

const clearSearch = () => {
    searchForm.search = '';
    searchForm.visibility = '';
    handleSearch();
};

const toggleReviewVisibility = (review: Review) => {
    router.post(
        toggleVisibilityRoute.url(review.id),
        {},
        {
            preserveScroll: true,
        },
    );
};

const confirmDeleteReview = (review: Review) => {
    reviewToDelete.value = review;
};

const closeDeleteModal = () => {
    if (isDeleting.value) return;
    reviewToDelete.value = null;
};

const executeDelete = () => {
    if (!reviewToDelete.value) return;

    isDeleting.value = true;
    router.delete(destroyRoute.url(reviewToDelete.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            reviewToDelete.value = null;
        },
        onFinish: () => {
            isDeleting.value = false;
        },
    });
};
</script>

<template>
    <Head title="Admin - Review Moderation" />

    <AdminLayout :breadcrumbs="[{ title: 'Reviews' }]">
        <div class="flex flex-col gap-6 font-sans text-xs">
            <div
                class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center"
            >
                <div>
                    <h1
                        class="flex items-center gap-2 text-2xl font-black tracking-tight text-primary uppercase"
                    >
                        <Star class="h-6 w-6 text-primary" />
                        <span>Review Moderation Panel</span>
                    </h1>
                    <p class="mt-1 text-xs text-on-surface-variant">
                        Moderate user feedback comments, ratings, and toggle
                        visibility on the public landing page.
                    </p>
                </div>
            </div>

            <div
                class="rounded-xl border border-outline-variant bg-surface-container-lowest p-4 shadow-sm"
            >
                <form
                    @submit.prevent="handleSearch"
                    class="flex flex-col gap-3 md:flex-row"
                >
                    <div class="relative flex-1">
                        <input
                            v-model="searchForm.search"
                            type="text"
                            placeholder="SEARCH BY USER PHONE OR REVIEW COMMENT..."
                            class="w-full rounded-lg border border-outline-variant bg-surface-container-low py-3 pr-4 pl-10 text-xs font-bold text-on-surface focus:ring-1 focus:ring-secondary focus:outline-none"
                        />
                        <Search
                            class="absolute top-3.5 left-3.5 h-4 w-4 text-on-surface-variant"
                        />
                    </div>

                    <div class="w-full md:w-48">
                        <select
                            v-model="searchForm.visibility"
                            class="w-full rounded-lg border border-outline-variant bg-surface-container-low px-4 py-3 text-xs font-bold text-on-surface uppercase focus:ring-1 focus:ring-secondary focus:outline-none"
                        >
                            <option value="">ALL VISIBILITY</option>
                            <option value="true">VISIBLE ON FEED</option>
                            <option value="false">HIDDEN FROM FEED</option>
                        </select>
                    </div>

                    <div class="flex gap-2">
                        <button
                            type="submit"
                            class="rounded-lg bg-secondary px-5 py-3 font-black text-on-secondary-fixed uppercase transition-all"
                        >
                            Filter
                        </button>
                        <button
                            type="button"
                            @click="clearSearch"
                            class="rounded-lg border border-outline-variant bg-surface-container-lowest px-5 py-3 font-bold text-on-surface-variant uppercase transition-all hover:text-primary"
                        >
                            Reset
                        </button>
                    </div>
                </form>
            </div>

            <div
                class="flex flex-col overflow-hidden rounded-xl border border-outline-variant bg-surface-container-lowest shadow-sm"
            >
                <div class="overflow-x-auto">
                    <table
                        class="w-full min-w-[900px] border-collapse text-left"
                    >
                        <thead
                            class="border-b border-outline-variant bg-surface-container-low"
                        >
                            <tr
                                class="text-[10px] font-bold tracking-widest text-on-surface-variant uppercase"
                            >
                                <th class="px-6 py-4">Review ID</th>
                                <th class="px-6 py-4">User Info</th>
                                <th class="px-6 py-4 text-center">Rating</th>
                                <th class="w-1/3 px-6 py-4">Comment</th>
                                <th class="px-6 py-4">Auction Item</th>
                                <th class="px-6 py-4">Social Link</th>
                                <th class="px-6 py-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant">
                            <tr
                                v-for="review in reviews.data"
                                :key="review.id"
                                class="group transition-colors hover:bg-surface-container-low/20"
                            >
                                <td
                                    class="px-6 py-4 font-bold text-on-surface-variant"
                                >
                                    #{{ review.id }}
                                </td>
                                <td class="px-6 py-4">
                                    <div
                                        v-if="review.user"
                                        class="flex flex-col"
                                    >
                                        <span class="font-bold text-primary">{{
                                            review.user.name
                                        }}</span>
                                        <span
                                            class="mt-0.5 text-[10px] text-on-surface-variant"
                                            >{{ review.user.phone }}</span
                                        >
                                    </div>
                                    <span
                                        v-else
                                        class="font-semibold text-on-surface-variant italic"
                                        >Anonymous User</span
                                    >
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div
                                        class="flex items-center justify-center gap-0.5 text-amber-500"
                                    >
                                        <Star
                                            v-for="i in 5"
                                            :key="i"
                                            class="h-3.5 w-3.5"
                                            :class="[
                                                i <= Math.round(review.rating)
                                                    ? 'fill-amber-500 text-amber-500'
                                                    : 'text-surface-variant',
                                            ]"
                                        />
                                    </div>
                                </td>
                                <td
                                    class="px-6 py-4 font-sans leading-normal font-medium text-on-surface"
                                >
                                    <div class="whitespace-pre-line">
                                        {{ review.comment }}
                                    </div>
                                    <div
                                        v-if="
                                            review.photos?.length ||
                                            review.video
                                        "
                                        class="mt-2 flex flex-wrap gap-1.5"
                                    >
                                        <div
                                            v-for="(
                                                photo, idx
                                            ) in review.photos"
                                            :key="idx"
                                            class="h-8 w-8 cursor-pointer overflow-hidden rounded border border-outline-variant bg-surface-container-low transition-transform hover:scale-105"
                                            @click="
                                                expandedImage = `${$page.props.asset_url}storage/${photo}`
                                            "
                                        >
                                            <img
                                                :src="`${$page.props.asset_url}storage/${photo}`"
                                                class="h-full w-full object-cover"
                                            />
                                        </div>
                                        <div
                                            v-if="review.video"
                                            class="relative flex h-8 w-12 cursor-pointer items-center justify-center overflow-hidden rounded border border-outline-variant bg-black transition-transform hover:scale-105"
                                            @click="
                                                expandedVideo = `${$page.props.asset_url}storage/${review.video}`
                                            "
                                        >
                                            <Play
                                                class="absolute z-10 h-2.5 w-2.5 fill-white text-white"
                                            />
                                            <video
                                                :src="`${$page.props.asset_url}storage/${review.video}`"
                                                class="h-full w-full object-cover opacity-60"
                                            ></video>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        class="font-bold text-primary"
                                        v-if="review.auction"
                                        >{{ review.auction.name }}</span
                                    >
                                    <span
                                        class="font-semibold text-on-surface-variant italic"
                                        v-else
                                        >Direct Feedback</span
                                    >
                                </td>
                                <td class="px-6 py-4">
                                    <div
                                        class="flex flex-col"
                                        v-if="review.social_handle"
                                    >
                                        <span
                                            class="text-[10px] font-black tracking-widest text-primary uppercase"
                                            >{{ review.social_platform }}</span
                                        >
                                        <span
                                            class="mt-0.5 text-[10px] font-semibold text-on-surface-variant select-all"
                                            >{{ review.social_handle }}</span
                                        >
                                    </div>
                                    <span
                                        class="font-semibold text-on-surface-variant italic"
                                        v-else
                                        >No Social Linked</span
                                    >
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div
                                        class="flex items-center justify-end gap-2"
                                    >
                                        <button
                                            @click="
                                                toggleReviewVisibility(review)
                                            "
                                            :class="[
                                                'inline-flex items-center gap-1.5 rounded-lg border px-3 py-1.5 text-[10px] font-bold uppercase transition-colors',
                                                review.is_visible
                                                    ? 'border-secondary/20 bg-secondary-container text-on-secondary-container hover:bg-secondary-container/85'
                                                    : 'border-outline-variant text-on-surface-variant hover:bg-surface-container-low hover:text-primary',
                                            ]"
                                            :title="
                                                review.is_visible
                                                    ? 'Hide review from home page feed'
                                                    : 'Show review on home page feed'
                                            "
                                        >
                                            <component
                                                :is="
                                                    review.is_visible
                                                        ? Eye
                                                        : EyeOff
                                                "
                                                class="h-3.5 w-3.5"
                                            />
                                            <span>{{
                                                review.is_visible
                                                    ? 'Visible'
                                                    : 'Hidden'
                                            }}</span>
                                        </button>

                                        <button
                                            type="button"
                                            @click="confirmDeleteReview(review)"
                                            class="inline-flex items-center gap-1.5 rounded-lg border border-red-500/20 bg-red-500/10 px-2.5 py-1.5 text-[10px] font-bold text-red-600 uppercase transition-colors hover:bg-red-500/20 hover:text-red-700 dark:border-red-500/30 dark:bg-red-500/20 dark:text-red-400 dark:hover:bg-red-500/30"
                                            title="Delete review"
                                        >
                                            <Trash2 class="h-3.5 w-3.5" />
                                            <span>Delete</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="reviews.data.length === 0">
                                <td
                                    colspan="7"
                                    class="px-6 py-12 text-center font-bold tracking-widest text-on-surface-variant uppercase"
                                >
                                    No customer reviews posted yet.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div
                    v-if="reviews.last_page > 1"
                    class="flex items-center justify-between border-t border-outline-variant bg-surface-container-low px-6 py-4"
                >
                    <p
                        class="text-[10px] font-bold tracking-widest text-on-surface-variant uppercase"
                    >
                        Page {{ reviews.current_page }} of
                        {{ reviews.last_page }}
                    </p>
                    <div class="flex gap-1">
                        <Link
                            v-for="link in reviews.links"
                            :key="link.label"
                            :href="link.url || '#'"
                            v-html="link.label"
                            :class="[
                                'rounded-lg border px-3 py-1.5 text-[10px] font-bold transition-all',
                                link.active
                                    ? 'border-primary bg-primary text-on-primary'
                                    : 'border-outline-variant bg-surface-container-lowest text-on-surface-variant hover:bg-surface-container-low',
                                !link.url && 'cursor-not-allowed opacity-40',
                            ]"
                        />
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>

    <Teleport to="body">
        <div
            v-if="expandedImage"
            class="fixed inset-0 z-[100] flex cursor-pointer items-center justify-center bg-black/90 p-4 backdrop-blur-sm"
            @click="expandedImage = null"
        >
            <img
                :src="expandedImage"
                class="max-h-full max-w-full rounded-2xl object-contain shadow-2xl"
                alt="Expanded review image"
            />
        </div>

        <div
            v-if="expandedVideo"
            class="fixed inset-0 z-[100] flex items-center justify-center bg-black/90 p-4 backdrop-blur-sm"
            @click="expandedVideo = null"
        >
            <div
                class="relative mx-4 w-full max-w-3xl overflow-hidden rounded-2xl bg-black shadow-2xl"
                @click.stop
            >
                <video
                    :src="expandedVideo"
                    controls
                    autoplay
                    class="aspect-video w-full"
                ></video>
                <button
                    type="button"
                    class="absolute top-3 right-3 flex h-8 w-8 items-center justify-center rounded-full bg-black/75 text-white hover:bg-black"
                    @click="expandedVideo = null"
                >
                    <X class="h-4 w-4" />
                </button>
            </div>
        </div>

        <div
            v-if="reviewToDelete"
            class="fixed inset-0 z-[100] flex items-center justify-center bg-black/70 p-4 backdrop-blur-sm"
            @click="closeDeleteModal"
        >
            <div
                class="w-full max-w-md overflow-hidden rounded-2xl border border-outline-variant bg-surface-container-lowest p-6 shadow-2xl transition-all"
                @click.stop
            >
                <div class="flex items-start gap-4">
                    <div
                        class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-red-500/10 text-red-600 dark:bg-red-500/20 dark:text-red-400"
                    >
                        <AlertTriangle class="h-6 w-6" />
                    </div>
                    <div class="flex-1">
                        <h3
                            class="text-base font-black tracking-wide text-on-surface uppercase"
                        >
                            Delete Review
                        </h3>
                        <p
                            class="mt-2 text-xs leading-relaxed text-on-surface-variant"
                        >
                            Are you sure you want to permanently delete review
                            <span class="font-bold text-primary"
                                >#{{ reviewToDelete.id }}</span
                            >
                            by
                            <span class="font-bold text-primary">{{
                                reviewToDelete.user?.name || 'Anonymous User'
                            }}</span
                            >? This action cannot be undone.
                        </p>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button
                        type="button"
                        @click="closeDeleteModal"
                        :disabled="isDeleting"
                        class="rounded-lg border border-outline-variant bg-surface-container-lowest px-4 py-2.5 text-xs font-bold text-on-surface-variant uppercase transition-colors hover:bg-surface-container-low hover:text-primary disabled:opacity-50"
                    >
                        Cancel
                    </button>
                    <button
                        type="button"
                        @click="executeDelete"
                        :disabled="isDeleting"
                        class="inline-flex items-center gap-2 rounded-lg bg-red-600 px-4 py-2.5 text-xs font-black text-white uppercase transition-colors hover:bg-red-700 disabled:opacity-50"
                    >
                        <Trash2 class="h-4 w-4" />
                        <span>{{
                            isDeleting ? 'Deleting...' : 'Delete Review'
                        }}</span>
                    </button>
                </div>
            </div>
        </div>
    </Teleport>
</template>
