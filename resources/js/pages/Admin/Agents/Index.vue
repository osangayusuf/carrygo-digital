<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { UserCheck, Check, UserMinus, Clock } from 'lucide-vue-next';
import { ref } from 'vue';
import {
    approve,
    reject as rejectRoute,
} from '@/actions/App/Http/Controllers/Admin/AgentController';
import AdminLayout from '@/layouts/AdminLayout.vue';

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
    if (
        confirm(
            `Are you sure you want to APPROVE ${agent.name} as a support agent?`,
        )
    ) {
        router.post(
            approve.url(agent.id),
            {},
            {
                preserveScroll: true,
            },
        );
    }
};

const rejectAgent = (agent: Agent) => {
    if (
        confirm(
            `Are you sure you want to DECLINE/REJECT ${agent.name}'s registration? This will delete their account.`,
        )
    ) {
        router.post(
            rejectRoute.url(agent.id),
            {},
            {
                preserveScroll: true,
            },
        );
    }
};
</script>

<template>
    <Head title="Admin - Agent Portal Approvals" />

    <AdminLayout :breadcrumbs="[{ title: 'Agent Approvals' }]">
        <div class="flex flex-col gap-6 font-sans text-xs">
            <!-- Header bar -->
            <div
                class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center"
            >
                <div>
                    <h1
                        class="flex items-center gap-2 text-2xl font-black tracking-tight text-primary uppercase"
                    >
                        <UserCheck class="h-6 w-6 text-primary" />
                        <span>Agent Management</span>
                    </h1>
                    <p class="mt-1 text-xs text-on-surface-variant">
                        Review agent registrations, approve portal access, or
                        reject registrations.
                    </p>
                </div>
            </div>

            <!-- Tabs Panel -->
            <div class="flex border-b border-outline-variant">
                <button
                    @click="activeTab = 'pending'"
                    :class="[
                        'border-b-2 px-5 py-3 font-bold tracking-wider uppercase transition-all',
                        activeTab === 'pending'
                            ? 'border-primary text-primary'
                            : 'border-transparent text-on-surface-variant hover:text-on-surface',
                    ]"
                >
                    Pending Approvals ({{ pending.length }})
                </button>
                <button
                    @click="activeTab = 'approved'"
                    :class="[
                        'border-b-2 px-5 py-3 font-bold tracking-wider uppercase transition-all',
                        activeTab === 'approved'
                            ? 'border-primary text-primary'
                            : 'border-transparent text-on-surface-variant hover:text-on-surface',
                    ]"
                >
                    Approved Agents ({{ approved.length }})
                </button>
            </div>

            <!-- Pending Registrations Tab -->
            <div
                v-if="activeTab === 'pending'"
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
                                <th class="px-6 py-4">Agent Name / Email</th>
                                <th class="px-6 py-4">Phone</th>
                                <th class="px-6 py-4">Department</th>
                                <th class="px-6 py-4">Employee ID</th>
                                <th class="px-6 py-4">Status</th>
                                <th class="px-6 py-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant">
                            <tr
                                v-for="agent in pending"
                                :key="agent.id"
                                class="transition-colors hover:bg-surface-container-low/20"
                            >
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <span class="font-bold text-primary">{{
                                            agent.name
                                        }}</span>
                                        <span
                                            class="mt-0.5 text-[10px] text-on-surface-variant"
                                            >{{ agent.email }}</span
                                        >
                                    </div>
                                </td>
                                <td
                                    class="px-6 py-4 font-semibold text-on-surface"
                                >
                                    {{ agent.phone }}
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        class="rounded border border-secondary/20 bg-secondary-container/40 px-2 py-0.5 text-[10px] font-bold tracking-widest text-on-secondary-container uppercase"
                                    >
                                        {{ agent.department }}
                                    </span>
                                </td>
                                <td
                                    class="px-6 py-4 font-mono font-bold text-on-surface"
                                >
                                    {{ agent.employee_id }}
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        class="inline-flex animate-pulse items-center gap-1 rounded-full border border-amber/30 bg-amber/15 px-2.5 py-1 text-[9px] font-black tracking-widest text-primary uppercase"
                                    >
                                        <Clock class="h-3 w-3 text-primary" />
                                        <span>Pending Review</span>
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <button
                                            @click="approveAgent(agent)"
                                            class="flex cursor-pointer items-center gap-1.5 rounded-lg border border-secondary/25 bg-secondary-container/40 px-3 py-1.5 text-[10px] font-bold text-on-secondary-container transition-colors hover:bg-secondary-container/85 hover:text-primary"
                                        >
                                            <Check class="h-3.5 w-3.5" />
                                            <span>Approve</span>
                                        </button>

                                        <button
                                            @click="rejectAgent(agent)"
                                            class="flex cursor-pointer items-center gap-1.5 rounded-lg border border-error/20 bg-error-container/15 px-3 py-1.5 text-[10px] font-bold text-error transition-colors hover:bg-error-container/30"
                                        >
                                            <UserMinus class="h-3.5 w-3.5" />
                                            <span>Decline</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="pending.length === 0">
                                <td
                                    colspan="6"
                                    class="px-6 py-12 text-center font-bold tracking-widest text-on-surface-variant uppercase"
                                >
                                    No pending agent registrations.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Approved Agents Tab -->
            <div
                v-if="activeTab === 'approved'"
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
                                <th class="px-6 py-4">Agent Name / Email</th>
                                <th class="px-6 py-4">Phone</th>
                                <th class="px-6 py-4">Department</th>
                                <th class="px-6 py-4">Employee ID</th>
                                <th class="px-6 py-4">Approved At</th>
                                <th class="px-6 py-4">Approved By</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant">
                            <tr
                                v-for="agent in approved"
                                :key="agent.id"
                                class="transition-colors hover:bg-surface-container-low/20"
                            >
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <span class="font-bold text-primary">{{
                                            agent.name
                                        }}</span>
                                        <span
                                            class="mt-0.5 text-[10px] text-on-surface-variant"
                                            >{{ agent.email }}</span
                                        >
                                    </div>
                                </td>
                                <td
                                    class="px-6 py-4 font-semibold text-on-surface"
                                >
                                    {{ agent.phone }}
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        class="rounded border border-secondary/20 bg-secondary-container/40 px-2 py-0.5 text-[10px] font-bold tracking-widest text-on-secondary-container uppercase"
                                    >
                                        {{ agent.department }}
                                    </span>
                                </td>
                                <td
                                    class="px-6 py-4 font-mono font-bold text-on-surface"
                                >
                                    {{ agent.employee_id }}
                                </td>
                                <td
                                    class="px-6 py-4 font-medium text-on-surface-variant"
                                >
                                    {{
                                        agent.agent_approved_at
                                            ? new Date(
                                                  agent.agent_approved_at,
                                              ).toLocaleString()
                                            : 'N/A'
                                    }}
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        class="rounded bg-secondary/10 px-2 py-0.5 text-[10px] font-black tracking-widest text-secondary uppercase"
                                    >
                                        {{
                                            agent.approved_by_user?.name ||
                                            'System / Admin'
                                        }}
                                    </span>
                                </td>
                            </tr>
                            <tr v-if="approved.length === 0">
                                <td
                                    colspan="6"
                                    class="px-6 py-12 text-center font-bold tracking-widest text-on-surface-variant uppercase"
                                >
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
