<script setup lang="ts">
import { Head, useForm, router, Link } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { ref } from 'vue';
import { 
    Users, 
    Search,
    Shield,
    CheckCircle,
    XCircle,
    UserMinus,
    Lock, 
    Unlock, 
    UserCheck
} from 'lucide-vue-next';
import { index as usersIndex, toggleActive as usersToggleActive, role as usersRole } from '@/routes/admin/users';

type User = {
    id: number;
    name: string;
    email: string;
    phone: string;
    points_balance: number;
    bonus_points: number;
    is_active: boolean;
    created_at: string;
    roles: { id: number; name: string }[];
};

const props = defineProps<{
    users: {
        data: User[];
        links: any[];
        current_page: number;
        last_page: number;
    };
    filters: {
        search: string | null;
        role: string | null;
    };
    availableRoles: string[];
}>();

const selectedUser = ref<User | null>(null);
const showRoleModal = ref(false);

const searchForm = useForm({
    search: props.filters.search || '',
    role: props.filters.role || '',
});

const roleForm = useForm({
    role: '',
});

const handleSearch = () => {
    searchForm.get(usersIndex.url(), {
        preserveState: true,
    });
};

const clearSearch = () => {
    searchForm.search = '';
    searchForm.role = '';
    handleSearch();
};

const toggleUserActive = (user: User) => {
    const action = user.is_active ? 'disable' : 'enable';
    if (confirm(`Are you sure you want to ${action} this user's account?`)) {
        router.post(usersToggleActive.url(user.id));
    }
};

const openRoleModal = (user: User) => {
    selectedUser.value = user;
    roleForm.role = user.roles.length > 0 ? user.roles[0].name : '';
    showRoleModal.value = true;
};

const submitRoleUpdate = () => {
    if (!selectedUser.value) return;
    
    roleForm.post(usersRole.url(selectedUser.value.id), {
        onSuccess: () => {
            showRoleModal.value = false;
        }
    });
};
</script>

<template>
    <Head title="Admin - Users" />

    <AdminLayout :breadcrumbs="[{ title: 'Users' }]">
        <div class="flex flex-col gap-6 font-sans text-xs">
            <!-- Header bar -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-black tracking-tight text-primary uppercase flex items-center gap-2">
                        <Users class="w-6 h-6 text-primary" />
                        <span>User Management</span>
                    </h1>
                    <p class="text-xs text-on-surface-variant mt-1">Manage user account permissions, deactivation states, and Spatie roles.</p>
                </div>
            </div>

            <!-- Search and Filter Panel -->
            <div class="bg-surface-container-lowest border border-outline-variant p-4 rounded-xl shadow-sm">
                <form @submit.prevent="handleSearch" class="flex flex-col md:flex-row gap-3">
                    <div class="flex-1 relative">
                        <input 
                            v-model="searchForm.search"
                            type="text"
                            placeholder="SEARCH BY NAME, EMAIL, OR PHONE..."
                            class="w-full bg-surface-container-low border border-outline-variant text-on-surface pl-10 pr-4 py-3 rounded-lg focus:outline-none focus:ring-1 focus:ring-secondary text-xs font-bold"
                        />
                        <Search class="w-4 h-4 text-on-surface-variant absolute left-3.5 top-3.5" />
                    </div>

                    <div class="w-full md:w-48">
                        <select 
                            v-model="searchForm.role"
                            class="w-full bg-surface-container-low border border-outline-variant text-on-surface px-4 py-3 rounded-lg focus:outline-none focus:ring-1 focus:ring-secondary text-xs font-bold uppercase"
                        >
                            <option value="">ALL ROLES</option>
                            <option v-for="role in availableRoles" :key="role" :value="role">{{ role }}</option>
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

            <!-- Users List Table -->
            <div class="bg-surface-container-lowest rounded-xl border border-outline-variant overflow-hidden shadow-sm flex flex-col">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse min-w-[900px]">
                        <thead class="bg-surface-container-low border-b border-outline-variant">
                            <tr class="text-[10px] font-bold text-on-surface-variant uppercase tracking-widest">
                                <th class="px-6 py-4">User info</th>
                                <th class="px-6 py-4">Phone</th>
                                <th class="px-6 py-4">Role</th>
                                <th class="px-6 py-4 text-right">Points Bal.</th>
                                <th class="px-6 py-4 text-right">Bonus Bal.</th>
                                <th class="px-6 py-4">Status</th>
                                <th class="px-6 py-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant">
                            <tr v-for="user in users.data" :key="user.id" class="hover:bg-surface-container-low/20 transition-colors group">
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <span class="font-bold text-primary">{{ user.name }}</span>
                                        <span class="text-[10px] text-on-surface-variant mt-0.5">{{ user.email }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-on-surface font-semibold">{{ user.phone }}</td>
                                <td class="px-6 py-4">
                                    <span v-if="user.roles.length > 0" class="text-[10px] font-black uppercase text-on-secondary-container tracking-widest bg-secondary-container border border-secondary/25 px-2.5 py-0.5 rounded-full">
                                        {{ user.roles[0].name }}
                                    </span>
                                    <span v-else class="text-[10px] font-bold uppercase text-on-surface-variant tracking-widest">
                                        User
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right font-bold text-primary">{{ user.points_balance }} PTS</td>
                                <td class="px-6 py-4 text-right font-bold text-on-surface-variant">{{ user.bonus_points }} PTS</td>
                                <td class="px-6 py-4">
                                    <span 
                                        :class="[
                                            'px-2.5 py-1 text-[9px] font-black uppercase tracking-widest border rounded-full',
                                            user.is_active 
                                                ? 'bg-secondary-container text-on-secondary-container border-secondary/20' 
                                                : 'bg-error-container/30 border-error/20 text-error animate-pulse'
                                        ]"
                                    >
                                        {{ user.is_active ? 'Active' : 'Disabled' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <button 
                                            @click="openRoleModal(user)"
                                            class="px-3 py-1.5 border border-outline-variant bg-surface-container-lowest hover:bg-surface-container-low rounded-lg text-on-surface-variant hover:text-primary transition-colors font-bold text-[10px] flex items-center gap-1.5"
                                        >
                                            <UserCheck class="w-3.5 h-3.5" />
                                            <span>Role</span>
                                        </button>
                                        
                                        <button 
                                            @click="toggleUserActive(user)"
                                            :class="[
                                                'px-3 py-1.5 border text-[10px] font-bold uppercase rounded-lg transition-colors flex items-center gap-1.5',
                                                user.is_active 
                                                    ? 'border-error/20 text-error hover:bg-error-container/20' 
                                                    : 'border-secondary/25 text-on-secondary-container hover:bg-secondary-container/45'
                                            ]"
                                        >
                                            <component :is="user.is_active ? Lock : Unlock" class="w-3.5 h-3.5" />
                                            <span>{{ user.is_active ? 'Disable' : 'Enable' }}</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="users.data.length === 0">
                                <td colspan="7" class="px-6 py-12 text-center text-on-surface-variant uppercase tracking-widest font-bold">
                                    No platform users found.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination footer links -->
                <div v-if="users.last_page > 1" class="px-6 py-4 bg-surface-container-low flex justify-between items-center border-t border-outline-variant">
                    <p class="text-[10px] text-on-surface-variant font-bold uppercase tracking-widest">
                        Page {{ users.current_page }} of {{ users.last_page }}
                    </p>
                    <div class="flex gap-1">
                        <Link 
                            v-for="link in users.links"
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

        <!-- Role Update Modal -->
        <div v-if="showRoleModal" class="fixed inset-0 z-50 bg-on-background/40 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-surface-container-lowest border border-outline-variant w-full max-w-sm p-6 flex flex-col gap-4 rounded-xl shadow-xl">
                <div class="flex items-center justify-between border-b border-outline-variant pb-4">
                    <h2 class="text-sm font-black uppercase tracking-widest text-primary">Manage User Role</h2>
                    <button @click="showRoleModal = false" class="text-on-surface-variant hover:text-primary">
                        <Lock class="w-4 h-4" />
                    </button>
                </div>

                <div class="text-[10px] text-on-surface-variant leading-relaxed">
                    <p>Updating role for <span class="text-primary font-bold">{{ selectedUser?.name }}</span>.</p>
                </div>

                <form @submit.prevent="submitRoleUpdate" class="space-y-4">
                    <div class="flex flex-col gap-2">
                        <label class="text-on-surface-variant uppercase tracking-wider text-[10px] font-bold">Select Role</label>
                        <select 
                            v-model="roleForm.role"
                            required
                            class="bg-surface-container-low border border-outline-variant text-on-surface px-4 py-3 rounded-lg focus:outline-none focus:border-secondary w-full uppercase font-bold text-xs"
                        >
                            <option value="">-- SELECT ROLE --</option>
                            <option v-for="role in availableRoles" :key="role" :value="role">{{ role }}</option>
                        </select>
                    </div>

                    <div class="flex gap-3 justify-end pt-4 border-t border-outline-variant">
                        <button 
                            type="button" 
                            @click="showRoleModal = false"
                            class="px-4 py-2.5 border border-outline-variant rounded-lg text-on-surface-variant hover:text-primary font-bold uppercase transition-colors"
                        >
                            Cancel
                        </button>
                        <button 
                            type="submit"
                            :disabled="roleForm.processing"
                            class="px-4 py-2.5 bg-secondary text-on-secondary-fixed font-black uppercase rounded-lg disabled:opacity-50 transition-colors"
                        >
                            Update
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>
