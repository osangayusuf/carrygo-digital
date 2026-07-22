<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps<{
    currentPage: number;
    lastPage: number;
}>();

const emit = defineEmits<{
    (e: 'page-change', page: number): void;
}>();

const visiblePages = computed(() => {
    const current = props.currentPage;
    const last = props.lastPage;
    const pages: (number | '...')[] = [];

    if (last <= 7) {
        for (let i = 1; i <= last; i++) {
            pages.push(i);
        }

        return pages;
    }

    pages.push(1);

    if (current > 3) {
        pages.push('...');
    }

    const start = Math.max(2, current - 1);
    const end = Math.min(last - 1, current + 1);

    for (let i = start; i <= end; i++) {
        pages.push(i);
    }

    if (current < last - 2) {
        pages.push('...');
    }

    pages.push(last);

    return pages;
});

function goToPage(page: number | null): void {
    if (page === null || page < 1 || page > props.lastPage) {
        return;
    }

    emit('page-change', page);
}
</script>

<template>
    <div
        v-if="lastPage > 1"
        class="mt-12 flex items-center justify-center gap-3"
    >
        <button
            type="button"
            :disabled="currentPage === 1"
            class="flex h-10 w-10 items-center justify-center rounded-full border-2 border-sage-border bg-white text-muted-green transition-colors hover:border-lemon hover:text-navy disabled:opacity-40"
            @click="goToPage(currentPage - 1)"
        >
            <span class="material-symbols-outlined text-xl">chevron_left</span>
        </button>

        <div class="flex gap-2">
            <template v-for="page in visiblePages" :key="page">
                <span
                    v-if="page === '...'"
                    class="flex h-10 w-10 items-center justify-center text-sm font-bold text-muted-green"
                >
                    ...
                </span>
                <button
                    v-else
                    type="button"
                    class="flex h-10 w-10 items-center justify-center rounded-xl border-2 text-sm font-extrabold transition-colors"
                    :class="
                        page === currentPage
                            ? 'border-navy bg-navy text-lemon'
                            : 'border-sage-border bg-white text-ink hover:border-lemon'
                    "
                    @click="goToPage(page)"
                >
                    {{ page }}
                </button>
            </template>
        </div>

        <button
            type="button"
            :disabled="currentPage === lastPage"
            class="flex h-10 w-10 items-center justify-center rounded-full border-2 border-sage-border bg-white text-muted-green transition-colors hover:border-lemon hover:text-navy disabled:opacity-40"
            @click="goToPage(currentPage + 1)"
        >
            <span class="material-symbols-outlined text-xl">chevron_right</span>
        </button>
    </div>
</template>
