import { echo } from '@laravel/echo-vue';
import { onBeforeUnmount, watch, type Ref } from 'vue';

export interface NavbarNotification {
    id: string;
    title: string;
    body: string;
    icon: string;
    created_at: string;
    read_at: string | null;
    url: string | null;
}

/**
 * Subscribe to the authenticated user's private channel for live notification updates.
 */
export function useRealtimeNotifications(
    userId: Ref<number | null | undefined>,
    notifications: Ref<NavbarNotification[]>,
    refreshNotifications: () => Promise<void>,
): void {
    let channelName: string | null = null;

    function unsubscribe(): void {
        if (typeof window === 'undefined') {
            return;
        }
        if (channelName !== null) {
            echo().leave(channelName);
            channelName = null;
        }
    }

    function subscribe(id: number): void {
        if (typeof window === 'undefined') {
            return;
        }
        unsubscribe();
        channelName = `App.Models.User.${id}`;

        echo()
            .private(channelName)
            .listen('.NotificationCreated', (payload: { notification?: NavbarNotification }) => {
                const item = payload?.notification;

                if (!item?.id) {
                    refreshNotifications();

                    return;
                }

                if (notifications.value.some((n) => n.id === item.id)) {
                    return;
                }

                notifications.value.unshift(item);
            });
    }

    watch(
        userId,
        (id) => {
            unsubscribe();

            if (id) {
                subscribe(id);
            }
        },
        { immediate: true },
    );

    onBeforeUnmount(unsubscribe);
}
