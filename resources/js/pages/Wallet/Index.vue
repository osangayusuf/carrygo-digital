<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import SettingsPanel from '@/components/settings/SettingsPanel.vue';
import SettingsShell from '@/components/settings/SettingsShell.vue';
import PublicLayout from '@/layouts/PublicLayout.vue';

defineOptions({ layout: PublicLayout });

const page = usePage();
const user = computed(() => page.props.auth.user);
</script>

<template>
    <Head title="Wallet" />

    <SettingsShell>
        <SettingsPanel
            title="Wallet"
            description="View your points balance and manage deposits."
        >
            <div class="max-w-2xl space-y-6">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="rounded-xl border border-outline-variant bg-surface-container-low p-5">
                        <p class="text-xs font-semibold uppercase tracking-wider text-on-surface-variant">
                            Points balance
                        </p>
                        <p class="mt-2 font-condensed text-3xl font-black text-on-surface">
                            {{ user?.points_balance ?? 0 }}
                        </p>
                    </div>
                    <div class="rounded-xl border border-outline-variant bg-surface-container-low p-5">
                        <p class="text-xs font-semibold uppercase tracking-wider text-on-surface-variant">
                            Bonus points
                        </p>
                        <p class="mt-2 font-condensed text-3xl font-black text-on-surface">
                            {{ user?.bonus_points ?? 0 }}
                        </p>
                    </div>
                </div>

                <p class="text-sm text-on-surface-variant">
                    Deposits and bonus claims are coming soon. Your balance updates automatically
                    when you bid or win auctions.
                </p>
            </div>
        </SettingsPanel>
    </SettingsShell>
</template>
