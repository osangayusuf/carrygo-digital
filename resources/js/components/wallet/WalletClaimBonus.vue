<script setup lang="ts">
import { useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import WalletController from '@/actions/App/Http/Controllers/WalletController';
import { useTermsModal } from '@/composables/useTermsModal';

defineProps<{
    bonusPoints: number;
}>();

const page = usePage();
const user = computed(() => (page.props.auth as { user?: { terms_accepted_at?: string | null } })?.user);
const { open: openTermsModal } = useTermsModal();

const form = useForm({});

function handleClaimSubmit(e: Event) {
    if (user.value && !user.value.terms_accepted_at) {
        e.preventDefault();
        openTermsModal(() => {
            form.post(WalletController.claimBonus.url());
        });
    }
}
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
        <form
            @submit="handleClaimSubmit"
            :action="WalletController.claimBonus.url()"
            method="post"
            class="mt-4"
        >
            <p v-if="form.errors.terms || (form.errors as Record<string, string>).bonus" class="mb-2 text-xs font-medium text-error">
                {{ form.errors.terms || (form.errors as Record<string, string>).bonus }}
            </p>
            <button
                type="submit"
                :disabled="form.processing || bonusPoints <= 0"
                data-test="claim-bonus-button"
                class="rounded-lg bg-primary px-8 py-3 text-xs font-bold text-on-primary shadow-md transition-all hover:bg-tertiary-container hover:shadow-lg disabled:opacity-60"
            >
                Claim all bonus points
            </button>
        </form>
    </div>
</template>
