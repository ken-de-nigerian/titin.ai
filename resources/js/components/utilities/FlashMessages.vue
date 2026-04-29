<script setup lang="ts">
    import { usePage } from '@inertiajs/vue3';
    import { CircleAlert, CircleCheck, CircleX, TriangleAlert } from 'lucide-vue-next';
    import type { Component } from 'vue';
    import { watch } from 'vue';
    import { useFlash } from '@/composables/useFlash';

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

    type FlashPayload = {
        success?: string;
        error?: string;
        warning?: string;
        info?: string;
        title?: string;
        duration?: number;
        dismissible?: boolean;
    };

    const page = usePage();
    const { notifications, removeNotification, notify } = useFlash();

    const flashIconComponents: Record<Notification['type'], Component> = {
        success: CircleCheck,
        error: CircleX,
        warning: TriangleAlert,
        info: CircleAlert,
    };

    const getIconComponent = (type: Notification['type']): Component =>
        flashIconComponents[type] ?? CircleAlert;

    const getIconShellClass = (type: Notification['type']) => {
        switch (type) {
            case 'success':
                return 'bg-success/15 text-success';
            case 'error':
                return 'bg-destructive/15 text-destructive';
            case 'warning':
                return 'bg-warning/20 text-warning';
            default:
                return 'bg-brand-soft text-brand';
        }
    };

    const handleAction = (notification: Notification) => {
        if (notification.action?.callback) {
            notification.action.callback();
        }

        removeNotification(notification.id);
    };

    watch(
        () => page.props.flash as FlashPayload | undefined,
        (flash) => {
            if (!flash) {
                return;
            }

            let type: 'success' | 'error' | 'info' | 'warning' = 'info';
            let message = '';

            if (flash.success) {
                type = 'success';
                message = flash.success;
            } else if (flash.error) {
                type = 'error';
                message = flash.error;
            } else if (flash.warning) {
                type = 'warning';
                message = flash.warning;
            } else if (flash.info) {
                type = 'info';
                message = flash.info;
            }

            if (message) {
                notify(type, message, {
                    title: flash.title,
                    duration: flash.duration ?? 5000,
                    dismissible: flash.dismissible !== false,
                });
            }
        },
        { deep: true, immediate: true },
    );
</script>

<template>
    <div id="toast-wrapper-bottom-end" class="fixed bottom-4 right-4 z-260 flex w-[calc(100vw-2rem)] max-w-sm flex-col gap-2 md:bottom-6 md:right-6" role="region" aria-label="Notifications">
        <TransitionGroup
            tag="div"
            class="flex flex-col gap-2"
            enter-active-class="transition-all duration-300 ease-out"
            enter-from-class="opacity-0 translate-y-2 scale-95"
            enter-to-class="opacity-100 translate-y-0 scale-100"
            leave-active-class="transition-all duration-200 ease-in"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 translate-y-1 scale-95"
            move-class="transition-transform duration-300 ease-out">
            <div
                v-for="notification in notifications"
                :key="notification.id"
                class="surface relative w-full overflow-hidden rounded-xl border border-hairline bg-surface p-3 shadow-lg">
                <div class="flex items-start gap-3 pr-7">
                    <div
                        class="flex size-9 shrink-0 items-center justify-center rounded-lg"
                        :class="getIconShellClass(notification.type)">
                        <component
                            :is="getIconComponent(notification.type)"
                            class="size-4.5"
                            stroke-width="2"
                            aria-hidden="true"
                        />
                    </div>

                    <div class="min-w-0 flex-1">
                        <p v-if="notification.title" class="truncate text-sm font-semibold text-foreground">
                            {{ notification.title }}
                        </p>
                        <div
                            class="text-sm text-muted-foreground"
                            :class="notification.title ? 'mt-0.5' : ''"
                            v-html="notification.message"></div>

                        <button
                            v-if="notification.action"
                            type="button"
                            class="mt-2 text-xs font-medium text-brand transition hover:underline"
                            @click="handleAction(notification)">
                            {{ notification.action.label }}
                        </button>
                    </div>
                </div>

                <button
                    v-if="notification.dismissible"
                    type="button"
                    class="absolute right-2.5 top-2.5 inline-flex h-6 w-6 items-center justify-center rounded-md text-muted-foreground transition hover:bg-surface-2 hover:text-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring/40"
                    @click="removeNotification(notification.id)">
                    <span class="sr-only">Close notification</span>
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke-width="2"
                        stroke="currentColor"
                        class="h-4 w-4"
                        aria-hidden="true">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </TransitionGroup>
    </div>
</template>
