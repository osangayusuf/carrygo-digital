<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import TermsController from '@/actions/App/Http/Controllers/TermsController';
import PublicLayout from '@/layouts/PublicLayout.vue';

defineOptions({ layout: PublicLayout });

const props = defineProps<{
    documents: Array<{
        name: string;
        slug: string;
        filename: string;
        url: string;
        download_url: string;
    }>;
}>();

const page = usePage();
const user = computed(
    () =>
        (page.props.auth as { user?: { terms_accepted_at?: string | null } })
            ?.user,
);

const hasAccepted = computed(() => !!user.value?.terms_accepted_at);

const form = useForm({});

function acceptTerms() {
    form.post(TermsController.accept.url(), {
        preserveScroll: true,
    });
}
</script>

<template>
    <Head title="Terms & Conditions" />

    <div class="min-h-screen bg-background px-4 py-8 sm:px-6 lg:px-8 lg:py-12">
        <div class="mx-auto max-w-4xl">
            <!-- Header -->
            <div class="mb-8 border-b border-outline-variant/30 pb-6">
                <div class="mb-2 flex items-center gap-3 text-primary">
                    <span class="material-symbols-outlined text-3xl"
                        >gavel</span
                    >
                    <span class="text-xs font-bold tracking-wider uppercase"
                        >Bidora Digital Platform</span
                    >
                </div>
                <h1
                    class="font-headline text-3xl font-black tracking-tight text-on-surface sm:text-4xl"
                >
                    Terms &amp; Conditions
                </h1>
                <p class="mt-2 text-on-surface-variant">
                    All legal policies, bidding regulations, winner allocations,
                    and platform guidelines governing your use of Bidora.
                </p>
            </div>

            <!-- Acceptance Alert Banner for Logged-In Users -->
            <div v-if="user" class="mb-8">
                <div
                    v-if="hasAccepted"
                    class="flex items-center justify-between rounded-2xl border border-primary/30 bg-primary/10 p-5 text-on-surface"
                >
                    <div class="flex items-center gap-3">
                        <span
                            class="material-symbols-outlined text-2xl text-primary"
                            >verified</span
                        >
                        <div>
                            <p class="text-sm font-bold">Terms Accepted</p>
                            <p class="text-xs text-on-surface-variant">
                                You accepted the platform terms on
                                {{
                                    new Date(
                                        user.terms_accepted_at!,
                                    ).toLocaleDateString()
                                }}.
                            </p>
                        </div>
                    </div>
                </div>

                <div
                    v-else
                    class="flex flex-col items-start justify-between gap-4 rounded-2xl border-2 border-primary bg-primary/15 p-5 text-on-surface shadow-md sm:flex-row sm:items-center"
                >
                    <div class="flex items-center gap-3">
                        <span
                            class="material-symbols-outlined text-2xl text-primary"
                            >warning</span
                        >
                        <div>
                            <p class="text-sm font-bold">Acceptance Required</p>
                            <p class="text-xs text-on-surface-variant">
                                You must accept these terms before you can place
                                bids or claim prizes on Bidora.
                            </p>
                        </div>
                    </div>
                    <form
                        @submit.prevent="acceptTerms"
                        class="w-full shrink-0 sm:w-auto"
                    >
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="w-full rounded-xl bg-primary px-5 py-2.5 text-xs font-bold text-on-primary shadow-sm transition-all hover:bg-tertiary-container active:scale-95 disabled:opacity-50 sm:w-auto"
                        >
                            Accept Terms Now
                        </button>
                    </form>
                </div>
            </div>

            <!-- Documents Grid -->
            <div class="space-y-4">
                <h2 class="font-headline text-xl font-bold text-on-surface">
                    Official Policy Documents
                </h2>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div
                        v-for="doc in props.documents"
                        :key="doc.filename"
                        class="flex flex-col justify-between rounded-2xl border border-outline-variant/40 bg-surface p-5 transition-all hover:border-primary/50 hover:shadow-md"
                    >
                        <div>
                            <div class="mb-2 flex items-center gap-2">
                                <span
                                    class="material-symbols-outlined text-xl text-primary"
                                    >description</span
                                >
                                <h3
                                    class="line-clamp-2 text-sm font-bold text-on-surface"
                                >
                                    {{ doc.name }}
                                </h3>
                            </div>
                        </div>
                        <div
                            class="mt-4 flex items-center justify-between border-t border-outline-variant/20 pt-3"
                        >
                            <span
                                class="text-[11px] font-semibold tracking-wider text-outline uppercase"
                            >
                                Official Governance
                            </span>
                            <Link
                                :href="doc.url"
                                class="inline-flex items-center gap-1 text-xs font-bold text-primary hover:underline"
                            >
                                <span>Read Policy</span>
                                <span class="material-symbols-outlined text-sm"
                                    >arrow_forward</span
                                >
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
