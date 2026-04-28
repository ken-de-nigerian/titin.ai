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

    /**
     * Icon tile: subtle fill + stroke via currentColor (Lucide).
     */
    const getIconShellClass = (type: Notification['type']) => {
        switch (type) {
            case 'success':
                return 'bg-[var(--success-subtle)] text-[var(--success)]';
            case 'error':
                return 'bg-[var(--error-subtle)] text-[var(--error)]';
            case 'warning':
                return 'bg-[var(--warning-subtle)] text-[var(--warning)]';
            default:
                return 'bg-[var(--info-subtle)] text-[var(--info)]';
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
    <div
        id="toast-wrapper-bottom-end"
        class="scroll-lock-pad-end"
        role="region"
        aria-label="Notifications">
        <TransitionGroup
            tag="div"
            class="contents"
            enter-active-class="transition-all duration-300 ease-out"
            enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100"
            leave-active-class="transition-all duration-200 ease-in"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-95"
            move-class="transition-transform duration-300 ease-out">
            <div
                v-for="notification in notifications"
                :key="notification.id"
                class="toast-wrapper">
                <div class="notification w-full max-w-87.5">
                    <div
                        class="notification-content"
                        :class="{ 'no-child': !notification.title }">
                        <div
                            class="mr-3 flex size-10 shrink-0 items-center justify-center rounded-lg"
                            :class="[
                                getIconShellClass(notification.type),
                                { 'self-start': Boolean(notification.title) },
                            ]">
                            <component
                                :is="getIconComponent(notification.type)"
                                stroke-width="2"
                                aria-hidden="true"
                            />
                        </div>

                        <div class="mr-4 min-w-0 flex-1 pr-6">
                            <div class="notification-description" v-html="notification.message"></div>
                            <button v-if="notification.action" type="button" class="mt-3 text-sm font-semibold text-primary hover:underline" @click="handleAction(notification)">
                                {{ notification.action.label }}
                            </button>
                        </div>
                    </div>

                    <button
                        v-if="notification.dismissible"
                        type="button"
                        class="close-button button-press-feedback notification-close absolute z-10 text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-100"
                        @click="removeNotification(notification.id)">
                        <span class="sr-only">Close notification</span>
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="2"
                            stroke="currentColor"
                            class="h-[1em] w-[1em]"
                            aria-hidden="true">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </TransitionGroup>
    </div>
</template>
