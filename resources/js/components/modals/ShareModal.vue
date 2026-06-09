<script setup lang="ts">
import { ref, computed } from 'vue';

const props = defineProps<{
    isOpen: boolean;
    url: string;
    name: string;
    message?: string;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
}>();

const copied = ref(false);

const shareMessage = computed(() => {
    return props.message || `I am bidding for this ${props.name}. Join me to bid on carrygo`;
});

const formattedShareText = computed(() => {
    if (props.message) {
        return `${shareMessage.value} ${props.url}`;
    }
    return `${shareMessage.value} - ${props.url}`;
});

const fbUrl = computed(() => {
    return `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(props.url)}&quote=${encodeURIComponent(shareMessage.value)}`;
});

const waUrl = computed(() => {
    return `https://api.whatsapp.com/send?text=${encodeURIComponent(formattedShareText.value)}`;
});

const xUrl = computed(() => {
    return `https://twitter.com/intent/tweet?text=${encodeURIComponent(shareMessage.value)}&url=${encodeURIComponent(props.url)}`;
});

const showFacebookTip = ref(false);

async function handleFacebookClick(): Promise<void> {
    try {
        await navigator.clipboard.writeText(formattedShareText.value);
        showFacebookTip.value = true;
        setTimeout(() => {
            showFacebookTip.value = false;
        }, 6000);
    } catch (err) {
        console.error('Failed to copy text: ', err);
    }
}

async function copyToClipboard(): Promise<void> {
    try {
        const textToCopy = formattedShareText.value;
        await navigator.clipboard.writeText(textToCopy);
        copied.value = true;
        setTimeout(() => {
            copied.value = false;
        }, 2000);
    } catch (err) {
        console.error('Failed to copy text: ', err);
    }
}
</script>

<template>
    <Teleport to="body">
        <div
            v-if="props.isOpen"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4 backdrop-blur-sm"
        >
            <!-- Overlay click to close -->
            <div class="absolute inset-0" @click="emit('close')"></div>

            <!-- Modal Content -->
            <div
                class="relative w-full max-w-md overflow-hidden rounded-3xl bg-surface-container-lowest p-6 shadow-2xl text-left"
            >
                <!-- Close Button -->
                <button
                    type="button"
                    class="absolute top-4 right-4 flex h-8 w-8 items-center justify-center rounded-full bg-surface-container text-on-surface-variant transition-colors hover:bg-surface-container-high"
                    @click="emit('close')"
                >
                    <span class="material-symbols-outlined text-[20px]">close</span>
                </button>

                <h2 class="mb-2 font-headline text-2xl font-extrabold text-on-surface flex items-center gap-2">
                    <span class="pi pi-share-alt text-primary"></span> Share this Auction
                </h2>
                <p class="mb-6 text-sm text-outline">
                    Invite your friends to join the bid on <span class="font-bold text-on-surface">{{ props.name }}</span>
                </p>

                <!-- Share options grid -->
                <div class="grid grid-cols-3 gap-4 mb-6">
                    <!-- WhatsApp -->
                    <a
                        :href="waUrl"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="flex flex-col items-center justify-center p-3 rounded-2xl bg-emerald-600/10 hover:bg-emerald-600/20 border border-emerald-500/20 hover:border-emerald-500/40 transition-all text-emerald-600 no-underline cursor-pointer group"
                    >
                        <i class="pi pi-whatsapp text-2xl mb-1.5 group-hover:scale-110 transition-transform"></i>
                        <span class="text-xs font-bold text-emerald-800">WhatsApp</span>
                    </a>

                    <!-- Twitter / X -->
                    <a
                        :href="xUrl"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="flex flex-col items-center justify-center p-3 rounded-2xl bg-on-surface/5 hover:bg-on-surface/10 border border-on-surface/10 hover:border-on-surface/20 transition-all text-on-surface no-underline cursor-pointer group"
                    >
                        <svg class="h-6 w-6 fill-current mb-1.5 group-hover:scale-110 transition-transform text-on-surface" viewBox="0 0 24 24">
                            <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                        </svg>
                        <span class="text-xs font-bold text-on-surface">Share on X</span>
                    </a>

                    <!-- Facebook -->
                    <a
                        :href="fbUrl"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="flex flex-col items-center justify-center p-3 rounded-2xl bg-blue-600/10 hover:bg-blue-600/20 border border-blue-500/20 hover:border-blue-500/40 transition-all text-blue-600 no-underline cursor-pointer group"
                        @click="handleFacebookClick"
                    >
                        <i class="pi pi-facebook text-2xl mb-1.5 group-hover:scale-110 transition-transform"></i>
                        <span class="text-xs font-bold text-blue-800">Facebook</span>
                    </a>
                </div>

                <!-- Facebook clipboard tip -->
                <div v-if="showFacebookTip" class="mb-6 p-3.5 rounded-2xl bg-blue-600/10 border border-blue-500/20 flex items-start gap-2.5 transition-all duration-300">
                    <span class="pi pi-info-circle text-blue-600 text-[18px] mt-0.5 shrink-0"></span>
                    <div>
                        <p class="text-xs font-bold text-blue-800 mb-0.5">Share text copied to clipboard!</p>
                        <p class="text-[11px] text-blue-800/80 leading-tight">Facebook does not allow apps to pre-fill post text. You can paste the copied text directly into your Facebook post.</p>
                    </div>
                </div>

                <!-- Copy Link Section -->
                <div class="border-t border-surface-container pt-4">
                    <label class="block text-xs font-bold tracking-widest text-outline uppercase mb-2">Copy Auto-Generated Invite</label>
                    <div class="flex items-center gap-2 bg-surface-container-low p-2.5 rounded-2xl border border-surface-container">
                        <p class="text-xs text-on-surface-variant truncate flex-1 font-mono pr-2">
                            {{ formattedShareText }}
                        </p>
                        <button
                            type="button"
                            class="shrink-0 flex items-center justify-center gap-1.5 bg-primary hover:bg-on-primary-fixed text-on-primary font-bold text-xs py-1.5 px-3 rounded-xl transition-all cursor-pointer"
                            @click="copyToClipboard"
                        >
                            <i :class="copied ? 'pi pi-check' : 'pi pi-copy'"></i>
                            {{ copied ? 'Copied' : 'Copy' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </Teleport>
</template>
