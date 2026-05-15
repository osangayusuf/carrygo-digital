<script setup>
import { ref } from 'vue';

const isOpen = ref(false);

const open = () => isOpen.value = true;
const close = () => isOpen.value = false;

defineExpose({ open, close });
</script>

<template>
    <div v-if="isOpen" class="fixed inset-0 z-[100] flex items-start justify-center pt-16 sm:pt-24 px-4 bg-gray-900/50 backdrop-blur-sm" @click.self="close">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-2xl overflow-hidden animate-in fade-in zoom-in-95 duration-200">
            <div class="p-4 border-b border-gray-100 flex items-center">
                <input type="text" class="w-full text-lg border-0 focus:ring-0 px-2 py-1 outline-none" placeholder="Search auctions..." autofocus />
                <button @click="close" class="p-2 text-gray-400 hover:text-gray-600 rounded-md">Esc</button>
            </div>
            <div class="p-6 bg-gray-50 text-center text-sm text-gray-500">
                Start typing to search across all live and closed auctions.
            </div>
        </div>
    </div>
</template>
