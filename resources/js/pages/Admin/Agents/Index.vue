<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { ref } from 'vue';
import { 
    UserCheck, 
    Check, 
    X,
    UserMinus,
    Clock,
    ShieldAlert
} from 'lucide-vue-next';

type Agent = {
    id: number;
    name: string;
    email: string;
    phone: string;
    department: string;
    employee_id: string;
    agent_approved_at: string | null;
    approved_by_user?: {
        name: string;
    } | null;
};

const props = defineProps<{
    pending: Agent[];
    approved: Agent[];
}>();

const activeTab = ref<'pending' | 'approved'>('pending');

const approveAgent = (agent: Agent) => {
    if (confirm(`Are you sure you want to APPROVE ${agent.name} as a support agent?`)) {
        router.post(`/admin/agents/${agent.id}/approve`, {}, {
            preserveScroll: true,
        });
    }
};

const rejectAgent = (agent: Agent) => {
    if (confirm(`Are you sure you want to DECLINE/REJECT ${agent.name}'s registration? This will delete their account.`)) {
        router.post(`/admin/agents/${agent.id}/reject`, {}, {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <Head title="Admin - Agent Portal Approvals" />

    <AdminLayout :breadcrumbs="[{ title: 'Agent Approvals' }]">
        <div class="flex flex-col gap-6 font-sans text-xs">
            <!-- Header bar -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-black tracking-tight text-primary uppercase flex items-center gap-2">
                        <UserCheck class="w-6 h-6 text-primary" />
                        <span>Agent Management</span>
                    </h1>
                    <p class="text-xs text-on-surface-variant mt-1">Review agent registrations, approve portal access, or reject registrations.</p>
                </div>
            </div>

            <!-- Tabs Panel -->
            <div class="flex border-b border-outline-variant">
                <button 
                    @click="activeTab = 'pending'"
                    :class="[
                        'px-5 py-3 font-bold uppercase tracking-wider transition-all border-b-2',
                        activeTab === 'pending' 
                            ? 'border-primary text-primary' 
                            : 'border-transparent text-on-surface-variant hover:text-on-surface'
                    ]"
                >
                    Pending Approvals ({{ pending.length }})
                </button>
                <button 
                    @click="activeTab = 'approved'"
                    :class="[
                        'px-5 py-3 font-bold uppercase tracking-wider transition-all border-b-2',
                        activeTab === 'approved' 
                            ? 'border-primary text-primary' 
                            : 'border-transparent text-on-surface-variant hover:text-on-surface'
                    ]"
                >
                    Approved Agents ({{ approved.length }})
                </button>
            </div>

            <!-- Pending Registrations Tab -->
            <div v-if="activeTab === 'pending'" class="bg-surface-container-lowest rounded-xl border border-outline-variant overflow-hidden shadow-sm flex flex-col">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse min-w-[900px]">
                        <thead class="bg-surface-container-low border-b border-outline-variant">
                            <tr class="text-[10px] font-bold text-on-surface-variant uppercase tracking-widest">
                                <th class="px-6 py-4">Agent Name / Email</th>
                                <th class="px-6 py-4">Phone</th>
                                <th class="px-6 py-4">Department</th>
                                <th class="px-6 py-4">Employee ID</th>
                                <th class="px-6 py-4">Status</th>
                                <th class="px-6 py-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant">
                            <tr v-for="agent in pending" :key="agent.id" class="hover:bg-surface-container-low/20 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <span class="font-bold text-primary">{{ agent.name }}</span>
                                        <span class="text-[10px] text-on-surface-variant mt-0.5">{{ agent.email }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-on-surface font-semibold">{{ agent.phone }}</td>
                                <td class="px-6 py-4">
                                    <span class="text-[10px] font-bold uppercase text-on-secondary-container tracking-widest bg-secondary-container/40 border border-secondary/20 px-2 py-0.5 rounded">
                                        {{ agent.department }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 font-mono font-bold text-on-surface">{{ agent.employee_id }}</td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center gap-1 bg-amber/15 border border-amber/30 text-primary text-[9px] font-black uppercase tracking-widest px-2.5 py-1 rounded-full animate-pulse">
                                        <Clock class="w-3 h-3 text-primary" />
                                        <span>Pending Review</span>
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <button 
                                            @click="approveAgent(agent)"
                                            class="px-3 py-1.5 border border-secondary/25 bg-secondary-container/40 hover:bg-secondary-container/85 text-on-secondary-container hover:text-primary rounded-lg transition-colors font-bold text-[10px] flex items-center gap-1.5 cursor-pointer"
                                        >
                                            <Check class="w-3.5 h-3.5" />
                                            <span>Approve</span>
                                        </button>
                                        
                                        <button 
                                            @click="rejectAgent(agent)"
                                            class="px-3 py-1.5 border border-error/20 bg-error-container/15 hover:bg-error-container/30 text-error rounded-lg transition-colors font-bold text-[10px] flex items-center gap-1.5 cursor-pointer"
                                        >
                                            <UserMinus class="w-3.5 h-3.5" />
                                            <span>Decline</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="pending.length === 0">
                                <td colspan="6" class="px-6 py-12 text-center text-on-surface-variant uppercase tracking-widest font-bold">
                                    No pending agent registrations.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Approved Agents Tab -->
            <div v-if="activeTab === 'approved'" class="bg-surface-container-lowest rounded-xl border border-outline-variant overflow-hidden shadow-sm flex flex-col">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse min-w-[900px]">
                        <thead class="bg-surface-container-low border-b border-outline-variant">
                            <tr class="text-[10px] font-bold text-on-surface-variant uppercase tracking-widest">
                                <th class="px-6 py-4">Agent Name / Email</th>
                                <th class="px-6 py-4">Phone</th>
                                <th class="px-6 py-4">Department</th>
                                <th class="px-6 py-4">Employee ID</th>
                                <th class="px-6 py-4">Approved At</th>
                                <th class="px-6 py-4">Approved By</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant">
                            <tr v-for="agent in approved" :key="agent.id" class="hover:bg-surface-container-low/20 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <span class="font-bold text-primary">{{ agent.name }}</span>
                                        <span class="text-[10px] text-on-surface-variant mt-0.5">{{ agent.email }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-on-surface font-semibold">{{ agent.phone }}</td>
                                <td class="px-6 py-4">
                                    <span class="text-[10px] font-bold uppercase text-on-secondary-container tracking-widest bg-secondary-container/40 border border-secondary/20 px-2 py-0.5 rounded">
                                        {{ agent.department }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 font-mono font-bold text-on-surface">{{ agent.employee_id }}</td>
                                <td class="px-6 py-4 text-on-surface-variant font-medium">
                                    {{ agent.agent_approved_at ? new Date(agent.agent_approved_at).toLocaleString() : 'N/A' }}
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-[10px] font-black uppercase text-secondary tracking-widest bg-secondary/10 px-2 py-0.5 rounded">
                                        {{ agent.approved_by_user?.name || 'System / Admin' }}
                                    </span>
                                </td>
                            </tr>
                            <tr v-if="approved.length === 0">
                                <td colspan="6" class="px-6 py-12 text-center text-on-surface-variant uppercase tracking-widest font-bold">
                                    No approved agents found.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
