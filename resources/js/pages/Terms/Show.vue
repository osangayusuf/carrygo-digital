<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import TermsController from '@/actions/App/Http/Controllers/TermsController';
import PublicLayout from '@/layouts/PublicLayout.vue';

defineOptions({ layout: PublicLayout });

const props = defineProps<{
    current_document: {
        name: string;
        slug: string;
        filename: string;
        blocks: Array<{
            type: 'paragraph' | 'table';
            text?: string;
            headers?: string[];
            rows?: string[][];
        }>;
    };
    documents: Array<{
        name: string;
        slug: string;
        filename: string;
        url: string;
        download_url: string;
    }>;
}>();

const page = usePage();
const user = computed(() => (page.props.auth as { user?: { terms_accepted_at?: string | null } })?.user);

const hasAccepted = computed(() => !!user.value?.terms_accepted_at);
const form = useForm({});

function acceptTerms() {
    form.post(TermsController.accept.url(), {
        preserveScroll: true,
    });
}
</script>

<template>
    <Head :title="props.current_document.name" />

    <div class="min-h-screen bg-background px-4 py-8 sm:px-6 lg:px-8 lg:py-12">
        <div class="mx-auto max-w-6xl">
            <!-- Breadcrumbs / Back button -->
            <div class="mb-6 flex items-center justify-between">
                <Link
                    href="/terms"
                    class="inline-flex items-center gap-2 text-xs font-bold text-primary hover:underline"
                >
                    <span class="material-symbols-outlined text-base">arrow_back</span>
                    <span>All Platform Policies</span>
                </Link>
                <span class="text-xs text-outline font-semibold">
                    Jurisdiction: Federal Republic of Nigeria
                </span>
            </div>

            <!-- Acceptance Alert Banner -->
            <div v-if="user && !hasAccepted" class="mb-8 rounded-2xl border-2 border-primary bg-primary/15 p-5 shadow-md">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <span class="material-symbols-outlined text-2xl text-primary">gavel</span>
                        <div>
                            <p class="font-bold text-sm text-on-surface">Terms Acceptance Required</p>
                            <p class="text-xs text-on-surface-variant">
                                Please read this document and accept Carrygo terms before bidding or claiming rewards.
                            </p>
                        </div>
                    </div>
                    <form @submit.prevent="acceptTerms">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="rounded-xl bg-primary px-5 py-2.5 text-xs font-bold text-on-primary shadow-sm hover:bg-tertiary-container transition-all active:scale-95 disabled:opacity-50"
                        >
                            Accept Terms Now
                        </button>
                    </form>
                </div>
            </div>

            <div class="grid gap-8 lg:grid-cols-[280px_1fr]">
                <!-- Sidebar: Document List -->
                <aside class="space-y-2">
                    <h3 class="mb-3 px-3 text-xs font-bold uppercase tracking-wider text-outline">
                        Policy Index ({{ props.documents.length }})
                    </h3>
                    <nav class="space-y-1">
                        <Link
                            v-for="doc in props.documents"
                            :key="doc.slug"
                            :href="doc.url"
                            class="flex items-center gap-2.5 rounded-xl px-3.5 py-2.5 text-xs font-semibold transition-colors"
                            :class="
                                doc.slug === props.current_document.slug
                                    ? 'bg-primary text-on-primary font-bold shadow-sm'
                                    : 'text-on-surface-variant hover:bg-surface-container-high hover:text-on-surface'
                            "
                        >
                            <span class="material-symbols-outlined text-base shrink-0">
                                {{ doc.slug === props.current_document.slug ? 'menu_book' : 'description' }}
                            </span>
                            <span class="truncate">{{ doc.name }}</span>
                        </Link>
                    </nav>
                </aside>

                <!-- Main Reading Area -->
                <main class="rounded-3xl border border-outline-variant/40 bg-surface p-6 sm:p-10 shadow-sm">
                    <div class="mb-6 border-b border-outline-variant/30 pb-6">
                        <div class="flex items-center gap-2 text-xs font-bold text-primary uppercase tracking-wider mb-2">
                            <span class="material-symbols-outlined text-lg">policy</span>
                            <span>Legal Governance Document</span>
                        </div>
                        <h1 class="font-headline text-2xl font-black tracking-tight text-on-surface sm:text-3xl">
                            {{ props.current_document.name }}
                        </h1>
                    </div>

                    <!-- Article Content Blocks (Paragraphs & Formatted Tables) -->
                    <article class="prose prose-sm max-w-none space-y-6 text-on-surface-variant leading-relaxed">
                        <template v-for="(block, i) in props.current_document.blocks" :key="i">
                            <!-- Table Block -->
                            <div v-if="block.type === 'table'" class="my-6 overflow-x-auto rounded-2xl border border-outline-variant/40 bg-surface shadow-sm">
                                <table class="w-full text-left text-xs border-collapse">
                                    <thead v-if="block.headers && block.headers.length" class="bg-surface-container-high text-on-surface font-extrabold border-b border-outline-variant/30 uppercase tracking-wider">
                                        <tr>
                                            <th v-for="(head, hIdx) in block.headers" :key="hIdx" class="px-4 py-3.5 border-r border-outline-variant/20 last:border-r-0">
                                                {{ head }}
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-outline-variant/20 text-on-surface-variant">
                                        <tr v-for="(row, rIdx) in block.rows" :key="rIdx" class="hover:bg-surface-container-low transition-colors">
                                            <td v-for="(cell, cIdx) in row" :key="cIdx" class="px-4 py-3 align-top border-r border-outline-variant/20 last:border-r-0">
                                                {{ cell }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Paragraph Block -->
                            <template v-else-if="block.type === 'paragraph' && block.text">
                                <h2
                                    v-if="block.text.length < 80 && (block.text.startsWith('Section') || block.text.startsWith('Article') || block.text.includes('Policy') || block.text.includes('Rules') || block.text.includes('Definitions'))"
                                    class="mt-6 text-base font-extrabold text-on-surface"
                                >
                                    {{ block.text }}
                                </h2>
                                <p v-else class="text-sm">
                                    {{ block.text }}
                                </p>
                            </template>
                        </template>
                    </article>

                    <!-- Bottom Acceptance Block -->
                    <div v-if="user && !hasAccepted" class="mt-10 border-t border-outline-variant/30 pt-8 text-center">
                        <p class="mb-4 text-xs text-outline">
                            Have you read and understood the {{ props.current_document.name }}?
                        </p>
                        <form @submit.prevent="acceptTerms" class="inline-block">
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="rounded-2xl bg-primary px-8 py-3.5 text-xs font-bold text-on-primary shadow-md hover:bg-tertiary-container transition-all active:scale-95 disabled:opacity-50"
                            >
                                Accept Terms &amp; Continue Bidding
                            </button>
                        </form>
                    </div>
                </main>
            </div>
        </div>
    </div>
</template>
