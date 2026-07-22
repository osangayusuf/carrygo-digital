<script setup lang="ts">
import { Head, useForm, router, Link } from '@inertiajs/vue3';
import {
    Users,
    Search,
    Shield,
    CheckCircle,
    XCircle,
    UserMinus,
    Lock,
    Unlock,
    UserCheck,
} from 'lucide-vue-next';
import { ref } from 'vue';
import AdminLayout from '@/layouts/AdminLayout.vue';
import {
    index as usersIndex,
    toggleActive as usersToggleActive,
    role as usersRole,
} from '@/routes/admin/users';

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
    if (!selectedUser.value) {
        return;
    }

    roleForm.post(usersRole.url(selectedUser.value.id), {
        onSuccess: () => {
            showRoleModal.value = false;
        },
    });
};
</script>

<template>
    <Head title="Admin - Users" />

    <AdminLayout :breadcrumbs="[{ title: 'Users' }]">
        <div class="flex flex-col gap-6 font-sans text-xs">
            <!-- Header bar -->
            <div
                class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center"
            >
                <div>
                    <h1
                        class="flex items-center gap-2 text-2xl font-black tracking-tight text-primary uppercase"
                    >
                        <Users class="h-6 w-6 text-primary" />
                        <span>User Management</span>
                    </h1>
                    <p class="mt-1 text-xs text-on-surface-variant">
                        Manage user account permissions, deactivation states,
                        and Spatie roles.
                    </p>
                </div>
            </div>

            <!-- Search and Filter Panel -->
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
                            placeholder="SEARCH BY NAME, EMAIL, OR PHONE..."
                            class="w-full rounded-lg border border-outline-variant bg-surface-container-low py-3 pr-4 pl-10 text-xs font-bold text-on-surface focus:ring-1 focus:ring-secondary focus:outline-none"
                        />
                        <Search
                            class="absolute top-3.5 left-3.5 h-4 w-4 text-on-surface-variant"
                        />
                    </div>

                    <div class="w-full md:w-48">
                        <select
                            v-model="searchForm.role"
                            class="w-full rounded-lg border border-outline-variant bg-surface-container-low px-4 py-3 text-xs font-bold text-on-surface uppercase focus:ring-1 focus:ring-secondary focus:outline-none"
                        >
                            <option value="">ALL ROLES</option>
                            <option
                                v-for="role in availableRoles"
                                :key="role"
                                :value="role"
                            >
                                {{ role }}
                            </option>
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

            <!-- Users List Table -->
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
                                <th class="px-6 py-4">User info</th>
                                <th class="px-6 py-4">Phone</th>
                                <th class="px-6 py-4">Role</th>
                                <th class="px-6 py-4 text-right">
                                    Points Bal.
                                </th>
                                <th class="px-6 py-4 text-right">Bonus Bal.</th>
                                <th class="px-6 py-4">Status</th>
                                <th class="px-6 py-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant">
                            <tr
                                v-for="user in users.data"
                                :key="user.id"
                                class="group transition-colors hover:bg-surface-container-low/20"
                            >
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <span class="font-bold text-primary">{{
                                            user.name
                                        }}</span>
                                        <span
                                            class="mt-0.5 text-[10px] text-on-surface-variant"
                                            >{{ user.email }}</span
                                        >
                                    </div>
                                </td>
                                <td
                                    class="px-6 py-4 font-semibold text-on-surface"
                                >
                                    {{ user.phone }}
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        v-if="user.roles.length > 0"
                                        class="rounded-full border border-secondary/25 bg-secondary-container px-2.5 py-0.5 text-[10px] font-black tracking-widest text-on-secondary-container uppercase"
                                    >
                                        {{ user.roles[0].name }}
                                    </span>
                                    <span
                                        v-else
                                        class="text-[10px] font-bold tracking-widest text-on-surface-variant uppercase"
                                    >
                                        User
                                    </span>
                                </td>
                                <td
                                    class="px-6 py-4 text-right font-bold text-primary"
                                >
                                    {{ user.points_balance }} PTS
                                </td>
                                <td
                                    class="px-6 py-4 text-right font-bold text-on-surface-variant"
                                >
                                    {{ user.bonus_points }} PTS
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        :class="[
                                            'rounded-full border px-2.5 py-1 text-[9px] font-black tracking-widest uppercase',
                                            user.is_active
                                                ? 'border-secondary/20 bg-secondary-container text-on-secondary-container'
                                                : 'animate-pulse border-error/20 bg-error-container/30 text-error',
                                        ]"
                                    >
                                        {{
                                            user.is_active
                                                ? 'Active'
                                                : 'Disabled'
                                        }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <button
                                            @click="openRoleModal(user)"
                                            class="flex items-center gap-1.5 rounded-lg border border-outline-variant bg-surface-container-lowest px-3 py-1.5 text-[10px] font-bold text-on-surface-variant transition-colors hover:bg-surface-container-low hover:text-primary"
                                        >
                                            <UserCheck class="h-3.5 w-3.5" />
                                            <span>Role</span>
                                        </button>

                                        <button
                                            @click="toggleUserActive(user)"
                                            :class="[
                                                'flex items-center gap-1.5 rounded-lg border px-3 py-1.5 text-[10px] font-bold uppercase transition-colors',
                                                user.is_active
                                                    ? 'border-error/20 text-error hover:bg-error-container/20'
                                                    : 'border-secondary/25 text-on-secondary-container hover:bg-secondary-container/45',
                                            ]"
                                        >
                                            <component
                                                :is="
                                                    user.is_active
                                                        ? Lock
                                                        : Unlock
                                                "
                                                class="h-3.5 w-3.5"
                                            />
                                            <span>{{
                                                user.is_active
                                                    ? 'Disable'
                                                    : 'Enable'
                                            }}</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="users.data.length === 0">
                                <td
                                    colspan="7"
                                    class="px-6 py-12 text-center font-bold tracking-widest text-on-surface-variant uppercase"
                                >
                                    No platform users found.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination footer links -->
                <div
                    v-if="users.last_page > 1"
                    class="flex items-center justify-between border-t border-outline-variant bg-surface-container-low px-6 py-4"
                >
                    <p
                        class="text-[10px] font-bold tracking-widest text-on-surface-variant uppercase"
                    >
                        Page {{ users.current_page }} of {{ users.last_page }}
                    </p>
                    <div class="flex gap-1">
                        <Link
                            v-for="link in users.links"
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

        <!-- Role Update Modal -->
        <div
            v-if="showRoleModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-on-background/40 p-4 backdrop-blur-sm"
        >
            <div
                class="flex w-full max-w-sm flex-col gap-4 rounded-xl border border-outline-variant bg-surface-container-lowest p-6 shadow-xl"
            >
                <div
                    class="flex items-center justify-between border-b border-outline-variant pb-4"
                >
                    <h2
                        class="text-sm font-black tracking-widest text-primary uppercase"
                    >
                        Manage User Role
                    </h2>
                    <button
                        @click="showRoleModal = false"
                        class="text-on-surface-variant hover:text-primary"
                    >
                        <Lock class="h-4 w-4" />
                    </button>
                </div>

                <div
                    class="text-[10px] leading-relaxed text-on-surface-variant"
                >
                    <p>
                        Updating role for
                        <span class="font-bold text-primary">{{
                            selectedUser?.name
                        }}</span
                        >.
                    </p>
                </div>

                <form @submit.prevent="submitRoleUpdate" class="space-y-4">
                    <div class="flex flex-col gap-2">
                        <label
                            class="text-[10px] font-bold tracking-wider text-on-surface-variant uppercase"
                            >Select Role</label
                        >
                        <select
                            v-model="roleForm.role"
                            required
                            class="w-full rounded-lg border border-outline-variant bg-surface-container-low px-4 py-3 text-xs font-bold text-on-surface uppercase focus:border-secondary focus:outline-none"
                        >
                            <option value="">-- SELECT ROLE --</option>
                            <option
                                v-for="role in availableRoles"
                                :key="role"
                                :value="role"
                            >
                                {{ role }}
                            </option>
                        </select>
                    </div>

                    <div
                        class="flex justify-end gap-3 border-t border-outline-variant pt-4"
                    >
                        <button
                            type="button"
                            @click="showRoleModal = false"
                            class="rounded-lg border border-outline-variant px-4 py-2.5 font-bold text-on-surface-variant uppercase transition-colors hover:text-primary"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            :disabled="roleForm.processing"
                            class="rounded-lg bg-secondary px-4 py-2.5 font-black text-on-secondary-fixed uppercase transition-colors disabled:opacity-50"
                        >
                            Update
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>
