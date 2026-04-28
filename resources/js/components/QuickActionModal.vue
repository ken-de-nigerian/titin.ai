<script setup lang="ts">
    import { useMediaQuery } from '@vueuse/core';
    import { XIcon } from 'lucide-vue-next';
    import { computed, useId } from 'vue';
    import { useBodyScrollLock } from '@/composables/useBodyScrollLock';
    import { useHydrated } from '@/composables/useHydrated';

    const props = withDefaults(
        defineProps<{
            isOpen: boolean;
            title: string;
            subtitle?: string;
            class?: string;
        }>(),
        { subtitle: undefined, class: '' },
    );

    const emit = defineEmits<{
        (e: 'close'): void;
    }>();

    const handleClose = () => emit('close');

    const hydrated = useHydrated();
    const titleId = useId();

    useBodyScrollLock(() => props.isOpen);

    const sheetLayout = useMediaQuery('(max-width: 1023px)');

    const transitionName = computed(() => (sheetLayout.value ? 'sheet-layer' : 'dialog-layer'));

    /** Desktop: passed `class` augments the dialog panel (e.g. max width). */
    const cardClasses = computed(() => props.class);

    const sheetPanelClasses =
        'pointer-events-auto !m-0 !mx-0 !my-0 max-h-[min(85dvh,calc(100dvh-2.5rem-max(1.75rem,calc(1rem+env(safe-area-inset-bottom,0px)))))] w-full max-w-none rounded-t-[1.35rem] rounded-b-none border-t border-gray-200 bg-white shadow-[0_-8px_40px_-12px_rgba(0,0,0,0.25)] dark:border-gray-700 dark:bg-gray-900 dark:shadow-[0_-12px_48px_-8px_rgba(0,0,0,0.5)]';

    const panelClasses = computed(() => {
        if (sheetLayout.value) {
            return ['dialog-content', 'relative', 'z-[42]', 'flex', 'w-full', 'flex-col', 'overflow-hidden', 'p-0', sheetPanelClasses];
        }

        return ['dialog-content', 'dialog-layer__panel', 'relative', 'z-[42]', 'w-full', 'max-w-lg', cardClasses.value];
    });
</script>

<template>
    <Teleport v-if="hydrated" to="body">
        <Transition :name="transitionName">
            <div v-show="isOpen" class="dialog-content-wrapper max-lg:z-100">
                <div class="layer-static-backdrop absolute inset-0 bg-gray-900/40 dark:bg-black/50" @click="handleClose" />

                <div
                    class="dialog relative z-41 flex min-h-full items-center justify-center p-4 max-lg:block max-lg:w-full max-lg:p-0"
                    @click.self="handleClose">
                    <div :class="sheetLayout ? 'sheet-layer__panel pointer-events-none absolute inset-x-0 bottom-0 flex w-full pt-3' : 'contents'">
                        <div
                            :class="panelClasses"
                            tabindex="-1"
                            data-floating-ui-focusable=""
                            role="dialog"
                            aria-modal="true"
                            :aria-labelledby="titleId"
                            @click.stop>
                            <template v-if="sheetLayout">
                                <div class="flex shrink-0 flex-col border-b border-gray-200 bg-gray-50 px-4 pb-3 pt-2 dark:border-gray-700 dark:bg-gray-800">
                                    <div class="mb-3 flex justify-center" aria-hidden="true">
                                        <div class="h-1 w-11 shrink-0 rounded-full bg-gray-300 dark:bg-gray-600" />
                                    </div>

                                    <div class="flex w-full items-start justify-between gap-3">
                                        <div class="min-w-0 flex flex-1 items-start gap-2 text-left">
                                            <div class="shrink-0 pt-0.5">
                                                <slot name="header-icon" />
                                            </div>
                                            <div class="min-w-0">
                                                <h5 :id="titleId" class="text-base font-semibold tracking-tight text-gray-900 dark:text-gray-50">
                                                    {{ props.title }}
                                                </h5>
                                                <p v-if="props.subtitle" class="mt-0.5 line-clamp-2 text-xs font-medium text-gray-500 dark:text-gray-400">
                                                    {{ props.subtitle }}
                                                </p>
                                            </div>
                                        </div>
                                        <button
                                            type="button"
                                            class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-white text-gray-600 shadow-sm ring-1 ring-gray-200 transition active:scale-95 hover:bg-gray-100 dark:bg-gray-950 dark:text-gray-300 dark:ring-gray-600 dark:hover:bg-gray-900"
                                            aria-label="Close dialog"
                                            @click="handleClose">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" class="h-5 w-5" aria-hidden="true">
                                                <path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>

                                <div class="min-h-0 flex-1 overflow-y-auto p-4 max-lg:overscroll-y-contain">
                                    <div class="space-y-6">
                                        <slot />
                                    </div>

                                    <div v-if="$slots.footer" class="mt-6 border-t border-gray-200 pt-4 dark:border-gray-700">
                                        <slot name="footer" />
                                    </div>
                                </div>
                            </template>

                            <template v-else>
                                <div class="mb-4 flex items-start justify-between gap-4">
                                    <div class="min-w-0 flex-1">
                                        <h3 :id="titleId" class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                            <slot name="header-icon" />
                                            {{ props.title }}
                                        </h3>

                                        <p v-if="props.subtitle" class="mt-1 text-sm text-gray-500 dark:text-gray-300">
                                            {{ props.subtitle }}
                                        </p>
                                    </div>

                                    <button
                                        type="button"
                                        class="rounded-lg p-2 text-gray-500 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-gray-100"
                                        aria-label="Close dialog"
                                        @click="handleClose">
                                        <XIcon class="h-5 w-5" />
                                    </button>
                                </div>

                                <div class="space-y-6">
                                    <slot />
                                </div>

                                <div v-if="$slots.footer" class="mt-6 border-t border-gray-200 pt-4 dark:border-gray-700">
                                    <slot name="footer" />
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
    /* Styling to hide the scrollbar while allowing scrolling */
    .no-scrollbar::-webkit-scrollbar {
        display: none; /* Hide scrollbar for Chrome, Safari and Opera */
    }

    .no-scrollbar {
        -ms-overflow-style: none; /* IE and Edge */
        scrollbar-width: none; /* Firefox */
    }
</style>
