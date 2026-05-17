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
    <div v-if="lastPage > 1" class="mt-20 flex items-center justify-center gap-4">
        <button
            type="button"
            :disabled="currentPage === 1"
            class="flex items-center justify-center rounded-full bg-surface-container-low p-2 text-outline transition-colors hover:bg-surface-container active:scale-90 disabled:opacity-40"
            @click="goToPage(currentPage - 1)"
        >
            <span class="material-symbols-outlined">chevron_left</span>
        </button>

        <div class="flex gap-2">
            <template v-for="page in visiblePages" :key="page">
                <span v-if="page === '...'" class="flex w-10 items-center justify-center text-outline">
                    ...
                </span>
                <button
                    v-else
                    type="button"
                    class="flex h-10 w-10 items-center justify-center rounded-xl text-sm font-bold transition-colors"
                    :class="page === currentPage
                        ? 'bg-primary text-white'
                        : 'bg-surface-container-low text-on-surface hover:bg-surface-container'"
                    @click="goToPage(page)"
                >
                    {{ page }}
                </button>
            </template>
        </div>

        <button
            type="button"
            :disabled="currentPage === lastPage"
            class="flex items-center justify-center rounded-full bg-surface-container-low p-2 text-outline transition-colors hover:bg-surface-container active:scale-90 disabled:opacity-40"
            @click="goToPage(currentPage + 1)"
        >
            <span class="material-symbols-outlined">chevron_right</span>
        </button>
    </div>
</template>
