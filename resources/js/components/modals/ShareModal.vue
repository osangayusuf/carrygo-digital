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
    return (
        props.message ||
        `I am bidding for this ${props.name}. Join me to bid on bidora`
    );
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
                class="relative w-full max-w-md overflow-hidden rounded-3xl bg-surface-container-lowest p-6 text-left shadow-2xl"
            >
                <!-- Close Button -->
                <button
                    type="button"
                    class="absolute top-4 right-4 flex h-8 w-8 items-center justify-center rounded-full bg-surface-container text-on-surface-variant transition-colors hover:bg-surface-container-high"
                    @click="emit('close')"
                >
                    <span class="material-symbols-outlined text-[20px]"
                        >close</span
                    >
                </button>

                <h2
                    class="mb-2 flex items-center gap-2 font-headline text-2xl font-extrabold text-on-surface"
                >
                    <span class="pi pi-share-alt text-primary"></span> Share
                    this Auction
                </h2>
                <p class="mb-6 text-sm text-outline">
                    Invite your friends to join the bid on
                    <span class="font-bold text-on-surface">{{
                        props.name
                    }}</span>
                </p>

                <!-- Share options grid -->
                <div class="mb-6 grid grid-cols-3 gap-4">
                    <!-- WhatsApp -->
                    <a
                        :href="waUrl"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="group flex cursor-pointer flex-col items-center justify-center rounded-2xl border border-emerald-500/20 bg-emerald-600/10 p-3 text-emerald-600 no-underline transition-all hover:border-emerald-500/40 hover:bg-emerald-600/20"
                    >
                        <i
                            class="pi pi-whatsapp mb-1.5 text-2xl transition-transform group-hover:scale-110"
                        ></i>
                        <span class="text-xs font-bold text-emerald-800"
                            >WhatsApp</span
                        >
                    </a>

                    <!-- Twitter / X -->
                    <a
                        :href="xUrl"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="group flex cursor-pointer flex-col items-center justify-center rounded-2xl border border-on-surface/10 bg-on-surface/5 p-3 text-on-surface no-underline transition-all hover:border-on-surface/20 hover:bg-on-surface/10"
                    >
                        <svg
                            class="mb-1.5 h-6 w-6 fill-current text-on-surface transition-transform group-hover:scale-110"
                            viewBox="0 0 24 24"
                        >
                            <path
                                d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"
                            />
                        </svg>
                        <span class="text-xs font-bold text-on-surface"
                            >Share on X</span
                        >
                    </a>

                    <!-- Facebook -->
                    <a
                        :href="fbUrl"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="group flex cursor-pointer flex-col items-center justify-center rounded-2xl border border-blue-500/20 bg-blue-600/10 p-3 text-blue-600 no-underline transition-all hover:border-blue-500/40 hover:bg-blue-600/20"
                        @click="handleFacebookClick"
                    >
                        <i
                            class="pi pi-facebook mb-1.5 text-2xl transition-transform group-hover:scale-110"
                        ></i>
                        <span class="text-xs font-bold text-blue-800"
                            >Facebook</span
                        >
                    </a>
                </div>

                <!-- Facebook clipboard tip -->
                <div
                    v-if="showFacebookTip"
                    class="mb-6 flex items-start gap-2.5 rounded-2xl border border-blue-500/20 bg-blue-600/10 p-3.5 transition-all duration-300"
                >
                    <span
                        class="pi pi-info-circle mt-0.5 shrink-0 text-[18px] text-blue-600"
                    ></span>
                    <div>
                        <p class="mb-0.5 text-xs font-bold text-blue-800">
                            Share text copied to clipboard!
                        </p>
                        <p class="text-[11px] leading-tight text-blue-800/80">
                            Facebook does not allow apps to pre-fill post text.
                            You can paste the copied text directly into your
                            Facebook post.
                        </p>
                    </div>
                </div>

                <!-- Copy Link Section -->
                <div class="border-t border-surface-container pt-4">
                    <label
                        class="mb-2 block text-xs font-bold tracking-widest text-outline uppercase"
                        >Copy Auto-Generated Invite</label
                    >
                    <div
                        class="flex items-center gap-2 rounded-2xl border border-surface-container bg-surface-container-low p-2.5"
                    >
                        <p
                            class="flex-1 truncate pr-2 font-mono text-xs text-on-surface-variant"
                        >
                            {{ formattedShareText }}
                        </p>
                        <button
                            type="button"
                            class="flex shrink-0 cursor-pointer items-center justify-center gap-1.5 rounded-xl bg-primary px-3 py-1.5 text-xs font-bold text-on-primary transition-all hover:bg-on-primary-fixed"
                            @click="copyToClipboard"
                        >
                            <i
                                :class="copied ? 'pi pi-check' : 'pi pi-copy'"
                            ></i>
                            {{ copied ? 'Copied' : 'Copy' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </Teleport>
</template>
