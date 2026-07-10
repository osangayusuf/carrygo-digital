<script setup lang="ts">
import { Head, useForm, router, Link } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { ref } from 'vue';
import { index as auctionsIndex, store as auctionsStore, update as auctionsUpdate, publish as auctionsPublish, close as auctionsClose, destroy as auctionsDestroy } from '@/routes/admin/auctions';
import { 
    Plus, 
    Trash2, 
    Send, 
    XSquare, 
    Edit, 
    Gavel,
    Image as ImageIcon,
    Search
} from 'lucide-vue-next';
import { formatPrice } from '@/lib/utils';

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
});

const openCreateModal = () => {
    form.reset();
    showCreateModal.value = true;
};

const submitCreate = () => {
    form.post(auctionsStore.url(), {
        onSuccess: () => {
            showCreateModal.value = false;
            form.reset();
        }
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
    showEditModal.value = true;
};

const submitEdit = () => {
    if (!selectedAuction.value) return;
    
    router.post(auctionsUpdate.url(selectedAuction.value.id), {
        _method: 'PUT',
        category: form.category,
        name: form.name,
        price: form.price,
        description: form.description,
        opening_points: form.opening_points,
        countdown_duration_seconds: form.countdown_duration_seconds,
        image: form.image,
    }, {
        onSuccess: () => {
            showEditModal.value = false;
        }
    });
};

const publishAuction = (id: number) => {
    router.post(auctionsPublish.url(id));
};

const closeAuction = (id: number) => {
    if (confirm('Are you sure you want to close this auction manually? This will finalize the winner.')) {
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
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-black tracking-tight text-primary uppercase flex items-center gap-2">
                        <Gavel class="w-6 h-6 text-primary" />
                        <span>Auction Management</span>
                    </h1>
                    <p class="text-xs text-on-surface-variant mt-1">Manage draft, active, triggered, and closed bidding items.</p>
                </div>
                <button 
                    @click="openCreateModal"
                    class="flex items-center justify-center gap-2 px-5 py-3 bg-secondary hover:bg-secondary/90 text-on-secondary-fixed font-black text-xs uppercase tracking-wider rounded-lg shadow-sm hover:scale-[1.01] active:scale-95 transition-all"
                >
                    <Plus class="w-4 h-4" />
                    <span>Create Auction</span>
                </button>
            </div>

            <!-- Search and Filter Tabs Block -->
            <div class="flex flex-col gap-4">
                <form @submit.prevent="handleSearch" class="flex flex-col sm:flex-row gap-3">
                    <div class="flex-1 relative">
                        <input 
                            v-model="searchForm.search"
                            type="text"
                            placeholder="SEARCH AUCTIONS BY NAME, CATEGORY, OR DESCRIPTION..."
                            class="w-full bg-surface-container-low border border-outline-variant text-on-surface pl-10 pr-4 py-3 rounded-lg focus:outline-none focus:ring-1 focus:ring-secondary transition-all text-xs font-bold"
                        />
                        <Search class="w-4 h-4 text-on-surface-variant absolute left-3.5 top-3.5" />
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

                <!-- Status Filter Tabs from Mockup -->
                <div class="flex items-center gap-1 bg-surface-container-low p-1 rounded-lg border border-outline-variant w-fit">
                    <button 
                        v-for="statusOpt in ['', 'active', 'closed', 'draft']" 
                        :key="statusOpt"
                        @click="setStatusFilter(statusOpt)"
                        :class="[
                            'px-4 py-1.5 rounded-md text-[10px] font-bold uppercase transition-all',
                            searchForm.status === statusOpt 
                                ? 'bg-surface-container-lowest text-primary shadow-sm font-black' 
                                : 'text-on-surface-variant hover:bg-surface-variant/40'
                        ]"
                    >
                        {{ statusOpt === '' ? 'ALL' : statusOpt }}
                    </button>
                </div>
            </div>

            <!-- Auctions Grid/Table List -->
            <div class="bg-surface-container-lowest rounded-xl border border-outline-variant overflow-hidden shadow-sm flex flex-col">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse min-w-[800px]">
                        <thead class="bg-surface-container-low border-b border-outline-variant">
                            <tr class="text-[10px] font-bold text-on-surface-variant uppercase tracking-widest">
                                <th class="px-6 py-4">Item Details</th>
                                <th class="px-6 py-4">Category</th>
                                <th class="px-6 py-4">Status</th>
                                <th class="px-6 py-4">Threshold</th>
                                <th class="px-6 py-4 w-48">Progress</th>
                                <th class="px-6 py-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant">
                            <tr v-for="auction in auctions.data" :key="auction.id" class="hover:bg-surface-container-low/20 transition-colors group">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-4">
                                        <div class="w-14 h-14 rounded-lg bg-surface-container-low border border-outline-variant flex items-center justify-center overflow-hidden flex-shrink-0 transition-transform group-hover:scale-105">
                                            <img v-if="auction.image" :src="auction.image.startsWith('http') || auction.image.startsWith('/') ? auction.image : `/storage/${auction.image}`" class="w-full h-full object-cover" />
                                            <ImageIcon v-else class="w-6 h-6 text-on-surface-variant/40" />
                                        </div>
                                        <div>
                                            <p class="font-bold text-primary group-hover:text-on-secondary-container transition-colors leading-tight">{{ auction.name }}</p>
                                            <p class="text-[10px] text-on-surface-variant mt-1 line-clamp-1 max-w-xs">{{ auction.description }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-[10px] font-bold text-on-surface uppercase tracking-wide">{{ auction.category }}</td>
                                <td class="px-6 py-4">
                                    <span 
                                        :class="[
                                            'px-2.5 py-1 text-[9px] font-black uppercase tracking-wider border rounded-full',
                                            auction.status === 'draft' && 'bg-surface-variant text-on-surface-variant border-outline/30',
                                            auction.status === 'active' && 'bg-secondary-container text-on-secondary-container border-secondary/20',
                                            auction.status === 'triggered' && 'bg-amber-100 text-amber-800 border-amber-300 animate-pulse',
                                            auction.status === 'closed' && 'bg-surface-container-low text-on-surface-variant border-outline-variant',
                                        ]"
                                    >
                                        {{ auction.status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-bold text-primary">{{ auction.opening_points }} <span class="text-[10px] text-on-surface-variant font-normal">PTS</span></div>
                                    <div class="text-[10px] text-on-surface-variant mt-0.5 font-medium">{{ formatPrice(auction.price) }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="space-y-1.5">
                                        <div class="flex justify-between text-[9px] font-bold text-on-surface-variant uppercase tracking-widest">
                                            <span>{{ Math.round((auction.current_points / auction.opening_points) * 100) }}%</span>
                                            <span>{{ auction.current_points }} pts</span>
                                        </div>
                                        <div class="w-full h-1.5 bg-surface-container rounded-full overflow-hidden">
                                            <div 
                                                class="h-full bg-secondary rounded-full shadow-[0_0_8px_rgba(200,224,0,0.5)]" 
                                                :style="{ width: Math.min((auction.current_points / auction.opening_points) * 100, 100) + '%' }"
                                            ></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end gap-2">
                                        <template v-if="auction.status === 'draft'">
                                            <button 
                                                @click="openEditModal(auction)"
                                                class="p-2 border border-outline-variant bg-surface-container-lowest hover:bg-surface-container-low rounded-lg text-on-surface-variant hover:text-primary transition-colors"
                                                title="Edit Draft"
                                            >
                                                <Edit class="w-4 h-4" />
                                            </button>
                                            <button 
                                                @click="publishAuction(auction.id)"
                                                class="p-2 border border-outline-variant bg-surface-container-lowest hover:bg-secondary-container hover:border-secondary/30 rounded-lg text-on-surface-variant hover:text-on-secondary-container transition-colors"
                                                title="Publish"
                                            >
                                                <Send class="w-4 h-4" />
                                            </button>
                                            <button 
                                                @click="deleteAuction(auction.id)"
                                                class="p-2 border border-outline-variant bg-surface-container-lowest hover:bg-error-container/20 hover:border-error/20 rounded-lg text-on-surface-variant hover:text-error transition-colors"
                                                title="Delete"
                                            >
                                                <Trash2 class="w-4 h-4" />
                                            </button>
                                        </template>

                                        <template v-if="auction.status === 'active' || auction.status === 'triggered'">
                                            <button 
                                                @click="closeAuction(auction.id)"
                                                class="px-3 py-1.5 border border-error/20 text-error hover:bg-error-container/20 text-[10px] font-bold uppercase rounded-lg transition-colors flex items-center gap-1"
                                                title="Close Manually"
                                            >
                                                <XSquare class="w-3.5 h-3.5" />
                                                <span>Close</span>
                                            </button>
                                        </template>

                                        <template v-if="auction.status === 'closed'">
                                            <span class="text-[10px] text-on-surface-variant font-bold uppercase tracking-widest">Archived</span>
                                        </template>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="auctions.data.length === 0">
                                <td colspan="6" class="px-6 py-12 text-center text-on-surface-variant uppercase tracking-wider font-bold">
                                    No auctions found matching criteria.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination footer links from mockup style -->
                <div v-if="auctions.last_page > 1" class="px-6 py-4 bg-surface-container-low flex justify-between items-center border-t border-outline-variant">
                    <p class="text-[10px] text-on-surface-variant font-bold uppercase tracking-widest">
                        Page {{ auctions.current_page }} of {{ auctions.last_page }}
                    </p>
                    <div class="flex gap-1">
                        <Link 
                            v-for="link in auctions.links"
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

        <!-- Create Auction Modal -->
        <div v-if="showCreateModal" class="fixed inset-0 z-50 bg-on-background/40 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-surface-container-lowest border border-outline-variant w-full max-w-lg p-6 flex flex-col gap-4 max-h-[90vh] overflow-y-auto rounded-xl shadow-xl">
                <div class="flex items-center justify-between border-b border-outline-variant pb-4">
                    <h2 class="text-sm font-black uppercase tracking-widest text-primary">Create Draft Auction</h2>
                    <button @click="showCreateModal = false" class="text-on-surface-variant hover:text-primary">
                        <XSquare class="w-5 h-5" />
                    </button>
                </div>

                <form @submit.prevent="submitCreate" class="space-y-4 text-xs font-bold">
                    <div class="flex flex-col gap-2">
                        <label class="text-on-surface-variant uppercase tracking-wider text-[10px]">Category Name</label>
                        <input 
                            v-model="form.category" 
                            type="text" 
                            required
                            class="bg-surface-container-low border border-outline-variant text-on-surface px-4 py-3 rounded-lg focus:outline-none focus:border-secondary w-full"
                            placeholder="e.g. Electronics, Fashion"
                        />
                    </div>

                    <div class="flex flex-col gap-2">
                        <label class="text-on-surface-variant uppercase tracking-wider text-[10px]">Item Title</label>
                        <input 
                            v-model="form.name" 
                            type="text" 
                            required
                            class="bg-surface-container-low border border-outline-variant text-on-surface px-4 py-3 rounded-lg focus:outline-none focus:border-secondary w-full"
                            placeholder="e.g. Gucci Leather Bag"
                        />
                    </div>

                    <div class="flex flex-col gap-2">
                        <label class="text-on-surface-variant uppercase tracking-wider text-[10px]">Description</label>
                        <textarea 
                            v-model="form.description" 
                            required
                            rows="3"
                            class="bg-surface-container-low border border-outline-variant text-on-surface px-4 py-3 rounded-lg focus:outline-none focus:border-secondary w-full resize-none font-medium"
                            placeholder="Write comprehensive item description..."
                        ></textarea>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="flex flex-col gap-2">
                            <label class="text-on-surface-variant uppercase tracking-wider text-[10px]">Price (₦)</label>
                            <input 
                                v-model="form.price" 
                                type="number" 
                                required
                                min="0"
                                step="0.01"
                                class="bg-surface-container-low border border-outline-variant text-on-surface px-4 py-3 rounded-lg focus:outline-none focus:border-secondary w-full"
                                placeholder="e.g. 50000"
                            />
                        </div>
                        <div class="flex flex-col gap-2">
                            <label class="text-on-surface-variant uppercase tracking-wider text-[10px]">Threshold Points</label>
                            <input 
                                v-model="form.opening_points" 
                                type="number" 
                                required
                                min="10"
                                class="bg-surface-container-low border border-outline-variant text-on-surface px-4 py-3 rounded-lg focus:outline-none focus:border-secondary w-full"
                            />
                        </div>
                        <div class="flex flex-col gap-2">
                            <label class="text-on-surface-variant uppercase tracking-wider text-[10px]">Countdown (Seconds)</label>
                            <input 
                                v-model="form.countdown_duration_seconds" 
                                type="number" 
                                required
                                min="10"
                                class="bg-surface-container-low border border-outline-variant text-on-surface px-4 py-3 rounded-lg focus:outline-none focus:border-secondary w-full"
                            />
                        </div>
                    </div>

                    <div class="flex flex-col gap-2">
                        <label class="text-on-surface-variant uppercase tracking-wider text-[10px]">Upload Item Image</label>
                        <input 
                            type="file" 
                            required
                            accept="image/*"
                            @change="handleImageChange"
                            class="bg-surface-container-low border border-outline-variant text-on-surface-variant px-4 py-3 rounded-lg focus:outline-none w-full"
                        />
                    </div>

                    <div class="flex gap-3 justify-end pt-4 border-t border-outline-variant">
                        <button 
                            type="button" 
                            @click="showCreateModal = false"
                            class="px-5 py-3 border border-outline-variant rounded-lg text-on-surface-variant hover:text-primary uppercase transition-colors"
                        >
                            Cancel
                        </button>
                        <button 
                            type="submit"
                            :disabled="form.processing"
                            class="px-5 py-3 bg-secondary hover:bg-secondary/90 text-on-secondary-fixed font-black uppercase rounded-lg disabled:opacity-50 transition-colors"
                        >
                            Save Draft
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Edit Auction Modal -->
        <div v-if="showEditModal" class="fixed inset-0 z-50 bg-on-background/40 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-surface-container-lowest border border-outline-variant w-full max-w-lg p-6 flex flex-col gap-4 max-h-[90vh] overflow-y-auto rounded-xl shadow-xl">
                <div class="flex items-center justify-between border-b border-outline-variant pb-4">
                    <h2 class="text-sm font-black uppercase tracking-widest text-primary">Edit Draft Auction</h2>
                    <button @click="showEditModal = false" class="text-on-surface-variant hover:text-primary">
                        <XSquare class="w-5 h-5" />
                    </button>
                </div>

                <form @submit.prevent="submitEdit" class="space-y-4 text-xs font-bold">
                    <div class="flex flex-col gap-2">
                        <label class="text-on-surface-variant uppercase tracking-wider text-[10px]">Category Name</label>
                        <input 
                            v-model="form.category" 
                            type="text" 
                            required
                            class="bg-surface-container-low border border-outline-variant text-on-surface px-4 py-3 rounded-lg focus:outline-none focus:border-secondary w-full"
                        />
                    </div>

                    <div class="flex flex-col gap-2">
                        <label class="text-on-surface-variant uppercase tracking-wider text-[10px]">Item Title</label>
                        <input 
                            v-model="form.name" 
                            type="text" 
                            required
                            class="bg-surface-container-low border border-outline-variant text-on-surface px-4 py-3 rounded-lg focus:outline-none focus:border-secondary w-full"
                        />
                    </div>

                    <div class="flex flex-col gap-2">
                        <label class="text-on-surface-variant uppercase tracking-wider text-[10px]">Description</label>
                        <textarea 
                            v-model="form.description" 
                            required
                            rows="3"
                            class="bg-surface-container-low border border-outline-variant text-on-surface px-4 py-3 rounded-lg focus:outline-none focus:border-secondary w-full resize-none font-medium"
                        ></textarea>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="flex flex-col gap-2">
                            <label class="text-on-surface-variant uppercase tracking-wider text-[10px]">Price (₦)</label>
                            <input 
                                v-model="form.price" 
                                type="number" 
                                required
                                min="0"
                                step="0.01"
                                class="bg-surface-container-low border border-outline-variant text-on-surface px-4 py-3 rounded-lg focus:outline-none focus:border-secondary w-full"
                                placeholder="e.g. 50000"
                            />
                        </div>
                        <div class="flex flex-col gap-2">
                            <label class="text-on-surface-variant uppercase tracking-wider text-[10px]">Threshold Points</label>
                            <input 
                                v-model="form.opening_points" 
                                type="number" 
                                required
                                min="10"
                                class="bg-surface-container-low border border-outline-variant text-on-surface px-4 py-3 rounded-lg focus:outline-none focus:border-secondary w-full"
                            />
                        </div>
                        <div class="flex flex-col gap-2">
                            <label class="text-on-surface-variant uppercase tracking-wider text-[10px]">Countdown (Seconds)</label>
                            <input 
                                v-model="form.countdown_duration_seconds" 
                                type="number" 
                                required
                                min="10"
                                class="bg-surface-container-low border border-outline-variant text-on-surface px-4 py-3 rounded-lg focus:outline-none focus:border-secondary w-full"
                            />
                        </div>
                    </div>

                    <div class="flex flex-col gap-2">
                        <label class="text-on-surface-variant uppercase tracking-wider text-[10px]">Update Image (Optional)</label>
                        <input 
                            type="file" 
                            accept="image/*"
                            @change="handleImageChange"
                            class="bg-surface-container-low border border-outline-variant text-on-surface-variant px-4 py-3 rounded-lg focus:outline-none w-full"
                        />
                        <p class="text-[9px] text-on-surface-variant/70 italic mt-0.5">Leave blank if you wish to keep the existing image.</p>
                    </div>

                    <div class="flex gap-3 justify-end pt-4 border-t border-outline-variant">
                        <button 
                            type="button" 
                            @click="showEditModal = false"
                            class="px-5 py-3 border border-outline-variant rounded-lg text-on-surface-variant hover:text-primary uppercase transition-colors"
                        >
                            Cancel
                        </button>
                        <button 
                            type="submit"
                            :disabled="form.processing"
                            class="px-5 py-3 bg-secondary hover:bg-secondary/90 text-on-secondary-fixed font-black uppercase rounded-lg disabled:opacity-50 transition-colors"
                        >
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>
