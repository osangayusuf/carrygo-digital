import { toast } from 'vue-sonner';
import type { FlashToast } from '@/types/ui';

const defaultDuration = 5000;

const toastClassNames = {
    toast: 'carrygo-toast',
    title: 'carrygo-toast__title',
    description: 'carrygo-toast__description',
    actionButton: 'carrygo-toast__action',
    cancelButton: 'carrygo-toast__cancel',
    closeButton: 'carrygo-toast__close',
    icon: 'carrygo-toast__icon',
};

function baseOptions(duration = defaultDuration) {
    return {
        duration,
        classNames: toastClassNames,
    };
}

function show({ type, message }: FlashToast, duration?: number): void {
    const options = baseOptions(duration);

    switch (type) {
        case 'success':
            toast.success(message, options);
            break;
        case 'error':
            toast.error(message, options);
            break;
        case 'warning':
            toast.warning(message, options);
            break;
        case 'info':
            toast.info(message, options);
            break;
    }
}

export const appToast = {
    show,
    success: (message: string, duration?: number) =>
        show({ type: 'success', message }, duration),
    error: (message: string, duration?: number) =>
        show({ type: 'error', message }, duration),
    warning: (message: string, duration?: number) =>
        show({ type: 'warning', message }, duration),
    info: (message: string, duration?: number) =>
        show({ type: 'info', message }, duration),
};
