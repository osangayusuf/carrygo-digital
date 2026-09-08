import { defineStore } from 'pinia';
import { computed, ref } from 'vue';

export interface AppNotification {
    id: string;
    type: string;
    data: Record<string, unknown>;
    read_at: string | null;
    created_at: string;
}

export const useNotificationStore = defineStore('notifications', () => {
    const notifications = ref<AppNotification[]>([]);
    const isLoading = ref(false);

    const unreadCount = computed(
        () => notifications.value.filter((n) => n.read_at === null).length,
    );

    /**
     * Fetch unread notifications from the API.
     * Uses the Inertia useHttp hook for standalone JSON requests.
     */
    async function fetchNotifications(): Promise<void> {
        isLoading.value = true;

        try {
            const response = await fetch('/notifications', {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (response.ok) {
                const data = (await response.json()) as AppNotification[];
                notifications.value = data;
            }
        } finally {
            isLoading.value = false;
        }
    }

    /**
     * Mark a single notification as read.
     */
    async function markRead(id: string): Promise<void> {
        const response = await fetch(`/notifications/${id}/read`, {
            method: 'PATCH',
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN':
                    (
                        document.querySelector(
                            'meta[name="csrf-token"]',
                        ) as HTMLMetaElement
                    )?.content ?? '',
            },
        });

        if (response.ok) {
            const notification = notifications.value.find((n) => n.id === id);

            if (notification) {
                notification.read_at = new Date().toISOString();
            }
        }
    }

    /**
     * Mark all notifications as read.
     */
    async function markAllRead(): Promise<void> {
        const response = await fetch('/notifications/read-all', {
            method: 'PATCH',
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN':
                    (
                        document.querySelector(
                            'meta[name="csrf-token"]',
                        ) as HTMLMetaElement
                    )?.content ?? '',
            },
        });

        if (response.ok) {
            const now = new Date().toISOString();
            notifications.value.forEach((n) => {
                n.read_at = now;
            });
        }
    }

    /**
     * Prepend a new notification (e.g. received via Echo broadcast).
     */
    function addNotification(notification: AppNotification): void {
        notifications.value.unshift(notification);
    }

    return {
        notifications,
        unreadCount,
        isLoading,
        fetchNotifications,
        markRead,
        markAllRead,
        addNotification,
    };
});
