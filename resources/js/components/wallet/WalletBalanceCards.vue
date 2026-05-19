<script setup lang="ts">
import type { WalletBalances, WalletConfig } from '@/types/wallet';

defineProps<{
    balances: WalletBalances;
    walletConfig: WalletConfig;
}>();
</script>

<template>
    <div class="grid gap-4 sm:grid-cols-2">
        <div class="rounded-xl border border-outline-variant bg-surface-container-low p-5">
            <p class="text-xs font-semibold uppercase tracking-wider text-on-surface-variant">
                Points balance
            </p>
            <p class="mt-2 font-condensed text-3xl font-black text-on-surface">
                {{ balances.points_balance.toLocaleString() }}
            </p>
            <p class="mt-2 text-xs text-on-surface-variant">
                ₦1 = {{ walletConfig.points_per_naira.toLocaleString() }} pts
            </p>
        </div>
        <div class="rounded-xl border border-outline-variant bg-surface-container-low p-5">
            <p class="text-xs font-semibold uppercase tracking-wider text-on-surface-variant">
                Bonus points
            </p>
            <p class="mt-2 font-condensed text-3xl font-black text-on-surface">
                {{ balances.bonus_points.toLocaleString() }}
            </p>
            <p class="mt-2 text-xs text-on-surface-variant">Claimable to spendable balance</p>
        </div>
    </div>
</template>
