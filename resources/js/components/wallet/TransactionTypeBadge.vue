<script setup lang="ts">
const props = defineProps<{
    type: string;
    label: string;
}>();

const badgeClass = {
    deposit: 'bg-forest/15 text-forest border-forest/30',
    bid_debit: 'bg-ink/10 text-ink border-ink/20',
    bonus_award: 'bg-amber/20 text-ink border-amber/40',
    bonus_claim: 'bg-secondary-container text-on-secondary-container border-secondary/30',
}[props.type] ?? 'bg-surface-container text-on-surface-variant border-outline-variant';
</script>

<template>
    <span
        class="inline-flex rounded-md border px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide"
        :class="badgeClass"
    >
        {{ label }}
    </span>
</template>
