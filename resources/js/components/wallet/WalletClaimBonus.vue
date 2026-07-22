<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import WalletController from '@/actions/App/Http/Controllers/WalletController';

defineProps<{
    bonusPoints: number;
}>();
</script>

<template>
    <div
        class="rounded-xl border border-outline-variant bg-surface-container-low p-5"
    >
        <h3 class="text-lg font-semibold text-on-surface">
            Claim bonus points
        </h3>
        <p class="mt-1 text-sm text-on-surface-variant">
            Convert all {{ bonusPoints.toLocaleString() }} bonus points to your
            spendable balance.
        </p>
        <Form
            v-bind="WalletController.claimBonus.form()"
            class="mt-4"
            v-slot="{ processing, errors }"
        >
            <p v-if="errors.bonus" class="mb-2 text-xs font-medium text-error">
                {{ errors.bonus }}
            </p>
            <button
                type="submit"
                :disabled="processing || bonusPoints <= 0"
                data-test="claim-bonus-button"
                class="rounded-lg bg-primary px-8 py-3 text-xs font-bold text-on-primary shadow-md transition-all hover:bg-tertiary-container hover:shadow-lg disabled:opacity-60"
            >
                Claim all bonus points
            </button>
        </Form>
    </div>
</template>
