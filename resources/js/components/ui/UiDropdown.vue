<script setup lang="ts">
    import { onClickOutside, onKeyStroke, useEventListener } from '@vueuse/core';
    import { computed, nextTick, ref, watch } from 'vue';
    import { useHydrated } from '@/composables/useHydrated';

    const props = withDefaults(
        defineProps<{
            placement?: 'above' | 'below';
            align?: 'start' | 'end' | 'stretch';
            /** Extra classes on the inner `<ul class="dropdown-menu">` (e.g. `min-w-[150px]`). */
            menuClass?: string;
            /**
             * When true (default), the menu is teleported to `document.body` with fixed coordinates
             * (updates on scroll). When false, the menu is absolutely positioned under the trigger
             * inside the component, matching document flow like `CustomSelectDropdown`.
             */
            teleportToBody?: boolean;
        }>(),
        {
            placement: 'below',
            align: 'start',
            menuClass: '',
            teleportToBody: true,
        },
    );

    const open = ref(false);
    const rootRef = ref<HTMLElement | null>(null);
    const panelRef = ref<HTMLElement | null>(null);
    const panelStyles = ref<Record<string, string>>({});
    const hydrated = useHydrated();

    const panelPositionClass = computed(() => {
        if (props.teleportToBody) {
            return 'dropdown-menu-wrapper fixed z-[200]';
        }

        const base = 'dropdown-menu-wrapper absolute z-[200]';

        if (props.placement === 'below') {
            if (props.align === 'stretch') {
                return `${base} left-0 right-0 top-full mt-1 w-full`;
            }

            if (props.align === 'end') {
                return `${base} right-0 top-full mt-1`;
            }

            return `${base} left-0 top-full mt-1`;
        }

        if (props.align === 'stretch') {
            return `${base} inset-x-0 bottom-full mb-1 w-full`;
        }

        if (props.align === 'end') {
            return `${base} right-0 bottom-full mb-1`;
        }

        return `${base} left-0 bottom-full mb-1`;
    });

    const stopPositionListeners: Array<() => void> = [];

    /** Scroll containers between the trigger and the viewport (window scroll is appended last). */
    function getScrollParents(node: HTMLElement | null): (HTMLElement | Window)[] {
        const list: (HTMLElement | Window)[] = [];

        if (!node) {
            list.push(window);

            return list;
        }

        let el: HTMLElement | null = node.parentElement;

        while (el) {
            const style = getComputedStyle(el);
            const overflow = [style.overflow, style.overflowX, style.overflowY].join(' ');

            if (!/(auto|scroll|overlay)/.test(overflow)) {
                el = el.parentElement;

                continue;
            }

            const canScrollY = el.scrollHeight > el.clientHeight + 1;
            const canScrollX = el.scrollWidth > el.clientWidth + 1;

            if (canScrollY || canScrollX) {
                list.push(el);
            }

            el = el.parentElement;
        }

        list.push(window);

        return list;
    }

    // Use root + ignore panel. A computed array of elements breaks VueUse: it treats non-Elements as
    // component instances and reads vm.$.subTree (throws on plain arrays).
    onClickOutside(
        rootRef,
        () => {
            open.value = false;
        },
        { ignore: [panelRef] },
    );

    onKeyStroke('Escape', () => {
        if (open.value) {
            open.value = false;
        }
    });

    function toggle(): void {
        open.value = !open.value;
    }

    function close(): void {
        open.value = false;
    }

    function updatePosition(): void {
        const trigger = rootRef.value;
        const panel = panelRef.value;

        if (!trigger) {
            return;
        }

        const rect = trigger.getBoundingClientRect();
        const gap = 4;
        const pad = 8;

        let menuW = panel?.offsetWidth ?? 220;
        let menuH = panel?.offsetHeight ?? 200;

        let top: number;
        if (props.placement === 'below') {
            top = rect.bottom + gap;
        } else {
            top = rect.top - menuH - gap;
            if (top < pad && rect.bottom + gap + menuH < window.innerHeight - pad) {
                top = rect.bottom + gap;
            }
        }

        let left: number;
        let width: string | undefined;

        if (props.align === 'stretch') {
            left = rect.left;
            width = `${rect.width}px`;
            menuW = rect.width;
        } else if (props.align === 'end') {
            left = rect.right - menuW;
        } else {
            left = rect.left;
        }

        if (panel && props.align === 'end') {
            menuW = panel.offsetWidth;
            menuH = panel.offsetHeight;
            left = rect.right - menuW;
        }

        left = Math.min(Math.max(pad, left), window.innerWidth - menuW - pad);
        top = Math.min(Math.max(pad, top), window.innerHeight - menuH - pad);

        const styles: Record<string, string> = {
            top: `${top}px`,
            left: `${left}px`,
        };
        if (width !== undefined) {
            styles.width = width;
        }
        panelStyles.value = styles;
    }

    watch(open, (isOpen) => {
        stopPositionListeners.forEach((s) => s());
        stopPositionListeners.length = 0;

        if (!isOpen) {
            return;
        }

        void nextTick(() => {
            if (props.teleportToBody) {
                updatePosition();
                requestAnimationFrame(() => {
                    updatePosition();
                    panelRef.value?.focus({ preventScroll: true });
                });
            } else {
                requestAnimationFrame(() => {
                    panelRef.value?.focus({ preventScroll: true });
                });
            }
        });

        if (!props.teleportToBody) {
            return;
        }

        stopPositionListeners.push(useEventListener(window, 'resize', () => requestAnimationFrame(updatePosition)));

        for (const target of getScrollParents(rootRef.value)) {
            stopPositionListeners.push(
                useEventListener(target, 'scroll', () => requestAnimationFrame(updatePosition), { passive: true }),
            );
        }

        if (typeof window !== 'undefined' && window.visualViewport) {
            stopPositionListeners.push(
                useEventListener(window.visualViewport, 'scroll', () => requestAnimationFrame(updatePosition), {
                    passive: true,
                }),
                useEventListener(window.visualViewport, 'resize', () => requestAnimationFrame(updatePosition)),
            );
        }
    });
</script>

<template>
    <div ref="rootRef" :class="{ relative: !teleportToBody }">
        <div
            class="outline-none"
            :aria-expanded="open"
            aria-haspopup="menu"
            @click.stop="toggle">
            <slot name="trigger" :open="open" :toggle="toggle" :close="close" />
        </div>

        <Teleport v-if="hydrated" :disabled="!teleportToBody" to="body">
            <div
                v-if="open"
                ref="panelRef"
                data-dropdown-panel
                tabindex="-1"
                :class="panelPositionClass"
                :style="teleportToBody ? panelStyles : undefined"
                role="menu"
                aria-orientation="vertical"
                @click.stop>
                <slot name="menu" :close="close" />
            </div>
        </Teleport>
    </div>
</template>
