import { ref } from 'vue';

interface Notification {
    id: number;
    type: 'success' | 'error' | 'info' | 'warning';
    message: string;
    title?: string;
    duration?: number;
    dismissible?: boolean;
    action?: {
        label: string;
        callback: () => void;
    };
}

// Singleton ref to share state across components
const notifications = ref<Notification[]>([]);
const timers = new Map<number, ReturnType<typeof setTimeout>>();
let nextId = 1;

const removeNotification = (id: number) => {
    if (timers.has(id)) {
        clearTimeout(timers.get(id));
        timers.delete(id);
    }

    notifications.value = notifications.value.filter(n => n.id !== id);
};

const addNotification = (notif: Omit<Notification, 'id'>) => {
    const id = nextId++;
    const duration = notif.duration ?? 5000;
    const dismissible = notif.dismissible ?? true;

    notifications.value.push({ ...notif, id, duration, dismissible });

    if (duration > 0) {
        const timer = setTimeout(() => removeNotification(id), duration);
        timers.set(id, timer);
    }

    return id;
};

export const useFlash = () => {
    const success = (message: string, title?: string, duration?: number) => {
        addNotification({ type: 'success', message, title, duration, dismissible: true });
    };

    const error = (message: string, title?: string, duration?: number) => {
        addNotification({ type: 'error', message, title, duration, dismissible: true });
    };

    const warning = (message: string, title?: string, duration?: number) => {
        addNotification({ type: 'warning', message, title, duration, dismissible: true });
    };

    const info = (message: string, title?: string, duration?: number) => {
        addNotification({ type: 'info', message, title, duration, dismissible: true });
    };

    const notify = (
        type: 'success' | 'error' | 'info' | 'warning',
        message: string,
        options?: Partial<Notification>
    ) => {
        addNotification({ type, message, ...options });
    };

    const clearAll = () => {
        notifications.value = [];
        timers.forEach(timer => clearTimeout(timer));
        timers.clear();
    };

    return {
        notifications,
        success,
        error,
        warning,
        info,
        notify,
        clearAll,
        removeNotification,
    };
};
