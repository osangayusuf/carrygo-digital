<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import TermsController from '@/actions/App/Http/Controllers/TermsController';
import { useTermsModal } from '@/composables/useTermsModal';

const props = defineProps<{
    isOpen?: boolean;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'accepted'): void;
}>();

const page = usePage();
const {
    isOpen: composableIsOpen,
    open: openComposable,
    close: closeComposable,
    notifyAccepted,
} = useTermsModal();

const isVisible = computed(() =>
    props.isOpen ? true : composableIsOpen.value,
);

function handleWindowOpenTerms() {
    openComposable();
}

onMounted(() => {
    window.addEventListener('open-terms-modal', handleWindowOpenTerms);
});

onBeforeUnmount(() => {
    window.removeEventListener('open-terms-modal', handleWindowOpenTerms);
});

const user = computed(
    () =>
        (page.props.auth as { user?: { terms_accepted_at?: string | null } })
            ?.user,
);
const hasAccepted = computed(() => !!user.value?.terms_accepted_at);

const documents = computed(() => {
    return (
        (page.props.terms_documents as Array<{
            name: string;
            slug: string;
            filename: string;
            url: string;
        }>) ?? []
    );
});

const searchQuery = ref('');
const filteredDocuments = computed(() => {
    if (!searchQuery.value.trim()) {
        return documents.value;
    }
    const q = searchQuery.value.toLowerCase();
    return documents.value.filter((doc) => doc.name.toLowerCase().includes(q));
});

const agreeChecked = ref(false);
const form = useForm({});

function handleClose() {
    closeComposable();
    emit('close');
}

function acceptTerms() {
    if (!agreeChecked.value) {
        return;
    }

    form.post(TermsController.accept.url(), {
        preserveScroll: true,
        onSuccess: () => {
            notifyAccepted();
            emit('accepted');
            emit('close');
        },
    });
}
</script>

<template>
    <Teleport to="body">
        <div
            v-if="isVisible"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-3 backdrop-blur-md transition-opacity sm:p-6"
        >
            <div class="absolute inset-0" @click="handleClose"></div>

            <div
                class="relative flex max-h-[92vh] w-full max-w-4xl flex-col overflow-hidden rounded-3xl border border-outline-variant/30 bg-surface-container-lowest shadow-2xl"
            >
                <!-- Top Header -->
                <div
                    class="flex items-center justify-between border-b border-outline-variant/30 bg-surface-container-low/50 px-6 py-5 sm:px-8"
                >
                    <div class="flex items-center gap-3">
                        <div
                            class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-primary/10 text-primary"
                        >
                            <span class="material-symbols-outlined text-2xl"
                                >gavel</span
                            >
                        </div>
                        <div>
                            <h2
                                class="font-headline text-lg font-extrabold text-on-surface sm:text-xl"
                            >
                                Bidora Platform Terms &amp; Conditions
                            </h2>
                            <p class="text-xs text-on-surface-variant">
                                Review official policies, bidding regulations,
                                and legal agreements
                            </p>
                        </div>
                    </div>
                    <button
                        type="button"
                        class="flex h-9 w-9 items-center justify-center rounded-full bg-surface-container text-on-surface-variant transition-colors hover:bg-surface-container-high"
                        @click="handleClose"
                    >
                        <span class="material-symbols-outlined text-[20px]"
                            >close</span
                        >
                    </button>
                </div>

                <!-- Status Banner -->
                <div
                    class="border-b border-outline-variant/20 px-6 py-3.5 sm:px-8"
                    :class="
                        hasAccepted
                            ? 'bg-primary/10'
                            : 'bg-secondary-container/30'
                    "
                >
                    <div
                        v-if="hasAccepted"
                        class="flex items-center gap-2 text-xs font-bold text-primary"
                    >
                        <span class="material-symbols-outlined text-base"
                            >verified</span
                        >
                        <span
                            >Terms Accepted on
                            {{
                                new Date(
                                    user!.terms_accepted_at!,
                                ).toLocaleDateString('en-US', {
                                    year: 'numeric',
                                    month: 'short',
                                    day: 'numeric',
                                })
                            }}</span
                        >
                    </div>
                    <div
                        v-else-if="user"
                        class="flex items-center gap-2 text-xs font-bold text-on-surface-variant"
                    >
                        <span
                            class="material-symbols-outlined text-base text-primary"
                            >info</span
                        >
                        <span
                            >Please review and accept these terms to participate
                            in auctions and claim rewards.</span
                        >
                    </div>
                    <div
                        v-else
                        class="flex items-center gap-2 text-xs font-semibold text-outline"
                    >
                        <span class="material-symbols-outlined text-base"
                            >help</span
                        >
                        <span
                            >You are viewing Bidora's official legal terms as a
                            guest.</span
                        >
                    </div>
                </div>

                <!-- Content Area: Documents list & search -->
                <div class="flex-1 space-y-6 overflow-y-auto p-6 sm:p-8">
                    <!-- Search & Filter bar -->
                    <div
                        class="flex flex-col items-stretch justify-between gap-3 sm:flex-row sm:items-center"
                    >
                        <p
                            class="text-xs font-bold tracking-wider text-outline uppercase"
                        >
                            Official Legal Documents ({{
                                filteredDocuments.length
                            }})
                        </p>
                        <div class="relative w-full max-w-xs">
                            <span
                                class="material-symbols-outlined absolute top-1/2 left-3 -translate-y-1/2 text-sm text-outline"
                                >search</span
                            >
                            <input
                                v-model="searchQuery"
                                type="text"
                                placeholder="Search policies..."
                                class="w-full rounded-xl border border-outline-variant bg-surface-container-low py-1.5 pr-3 pl-9 text-xs text-on-surface focus:ring-1 focus:ring-primary focus:outline-none"
                            />
                        </div>
                    </div>

                    <!-- Documents Grid -->
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div
                            v-for="doc in filteredDocuments"
                            :key="doc.filename"
                            class="flex items-center justify-between rounded-2xl border border-outline-variant/40 bg-surface p-4 transition-all hover:border-primary/40 hover:shadow-sm"
                        >
                            <div class="flex min-w-0 items-center gap-3 pr-2">
                                <span
                                    class="material-symbols-outlined shrink-0 text-xl text-primary"
                                    >description</span
                                >
                                <span
                                    class="truncate text-xs font-bold text-on-surface"
                                    :title="doc.name"
                                >
                                    {{ doc.name }}
                                </span>
                            </div>
                            <Link
                                :href="doc.url"
                                @click="handleClose"
                                class="flex shrink-0 items-center gap-1 rounded-xl bg-primary/10 px-3 py-1.5 text-[11px] font-bold text-primary transition-colors hover:bg-primary hover:text-on-primary"
                            >
                                <span>Read Policy</span>
                                <span class="material-symbols-outlined text-xs"
                                    >arrow_forward</span
                                >
                            </Link>
                        </div>
                    </div>

                    <div
                        v-if="filteredDocuments.length === 0"
                        class="py-8 text-center text-xs text-outline"
                    >
                        No policies match your search "{{ searchQuery }}".
                    </div>
                </div>

                <!-- Footer Action Bar -->
                <div
                    class="border-t border-outline-variant/30 bg-surface-container-low/80 p-6 sm:px-8 sm:py-5"
                >
                    <template v-if="user && !hasAccepted">
                        <form @submit.prevent="acceptTerms" class="space-y-3">
                            <label
                                class="flex cursor-pointer items-start gap-3 rounded-xl border border-outline-variant/50 bg-surface p-3.5 select-none hover:bg-surface-container"
                            >
                                <input
                                    type="checkbox"
                                    v-model="agreeChecked"
                                    class="mt-0.5 h-4 w-4 rounded border-outline text-primary focus:ring-primary"
                                />
                                <span
                                    class="text-xs leading-relaxed font-medium text-on-surface"
                                >
                                    I confirm that I have reviewed, understood,
                                    and accept Bidora's
                                    <strong>Terms of Use</strong>,
                                    <strong>Bidding &amp; Winner Rules</strong>,
                                    and <strong>Privacy Policies</strong>.
                                </span>
                            </label>

                            <div
                                class="flex flex-col items-center justify-between gap-3 sm:flex-row"
                            >
                                <button
                                    type="button"
                                    @click="handleClose"
                                    class="w-full rounded-xl px-5 py-2.5 text-xs font-semibold text-on-surface-variant hover:bg-surface-container sm:w-auto"
                                >
                                    Close Without Accepting
                                </button>
                                <button
                                    type="submit"
                                    :disabled="!agreeChecked || form.processing"
                                    class="flex w-full items-center justify-center rounded-2xl bg-primary px-8 py-3 text-xs font-bold text-on-primary shadow-md transition-all hover:bg-tertiary-container active:scale-95 disabled:opacity-50 sm:w-auto"
                                >
                                    <span
                                        v-if="form.processing"
                                        class="material-symbols-outlined mr-2 animate-spin text-sm"
                                    >
                                        progress_activity
                                    </span>
                                    Accept Terms &amp; Continue
                                </button>
                            </div>
                        </form>
                    </template>

                    <template v-else>
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-outline">
                                Bidora Digital Platform Terms v1.0
                            </span>
                            <button
                                type="button"
                                @click="handleClose"
                                class="rounded-xl bg-surface-container-high px-6 py-2.5 text-xs font-bold text-on-surface transition-colors hover:bg-surface-container-highest"
                            >
                                Close
                            </button>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </Teleport>
</template>
