<script setup lang="ts">
import { Head, useForm, router, Link } from '@inertiajs/vue3';
import {
    Plus,
    Trash2,
    Send,
    XSquare,
    Edit,
    Gavel,
    Image as ImageIcon,
    Search,
} from 'lucide-vue-next';
import { ref, computed, watch } from 'vue';
import BidCard from '@/components/cards/BidCard.vue';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { formatPrice } from '@/lib/utils';
import {
    index as auctionsIndex,
    store as auctionsStore,
    update as auctionsUpdate,
    publish as auctionsPublish,
    close as auctionsClose,
    destroy as auctionsDestroy,
} from '@/routes/admin/auctions';

type Auction = {
    id: number;
    category: string;
    name: string;
    price: string | number;
    description: string;
    opening_points: number;
    current_points: number;
    status: 'draft' | 'active' | 'triggered' | 'closed';
    image: string | null;
    external_url: string | null;
    bid_count: number;
    countdown_duration_seconds: number;
    triggered_at: string | null;
    expires_at: string | null;
    winner_id: number | null;
};

const props = defineProps<{
    auctions: {
        data: Auction[];
        links: any[];
        current_page: number;
        last_page: number;
    };
    categories: string[];
    filters: {
        search: string | null;
        status: string | null;
    };
}>();

const searchForm = useForm({
    search: props.filters?.search || '',
    status: props.filters?.status || '',
});

const handleSearch = () => {
    searchForm.get(auctionsIndex.url(), {
        preserveState: true,
    });
};

const setStatusFilter = (status: string) => {
    searchForm.status = status;
    handleSearch();
};

const clearSearch = () => {
    searchForm.search = '';
    searchForm.status = '';
    handleSearch();
};

const showCreateModal = ref(false);
const showEditModal = ref(false);
const selectedAuction = ref<Auction | null>(null);

const form = useForm({
    category: '',
    name: '',
    price: 0,
    description: '',
    opening_points: 100,
    countdown_duration_seconds: 60,
    image: null as File | null,
    external_url: '',
});

const imagePreviewUrl = ref<string | null>(null);

const previewBid = computed(() => {
    return {
        id: selectedAuction.value?.id ?? 0,
        name: form.name || 'Preview Item Name',
        image:
            imagePreviewUrl.value ||
            (selectedAuction.value?.image
                ? selectedAuction.value.image.startsWith('http') ||
                  selectedAuction.value.image.startsWith('/')
                    ? selectedAuction.value.image
                    : `/storage/${selectedAuction.value.image}`
                : null),
        description: form.description || 'Preview description...',
        url: '#',
        price: form.price ? Number(form.price).toFixed(2) : '0.00',
        opening_points: form.opening_points || 100,
        rating: null,
        open_date: Date.now() / 1000,
        status: 0 as const,
        created_at: new Date().toISOString(),
        current_points: 0,
        expires_at: null,
        bid_count: 0,
    };
});

watch(
    () => form.image,
    (newImage) => {
        if (newImage instanceof File) {
            if (imagePreviewUrl.value) {
                URL.revokeObjectURL(imagePreviewUrl.value);
            }

            imagePreviewUrl.value = URL.createObjectURL(newImage);
        } else if (!newImage) {
            imagePreviewUrl.value = null;
        }
    },
);

const openCreateModal = () => {
    form.reset();

    if (imagePreviewUrl.value) {
        URL.revokeObjectURL(imagePreviewUrl.value);
    }

    imagePreviewUrl.value = null;
    showCreateModal.value = true;
};

const submitCreate = (publish = false) => {
    form.transform((data) => ({
        ...data,
        publish: publish,
    })).post(auctionsStore.url(), {
        onSuccess: () => {
            showCreateModal.value = false;
            form.reset();

            if (imagePreviewUrl.value) {
                URL.revokeObjectURL(imagePreviewUrl.value);
                imagePreviewUrl.value = null;
            }
        },
    });
};

const openEditModal = (auction: Auction) => {
    selectedAuction.value = auction;
    form.category = auction.category;
    form.name = auction.name;
    form.price = auction.price as any;
    form.description = auction.description;
    form.opening_points = auction.opening_points;
    form.countdown_duration_seconds = auction.countdown_duration_seconds;
    form.image = null;
    form.external_url = auction.external_url || '';

    if (imagePreviewUrl.value) {
        URL.revokeObjectURL(imagePreviewUrl.value);
    }

    imagePreviewUrl.value = null;
    showEditModal.value = true;
};

const submitEdit = (publish = false) => {
    if (!selectedAuction.value) {
        return;
    }

    form.transform((data) => {
        const payload: Record<string, any> = {
            ...data,
            _method: 'PUT',
            publish: publish,
        };

        if (!data.image) {
            delete payload.image;
        }

        return payload;
    }).post(auctionsUpdate.url(selectedAuction.value.id), {
        onSuccess: () => {
            showEditModal.value = false;

            if (imagePreviewUrl.value) {
                URL.revokeObjectURL(imagePreviewUrl.value);
                imagePreviewUrl.value = null;
            }
        },
    });
};

const publishAuction = (id: number) => {
    router.post(auctionsPublish.url(id));
};

const closeAuction = (id: number) => {
    if (
        confirm(
            'Are you sure you want to close this auction manually? This will finalize the winner.',
        )
    ) {
        router.post(auctionsClose.url(id));
    }
};

const deleteAuction = (id: number) => {
    if (confirm('Are you sure you want to delete this draft auction?')) {
        router.delete(auctionsDestroy.url(id));
    }
};

const handleImageChange = (e: Event) => {
    const target = e.target as HTMLInputElement;

    if (target.files && target.files[0]) {
        form.image = target.files[0];
    }
};
</script>

<template>
    <Head title="Admin - Auctions" />

    <AdminLayout :breadcrumbs="[{ title: 'Auctions' }]">
        <div class="flex flex-col gap-6 font-sans text-xs">
            <!-- Header bar -->
            <div
                class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center"
            >
                <div>
                    <h1
                        class="flex items-center gap-2 text-2xl font-black tracking-tight text-primary uppercase"
                    >
                        <Gavel class="h-6 w-6 text-primary" />
                        <span>Auction Management</span>
                    </h1>
                    <p class="mt-1 text-xs text-on-surface-variant">
                        Manage draft, active, triggered, and closed bidding
                        items.
                    </p>
                </div>
                <button
                    @click="openCreateModal"
                    class="flex items-center justify-center gap-2 rounded-lg bg-secondary px-5 py-3 text-xs font-black tracking-wider text-on-secondary-fixed uppercase shadow-sm transition-all hover:scale-[1.01] hover:bg-secondary/90 active:scale-95"
                >
                    <Plus class="h-4 w-4" />
                    <span>Create Auction</span>
                </button>
            </div>

            <!-- Search and Filter Tabs Block -->
            <div class="flex flex-col gap-4">
                <form
                    @submit.prevent="handleSearch"
                    class="flex flex-col gap-3 sm:flex-row"
                >
                    <div class="relative flex-1">
                        <input
                            v-model="searchForm.search"
                            type="text"
                            placeholder="SEARCH AUCTIONS BY NAME, CATEGORY, OR DESCRIPTION..."
                            class="w-full rounded-lg border border-outline-variant bg-surface-container-low py-3 pr-4 pl-10 text-xs font-bold text-on-surface transition-all focus:ring-1 focus:ring-secondary focus:outline-none"
                        />
                        <Search
                            class="absolute top-3.5 left-3.5 h-4 w-4 text-on-surface-variant"
                        />
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

                <!-- Status Filter Tabs from Mockup -->
                <div
                    class="flex w-fit items-center gap-1 rounded-lg border border-outline-variant bg-surface-container-low p-1"
                >
                    <button
                        v-for="statusOpt in ['', 'active', 'closed', 'draft']"
                        :key="statusOpt"
                        @click="setStatusFilter(statusOpt)"
                        :class="[
                            'rounded-md px-4 py-1.5 text-[10px] font-bold uppercase transition-all',
                            searchForm.status === statusOpt
                                ? 'bg-surface-container-lowest font-black text-primary shadow-sm'
                                : 'text-on-surface-variant hover:bg-surface-variant/40',
                        ]"
                    >
                        {{ statusOpt === '' ? 'ALL' : statusOpt }}
                    </button>
                </div>
            </div>

            <!-- Auctions Grid/Table List -->
            <div
                class="flex flex-col overflow-hidden rounded-xl border border-outline-variant bg-surface-container-lowest shadow-sm"
            >
                <div class="overflow-x-auto">
                    <table
                        class="w-full min-w-[800px] border-collapse text-left"
                    >
                        <thead
                            class="border-b border-outline-variant bg-surface-container-low"
                        >
                            <tr
                                class="text-[10px] font-bold tracking-widest text-on-surface-variant uppercase"
                            >
                                <th class="px-6 py-4">Item Details</th>
                                <th class="px-6 py-4">Category</th>
                                <th class="px-6 py-4">Status</th>
                                <th class="px-6 py-4">Threshold</th>
                                <th class="w-48 px-6 py-4">Progress</th>
                                <th class="px-6 py-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant">
                            <tr
                                v-for="auction in auctions.data"
                                :key="auction.id"
                                class="group transition-colors hover:bg-surface-container-low/20"
                            >
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-4">
                                        <div
                                            class="flex h-14 w-14 flex-shrink-0 items-center justify-center overflow-hidden rounded-lg border border-outline-variant bg-surface-container-low transition-transform group-hover:scale-105"
                                        >
                                            <img
                                                v-if="auction.image"
                                                :src="
                                                    auction.image.startsWith(
                                                        'http',
                                                    ) ||
                                                    auction.image.startsWith(
                                                        '/',
                                                    )
                                                        ? auction.image
                                                        : `/storage/${auction.image}`
                                                "
                                                class="h-full w-full object-cover"
                                            />
                                            <ImageIcon
                                                v-else
                                                class="h-6 w-6 text-on-surface-variant/40"
                                            />
                                        </div>
                                        <div>
                                            <p
                                                class="leading-tight font-bold text-primary transition-colors group-hover:text-on-secondary-container"
                                            >
                                                {{ auction.name }}
                                            </p>
                                            <p
                                                class="mt-1 line-clamp-1 max-w-xs text-[10px] text-on-surface-variant"
                                            >
                                                {{ auction.description }}
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                <td
                                    class="px-6 py-4 text-[10px] font-bold tracking-wide text-on-surface uppercase"
                                >
                                    {{ auction.category }}
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        :class="[
                                            'rounded-full border px-2.5 py-1 text-[9px] font-black tracking-wider uppercase',
                                            auction.status === 'draft' &&
                                                'border-outline/30 bg-surface-variant text-on-surface-variant',
                                            auction.status === 'active' &&
                                                'border-secondary/20 bg-secondary-container text-on-secondary-container',
                                            auction.status === 'triggered' &&
                                                'animate-pulse border-amber-300 bg-amber-100 text-amber-800',
                                            auction.status === 'closed' &&
                                                'border-outline-variant bg-surface-container-low text-on-surface-variant',
                                        ]"
                                    >
                                        {{ auction.status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-bold text-primary">
                                        {{ auction.opening_points }}
                                        <span
                                            class="text-[10px] font-normal text-on-surface-variant"
                                            >PTS</span
                                        >
                                    </div>
                                    <div
                                        class="mt-0.5 text-[10px] font-medium text-on-surface-variant"
                                    >
                                        {{ formatPrice(auction.price) }}
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="space-y-1.5">
                                        <div
                                            class="flex justify-between text-[9px] font-bold tracking-widest text-on-surface-variant uppercase"
                                        >
                                            <span
                                                >{{
                                                    Math.round(
                                                        (auction.current_points /
                                                            auction.opening_points) *
                                                            100,
                                                    )
                                                }}%</span
                                            >
                                            <span
                                                >{{
                                                    auction.current_points
                                                }}
                                                pts</span
                                            >
                                        </div>
                                        <div
                                            class="h-1.5 w-full overflow-hidden rounded-full bg-surface-container"
                                        >
                                            <div
                                                class="h-full rounded-full bg-secondary shadow-[0_0_8px_rgba(200,224,0,0.5)]"
                                                :style="{
                                                    width:
                                                        Math.min(
                                                            (auction.current_points /
                                                                auction.opening_points) *
                                                                100,
                                                            100,
                                                        ) + '%',
                                                }"
                                            ></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end gap-2">
                                        <template
                                            v-if="auction.status === 'draft'"
                                        >
                                            <button
                                                @click="openEditModal(auction)"
                                                class="rounded-lg border border-outline-variant bg-surface-container-lowest p-2 text-on-surface-variant transition-colors hover:bg-surface-container-low hover:text-primary"
                                                title="Edit Draft"
                                            >
                                                <Edit class="h-4 w-4" />
                                            </button>
                                            <button
                                                @click="
                                                    publishAuction(auction.id)
                                                "
                                                class="rounded-lg border border-outline-variant bg-surface-container-lowest p-2 text-on-surface-variant transition-colors hover:border-secondary/30 hover:bg-secondary-container hover:text-on-secondary-container"
                                                title="Publish"
                                            >
                                                <Send class="h-4 w-4" />
                                            </button>
                                            <button
                                                @click="
                                                    deleteAuction(auction.id)
                                                "
                                                class="rounded-lg border border-outline-variant bg-surface-container-lowest p-2 text-on-surface-variant transition-colors hover:border-error/20 hover:bg-error-container/20 hover:text-error"
                                                title="Delete"
                                            >
                                                <Trash2 class="h-4 w-4" />
                                            </button>
                                        </template>

                                        <template
                                            v-if="
                                                auction.status === 'active' ||
                                                auction.status === 'triggered'
                                            "
                                        >
                                            <button
                                                @click="
                                                    closeAuction(auction.id)
                                                "
                                                class="flex items-center gap-1 rounded-lg border border-error/20 px-3 py-1.5 text-[10px] font-bold text-error uppercase transition-colors hover:bg-error-container/20"
                                                title="Close Manually"
                                            >
                                                <XSquare class="h-3.5 w-3.5" />
                                                <span>Close</span>
                                            </button>
                                        </template>

                                        <template
                                            v-if="auction.status === 'closed'"
                                        >
                                            <span
                                                class="text-[10px] font-bold tracking-widest text-on-surface-variant uppercase"
                                                >Archived</span
                                            >
                                        </template>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="auctions.data.length === 0">
                                <td
                                    colspan="6"
                                    class="px-6 py-12 text-center font-bold tracking-wider text-on-surface-variant uppercase"
                                >
                                    No auctions found matching criteria.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination footer links from mockup style -->
                <div
                    v-if="auctions.last_page > 1"
                    class="flex items-center justify-between border-t border-outline-variant bg-surface-container-low px-6 py-4"
                >
                    <p
                        class="text-[10px] font-bold tracking-widest text-on-surface-variant uppercase"
                    >
                        Page {{ auctions.current_page }} of
                        {{ auctions.last_page }}
                    </p>
                    <div class="flex gap-1">
                        <Link
                            v-for="link in auctions.links"
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

        <!-- Create Auction Modal -->
        <div
            v-if="showCreateModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-on-background/40 p-4 backdrop-blur-sm"
        >
            <div
                class="flex max-h-[90vh] w-full max-w-4xl flex-col gap-4 overflow-y-auto rounded-xl border border-outline-variant bg-surface-container-lowest p-6 shadow-xl"
            >
                <div
                    class="flex items-center justify-between border-b border-outline-variant pb-4"
                >
                    <h2
                        class="text-sm font-black tracking-widest text-primary uppercase"
                    >
                        Create Draft Auction
                    </h2>
                    <button
                        @click="showCreateModal = false"
                        class="text-on-surface-variant hover:text-primary"
                    >
                        <XSquare class="h-5 w-5" />
                    </button>
                </div>

                <div class="grid grid-cols-1 items-start gap-6 md:grid-cols-2">
                    <!-- Form side -->
                    <form
                        @submit.prevent="submitCreate(false)"
                        class="space-y-4 text-xs font-bold"
                    >
                        <div class="flex flex-col gap-2">
                            <label
                                class="text-[10px] tracking-wider text-on-surface-variant uppercase"
                                >Category Name</label
                            >
                            <input
                                v-model="form.category"
                                type="text"
                                required
                                list="categories-list"
                                class="w-full rounded-lg border border-outline-variant bg-surface-container-low px-4 py-3 text-on-surface focus:border-secondary focus:outline-none"
                                placeholder="e.g. Electronics, Fashion"
                            />
                            <datalist id="categories-list">
                                <option
                                    v-for="cat in props.categories"
                                    :key="cat"
                                    :value="cat"
                                />
                            </datalist>
                        </div>

                        <div class="flex flex-col gap-2">
                            <label
                                class="text-[10px] tracking-wider text-on-surface-variant uppercase"
                                >Item Title</label
                            >
                            <input
                                v-model="form.name"
                                type="text"
                                required
                                class="w-full rounded-lg border border-outline-variant bg-surface-container-low px-4 py-3 text-on-surface focus:border-secondary focus:outline-none"
                                placeholder="e.g. Gucci Leather Bag"
                            />
                        </div>

                        <div class="flex flex-col gap-2">
                            <label
                                class="text-[10px] tracking-wider text-on-surface-variant uppercase"
                                >Description</label
                            >
                            <textarea
                                v-model="form.description"
                                required
                                rows="3"
                                class="w-full resize-none rounded-lg border border-outline-variant bg-surface-container-low px-4 py-3 font-medium text-on-surface focus:border-secondary focus:outline-none"
                                placeholder="Write comprehensive item description..."
                            ></textarea>
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                            <div class="flex flex-col gap-2">
                                <label
                                    class="text-[10px] tracking-wider text-on-surface-variant uppercase"
                                    >Price (₦)</label
                                >
                                <input
                                    v-model="form.price"
                                    type="number"
                                    required
                                    min="0"
                                    step="0.01"
                                    class="w-full rounded-lg border border-outline-variant bg-surface-container-low px-4 py-3 text-on-surface focus:border-secondary focus:outline-none"
                                    placeholder="e.g. 50000"
                                />
                            </div>
                            <div class="flex flex-col gap-2">
                                <label
                                    class="text-[10px] tracking-wider text-on-surface-variant uppercase"
                                    >Threshold Points</label
                                >
                                <input
                                    v-model="form.opening_points"
                                    type="number"
                                    required
                                    min="10"
                                    class="w-full rounded-lg border border-outline-variant bg-surface-container-low px-4 py-3 text-on-surface focus:border-secondary focus:outline-none"
                                />
                            </div>
                            <div class="flex flex-col gap-2">
                                <label
                                    class="text-[10px] tracking-wider text-on-surface-variant uppercase"
                                    >Countdown (Seconds)</label
                                >
                                <input
                                    v-model="form.countdown_duration_seconds"
                                    type="number"
                                    required
                                    min="10"
                                    class="w-full rounded-lg border border-outline-variant bg-surface-container-low px-4 py-3 text-on-surface focus:border-secondary focus:outline-none"
                                />
                            </div>
                        </div>

                        <div class="flex flex-col gap-2">
                            <label
                                class="text-[10px] tracking-wider text-on-surface-variant uppercase"
                                >Upload Item Image</label
                            >
                            <input
                                type="file"
                                required
                                accept="image/*"
                                @change="handleImageChange"
                                class="w-full rounded-lg border border-outline-variant bg-surface-container-low px-4 py-3 text-on-surface-variant focus:outline-none"
                            />
                        </div>

                        <div class="flex flex-col gap-2">
                            <label
                                class="text-[10px] tracking-wider text-on-surface-variant uppercase"
                                >External Sourcing URL (Optional)</label
                            >
                            <input
                                v-model="form.external_url"
                                type="url"
                                class="w-full rounded-lg border border-outline-variant bg-surface-container-low px-4 py-3 text-on-surface focus:border-secondary focus:outline-none"
                                placeholder="e.g. https://partner-site.com/item"
                            />
                        </div>

                        <div
                            class="flex flex-wrap justify-end gap-3 border-t border-outline-variant pt-4"
                        >
                            <button
                                type="button"
                                @click="showCreateModal = false"
                                class="rounded-lg border border-outline-variant px-5 py-3 text-on-surface-variant uppercase transition-colors hover:text-primary"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="rounded-lg border border-outline-variant bg-surface-container-low px-5 py-3 text-on-surface uppercase transition-colors hover:bg-surface-container-high"
                            >
                                Save Draft
                            </button>
                            <button
                                type="button"
                                @click="submitCreate(true)"
                                :disabled="form.processing"
                                class="rounded-lg bg-secondary px-5 py-3 font-black text-on-secondary-fixed uppercase transition-all hover:bg-secondary/90 active:scale-[0.98] disabled:opacity-50"
                            >
                                Save & Publish
                            </button>
                        </div>
                    </form>

                    <!-- Preview side -->
                    <div
                        class="flex flex-col gap-3 rounded-xl border border-outline-variant bg-surface-container-low p-4 md:sticky md:top-0"
                    >
                        <span
                            class="text-[10px] font-black tracking-wider text-on-surface-variant uppercase"
                            >Live Bid Card Preview</span
                        >
                        <div class="mx-auto w-[280px] max-w-full">
                            <BidCard :bid="previewBid" :is-preview="true" />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Auction Modal -->
        <div
            v-if="showEditModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-on-background/40 p-4 backdrop-blur-sm"
        >
            <div
                class="flex max-h-[90vh] w-full max-w-4xl flex-col gap-4 overflow-y-auto rounded-xl border border-outline-variant bg-surface-container-lowest p-6 shadow-xl"
            >
                <div
                    class="flex items-center justify-between border-b border-outline-variant pb-4"
                >
                    <h2
                        class="text-sm font-black tracking-widest text-primary uppercase"
                    >
                        Edit Draft Auction
                    </h2>
                    <button
                        @click="showEditModal = false"
                        class="text-on-surface-variant hover:text-primary"
                    >
                        <XSquare class="h-5 w-5" />
                    </button>
                </div>

                <div class="grid grid-cols-1 items-start gap-6 md:grid-cols-2">
                    <!-- Form side -->
                    <form
                        @submit.prevent="submitEdit(false)"
                        class="space-y-4 text-xs font-bold"
                    >
                        <div class="flex flex-col gap-2">
                            <label
                                class="text-[10px] tracking-wider text-on-surface-variant uppercase"
                                >Category Name</label
                            >
                            <input
                                v-model="form.category"
                                type="text"
                                required
                                list="categories-list"
                                class="w-full rounded-lg border border-outline-variant bg-surface-container-low px-4 py-3 text-on-surface focus:border-secondary focus:outline-none"
                            />
                        </div>

                        <div class="flex flex-col gap-2">
                            <label
                                class="text-[10px] tracking-wider text-on-surface-variant uppercase"
                                >Item Title</label
                            >
                            <input
                                v-model="form.name"
                                type="text"
                                required
                                class="w-full rounded-lg border border-outline-variant bg-surface-container-low px-4 py-3 text-on-surface focus:border-secondary focus:outline-none"
                            />
                        </div>

                        <div class="flex flex-col gap-2">
                            <label
                                class="text-[10px] tracking-wider text-on-surface-variant uppercase"
                                >Description</label
                            >
                            <textarea
                                v-model="form.description"
                                required
                                rows="3"
                                class="w-full resize-none rounded-lg border border-outline-variant bg-surface-container-low px-4 py-3 font-medium text-on-surface focus:border-secondary focus:outline-none"
                            ></textarea>
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                            <div class="flex flex-col gap-2">
                                <label
                                    class="text-[10px] tracking-wider text-on-surface-variant uppercase"
                                    >Price (₦)</label
                                >
                                <input
                                    v-model="form.price"
                                    type="number"
                                    required
                                    min="0"
                                    step="0.01"
                                    class="w-full rounded-lg border border-outline-variant bg-surface-container-low px-4 py-3 text-on-surface focus:border-secondary focus:outline-none"
                                    placeholder="e.g. 50000"
                                />
                            </div>
                            <div class="flex flex-col gap-2">
                                <label
                                    class="text-[10px] tracking-wider text-on-surface-variant uppercase"
                                    >Threshold Points</label
                                >
                                <input
                                    v-model="form.opening_points"
                                    type="number"
                                    required
                                    min="10"
                                    class="w-full rounded-lg border border-outline-variant bg-surface-container-low px-4 py-3 text-on-surface focus:border-secondary focus:outline-none"
                                />
                            </div>
                            <div class="flex flex-col gap-2">
                                <label
                                    class="text-[10px] tracking-wider text-on-surface-variant uppercase"
                                    >Countdown (Seconds)</label
                                >
                                <input
                                    v-model="form.countdown_duration_seconds"
                                    type="number"
                                    required
                                    min="10"
                                    class="w-full rounded-lg border border-outline-variant bg-surface-container-low px-4 py-3 text-on-surface focus:border-secondary focus:outline-none"
                                />
                            </div>
                        </div>

                        <div class="flex flex-col gap-2">
                            <label
                                class="text-[10px] tracking-wider text-on-surface-variant uppercase"
                                >Update Image (Optional)</label
                            >
                            <input
                                type="file"
                                accept="image/*"
                                @change="handleImageChange"
                                class="w-full rounded-lg border border-outline-variant bg-surface-container-low px-4 py-3 text-on-surface-variant focus:outline-none"
                            />
                            <p
                                class="mt-0.5 text-[9px] text-on-surface-variant/70 italic"
                            >
                                Leave blank if you wish to keep the existing
                                image.
                            </p>
                        </div>

                        <div class="flex flex-col gap-2">
                            <label
                                class="text-[10px] tracking-wider text-on-surface-variant uppercase"
                                >External Sourcing URL (Optional)</label
                            >
                            <input
                                v-model="form.external_url"
                                type="url"
                                class="w-full rounded-lg border border-outline-variant bg-surface-container-low px-4 py-3 text-on-surface focus:border-secondary focus:outline-none"
                                placeholder="e.g. https://partner-site.com/item"
                            />
                        </div>

                        <div
                            class="flex flex-wrap justify-end gap-3 border-t border-outline-variant pt-4"
                        >
                            <button
                                type="button"
                                @click="showEditModal = false"
                                class="rounded-lg border border-outline-variant px-5 py-3 text-on-surface-variant uppercase transition-colors hover:text-primary"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="rounded-lg border border-outline-variant bg-surface-container-low px-5 py-3 text-on-surface uppercase transition-colors hover:bg-surface-container-high"
                            >
                                Save Changes
                            </button>
                            <button
                                type="button"
                                @click="submitEdit(true)"
                                :disabled="form.processing"
                                class="rounded-lg bg-secondary px-5 py-3 font-black text-on-secondary-fixed uppercase transition-all hover:bg-secondary/90 active:scale-[0.98] disabled:opacity-50"
                            >
                                Save & Publish
                            </button>
                        </div>
                    </form>

                    <!-- Preview side -->
                    <div
                        class="flex flex-col gap-3 rounded-xl border border-outline-variant bg-surface-container-low p-4 md:sticky md:top-0"
                    >
                        <span
                            class="text-[10px] font-black tracking-wider text-on-surface-variant uppercase"
                            >Live Bid Card Preview</span
                        >
                        <div class="mx-auto w-[280px] max-w-full">
                            <BidCard :bid="previewBid" :is-preview="true" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
