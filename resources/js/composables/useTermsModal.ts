import { ref } from 'vue';

const isOpen = ref(false);
const onAcceptCallback = ref<(() => void) | null>(null);

export function useTermsModal() {
    function open(onAccepted?: () => void): void {
        isOpen.value = true;
        if (onAccepted) {
            onAcceptCallback.value = onAccepted;
        } else {
            onAcceptCallback.value = null;
        }
    }

    function close(): void {
        isOpen.value = false;
        onAcceptCallback.value = null;
    }

    function notifyAccepted(): void {
        if (onAcceptCallback.value) {
            const cb = onAcceptCallback.value;
            onAcceptCallback.value = null;
            cb();
        }
        isOpen.value = false;
    }

    return {
        isOpen,
        open,
        close,
        notifyAccepted,
    };
}
