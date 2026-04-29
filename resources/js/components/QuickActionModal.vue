<script setup lang="ts">
    import { useMediaQuery } from '@vueuse/core';
    import { XIcon } from 'lucide-vue-next';
    import { computed, onBeforeUnmount, useId, watch } from 'vue';
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

    let previousOverflow = '';

    watch(
        () => props.isOpen,
        (open) => {
            if (typeof document === 'undefined') {
                return;
            }

            if (open) {
                previousOverflow = document.body.style.overflow;
                document.body.style.overflow = 'hidden';
                return;
            }

            document.body.style.overflow = previousOverflow;
        },
        { immediate: true },
    );

    onBeforeUnmount(() => {
        if (typeof document !== 'undefined') {
            document.body.style.overflow = previousOverflow;
        }
    });

    const sheetLayout = useMediaQuery('(max-width: 1023px)');

    const transitionName = computed(() => (sheetLayout.value ? 'sheet-layer' : 'dialog-layer'));

    /** Desktop: passed `class` augments the dialog panel (e.g. max width). */
    const cardClasses = computed(() => props.class);

    const sheetPanelClasses =
        'pointer-events-auto !m-0 !mx-0 !my-0 max-h-[min(85dvh,calc(100dvh-2.5rem-max(1.75rem,calc(1rem+env(safe-area-inset-bottom,0px)))))] w-full max-w-none rounded-t-[1.35rem] rounded-b-none border-t border-hairline bg-background shadow-xl';

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
            <div v-show="isOpen" class="fixed inset-0 z-90">
                <div class="layer-static-backdrop absolute inset-0 bg-foreground/30 backdrop-blur-sm" @click="handleClose" />

                <div
                    class="relative z-91 flex min-h-full items-center justify-center p-4 max-lg:block max-lg:w-full max-lg:p-0"
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
                                <div class="flex shrink-0 flex-col border-b border-hairline bg-surface px-4 pb-3 pt-2">
                                    <div class="mb-3 flex justify-center" aria-hidden="true">
                                        <div class="h-1 w-11 shrink-0 rounded-full bg-muted" />
                                    </div>

                                    <div class="flex w-full items-start justify-between gap-3">
                                        <div class="min-w-0 flex flex-1 items-start gap-2 text-left">
                                            <div class="shrink-0 pt-0.5">
                                                <slot name="header-icon" />
                                            </div>
                                            <div class="min-w-0">
                                                <h5 :id="titleId" class="text-base font-semibold tracking-tight text-foreground">
                                                    {{ props.title }}
                                                </h5>
                                                <p v-if="props.subtitle" class="mt-0.5 line-clamp-2 text-xs font-medium text-muted-foreground">
                                                    {{ props.subtitle }}
                                                </p>
                                            </div>
                                        </div>
                                        <button
                                            type="button"
                                            class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-background text-muted-foreground shadow-sm ring-1 ring-hairline transition active:scale-95 hover:bg-surface-2 hover:text-foreground"
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

                                    <div v-if="$slots.footer" class="mt-6 border-t border-hairline pt-4">
                                        <slot name="footer" />
                                    </div>
                                </div>
                            </template>

                            <template v-else>
                                <div class="mb-4 flex items-start justify-between gap-4">
                                    <div class="min-w-0 flex-1">
                                        <h3 :id="titleId" class="text-lg font-semibold text-foreground">
                                            <slot name="header-icon" />
                                            {{ props.title }}
                                        </h3>

                                        <p v-if="props.subtitle" class="mt-1 text-sm text-muted-foreground">
                                            {{ props.subtitle }}
                                        </p>
                                    </div>

                                    <button
                                        type="button"
                                        class="rounded-lg p-2 text-muted-foreground hover:bg-surface-2 hover:text-foreground"
                                        aria-label="Close dialog"
                                        @click="handleClose">
                                        <XIcon class="h-5 w-5" />
                                    </button>
                                </div>

                                <div class="space-y-6">
                                    <slot />
                                </div>

                                <div v-if="$slots.footer" class="mt-6 border-t border-hairline pt-4">
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
    /* Desktop dialog transition */
    .dialog-layer-enter-active,
    .dialog-layer-leave-active {
        transition: opacity 0.2s ease;
    }

    .dialog-layer-enter-from,
    .dialog-layer-leave-to {
        opacity: 0;
    }

    .dialog-layer-enter-active .layer-static-backdrop,
    .dialog-layer-leave-active .layer-static-backdrop {
        transition: opacity 0.18s ease, backdrop-filter 0.22s ease;
    }

    .dialog-layer-enter-from .layer-static-backdrop,
    .dialog-layer-leave-to .layer-static-backdrop {
        opacity: 0;
        backdrop-filter: blur(0px);
    }

    .dialog-layer-enter-active :deep(.dialog-layer__panel),
    .dialog-layer-leave-active :deep(.dialog-layer__panel) {
        transition: transform 0.24s ease, opacity 0.24s ease;
        transition-delay: 0.06s;
    }

    .dialog-layer-enter-from :deep(.dialog-layer__panel),
    .dialog-layer-leave-to :deep(.dialog-layer__panel) {
        transform: translateY(10px) scale(0.98);
        opacity: 0;
    }

    /* Mobile sheet transition */
    .sheet-layer-enter-active,
    .sheet-layer-leave-active {
        transition: opacity 0.2s ease;
    }

    .sheet-layer-enter-from,
    .sheet-layer-leave-to {
        opacity: 0;
    }

    .sheet-layer-enter-active .layer-static-backdrop,
    .sheet-layer-leave-active .layer-static-backdrop {
        transition: opacity 0.18s ease, backdrop-filter 0.22s ease;
    }

    .sheet-layer-enter-from .layer-static-backdrop,
    .sheet-layer-leave-to .layer-static-backdrop {
        opacity: 0;
        backdrop-filter: blur(0px);
    }

    .sheet-layer-enter-active :deep(.sheet-layer__panel),
    .sheet-layer-leave-active :deep(.sheet-layer__panel) {
        transition: transform 0.28s cubic-bezier(0.22, 1, 0.36, 1), opacity 0.2s ease;
        transition-delay: 0.07s;
    }

    .sheet-layer-enter-from :deep(.sheet-layer__panel),
    .sheet-layer-leave-to :deep(.sheet-layer__panel) {
        transform: translateY(24px);
        opacity: 0.98;
    }
</style>
