<script setup lang="ts">
import { Head, useForm, router, Link } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { 
    Star, 
    Search,
    Eye,
    EyeOff,
    Filter
} from 'lucide-vue-next';

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
};

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
    searchForm.get('/admin/reviews', {
        preserveState: true,
    });
};

const clearSearch = () => {
    searchForm.search = '';
    searchForm.visibility = '';
    handleSearch();
};

const toggleReviewVisibility = (review: Review) => {
    router.post(`/admin/reviews/${review.id}/toggle-visibility`, {}, {
        preserveScroll: true
    });
};
</script>

<template>
    <Head title="Admin - Review Moderation" />

    <AdminLayout :breadcrumbs="[{ title: 'Reviews' }]">
        <div class="flex flex-col gap-6 font-sans text-xs">
            <!-- Header bar -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-black tracking-tight text-primary uppercase flex items-center gap-2">
                        <Star class="w-6 h-6 text-primary" />
                        <span>Review Moderation Panel</span>
                    </h1>
                    <p class="text-xs text-on-surface-variant mt-1">Moderate user feedback comments, ratings, and toggle visibility on the public landing page.</p>
                </div>
            </div>

            <!-- Search and Filter Panel -->
            <div class="bg-surface-container-lowest border border-outline-variant p-4 rounded-xl shadow-sm">
                <form @submit.prevent="handleSearch" class="flex flex-col md:flex-row gap-3">
                    <div class="flex-1 relative">
                        <input 
                            v-model="searchForm.search"
                            type="text"
                            placeholder="SEARCH BY USER PHONE OR REVIEW COMMENT..."
                            class="w-full bg-surface-container-low border border-outline-variant text-on-surface pl-10 pr-4 py-3 rounded-lg focus:outline-none focus:ring-1 focus:ring-secondary text-xs font-bold"
                        />
                        <Search class="w-4 h-4 text-on-surface-variant absolute left-3.5 top-3.5" />
                    </div>

                    <div class="w-full md:w-48">
                        <select 
                            v-model="searchForm.visibility"
                            class="w-full bg-surface-container-low border border-outline-variant text-on-surface px-4 py-3 rounded-lg focus:outline-none focus:ring-1 focus:ring-secondary text-xs font-bold uppercase"
                        >
                            <option value="">ALL VISIBILITY</option>
                            <option value="true">VISIBLE ON FEED</option>
                            <option value="false">HIDDEN FROM FEED</option>
                        </select>
                    </div>

                    <div class="flex gap-2">
                        <button 
                            type="submit"
                            class="px-5 py-3 bg-secondary text-on-secondary-fixed font-black uppercase rounded-lg transition-all"
                        >
                            Filter
                        </button>
                        <button 
                            type="button"
                            @click="clearSearch"
                            class="px-5 py-3 border border-outline-variant bg-surface-container-lowest text-on-surface-variant hover:text-primary font-bold uppercase rounded-lg transition-all"
                        >
                            Reset
                        </button>
                    </div>
                </form>
            </div>

            <!-- Reviews List Table -->
            <div class="bg-surface-container-lowest rounded-xl border border-outline-variant overflow-hidden shadow-sm flex flex-col">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse min-w-[900px]">
                        <thead class="bg-surface-container-low border-b border-outline-variant">
                            <tr class="text-[10px] font-bold text-on-surface-variant uppercase tracking-widest">
                                <th class="px-6 py-4">Review ID</th>
                                <th class="px-6 py-4">User Info</th>
                                <th class="px-6 py-4 text-center">Rating</th>
                                <th class="px-6 py-4 w-1/3">Comment</th>
                                <th class="px-6 py-4">Auction Item</th>
                                <th class="px-6 py-4">Social Link</th>
                                <th class="px-6 py-4 text-right">Visibility</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant">
                            <tr v-for="review in reviews.data" :key="review.id" class="hover:bg-surface-container-low/20 transition-colors group">
                                <td class="px-6 py-4 font-bold text-on-surface-variant">#{{ review.id }}</td>
                                <td class="px-6 py-4">
                                    <div v-if="review.user" class="flex flex-col">
                                        <span class="font-bold text-primary">{{ review.user.name }}</span>
                                        <span class="text-[10px] text-on-surface-variant mt-0.5">{{ review.user.phone }}</span>
                                    </div>
                                    <span v-else class="text-on-surface-variant italic font-semibold">Anonymous User</span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex items-center justify-center gap-0.5 text-amber-500">
                                        <Star 
                                            v-for="i in 5" 
                                            :key="i"
                                            class="w-3.5 h-3.5"
                                            :class="[i <= Math.round(review.rating) ? 'fill-amber-500 text-amber-500' : 'text-surface-variant']"
                                        />
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-on-surface font-medium leading-normal py-4 font-sans">{{ review.comment }}</td>
                                <td class="px-6 py-4">
                                    <span class="font-bold text-primary" v-if="review.auction">{{ review.auction.name }}</span>
                                    <span class="text-on-surface-variant italic font-semibold" v-else>Direct Feedback</span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col" v-if="review.social_handle">
                                        <span class="text-primary text-[10px] font-black uppercase tracking-widest">{{ review.social_platform }}</span>
                                        <span class="text-[10px] text-on-surface-variant mt-0.5 select-all font-semibold">{{ review.social_handle }}</span>
                                    </div>
                                    <span class="text-on-surface-variant italic font-semibold" v-else>No Social Linked</span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <button 
                                        @click="toggleReviewVisibility(review)"
                                        :class="[
                                            'px-3 py-1.5 border text-[10px] font-bold uppercase rounded-lg transition-colors inline-flex items-center gap-1.5',
                                            review.is_visible 
                                                ? 'bg-secondary-container text-on-secondary-container border-secondary/20 hover:bg-secondary-container/85' 
                                                : 'border-outline-variant text-on-surface-variant hover:text-primary hover:bg-surface-container-low'
                                        ]"
                                        :title="review.is_visible ? 'Hide review from home page feed' : 'Show review on home page feed'"
                                    >
                                        <component :is="review.is_visible ? Eye : EyeOff" class="w-3.5 h-3.5" />
                                        <span>{{ review.is_visible ? 'Visible' : 'Hidden' }}</span>
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="reviews.data.length === 0">
                                <td colspan="7" class="px-6 py-12 text-center text-on-surface-variant uppercase tracking-widest font-bold">
                                    No customer reviews posted yet.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination footer links -->
                <div v-if="reviews.last_page > 1" class="px-6 py-4 bg-surface-container-low flex justify-between items-center border-t border-outline-variant">
                    <p class="text-[10px] text-on-surface-variant font-bold uppercase tracking-widest">
                        Page {{ reviews.current_page }} of {{ reviews.last_page }}
                    </p>
                    <div class="flex gap-1">
                        <Link 
                            v-for="link in reviews.links"
                            :key="link.label"
                            :href="link.url || '#'"
                            v-html="link.label"
                            :class="[
                                'px-3 py-1.5 border text-[10px] font-bold rounded-lg transition-all',
                                link.active 
                                    ? 'bg-primary text-on-primary border-primary' 
                                    : 'text-on-surface-variant border-outline-variant bg-surface-container-lowest hover:bg-surface-container-low',
                                !link.url && 'opacity-40 cursor-not-allowed'
                            ]"
                        />
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
