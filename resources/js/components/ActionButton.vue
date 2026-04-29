<script lang="ts" setup>
    import type { ClassValue } from 'clsx';
    import { ArrowRight } from 'lucide-vue-next';
    import { computed, useAttrs } from 'vue';
    import { cn } from '@/lib/utils';

    defineOptions({
        inheritAttrs: false,
    });

    defineProps({
        processing: {
            type: Boolean,
            default: false,
        },
        disabled: {
            type: Boolean,
            default: false,
        },
    });

    const attrs = useAttrs();

    function hasCustomClass(): boolean {
        const c = attrs.class;

        if (c == null) {
            return false;
        }

        if (typeof c === 'string') {
            return c.trim().length > 0;
        }

        if (Array.isArray(c)) {
            return c.length > 0;
        }

        return true;
    }

    const mergedClass = computed(() =>
        cn(
            'group mt-2 flex w-full items-center justify-center gap-2 rounded-lg bg-foreground px-4 py-2.5 text-sm font-medium text-background shadow-sm transition hover:bg-foreground/90',
            'disabled:opacity-50 disabled:cursor-not-allowed',
            hasCustomClass() ? null : null,
            attrs.class as ClassValue,
        ),
    );

    type ButtonType = 'button' | 'submit' | 'reset';

    const passthroughAttrs = computed((): Record<string, unknown> & { type: ButtonType } => {
        const raw = { ...(attrs as Record<string, unknown>) };
        const typeRaw = raw.type as string | undefined;
        delete raw.class;
        delete raw.type;

        const type: ButtonType =
            typeRaw === 'button' || typeRaw === 'submit' || typeRaw === 'reset' ? typeRaw : 'submit';

        return {
            ...raw,
            type,
        };
    });
</script>

<template>
    <button v-bind="passthroughAttrs" :disabled="processing || disabled" :class="mergedClass">
        <span v-if="!processing" class="flex items-center justify-center gap-2">
            <slot>Submit</slot>
            <ArrowRight class="h-4 w-4 transition group-hover:translate-x-0.5" />
        </span>
        <span v-else class="flex items-center justify-center gap-2">
            <svg stroke="currentColor" fill="currentColor" stroke-width="0" version="1.1" viewBox="0 0 16 16" class="spinner animate-spin" height="1em" width="1em" xmlns="http://www.w3.org/2000/svg" style="height: 18px; width: 18px;">
                <path d="M8 0c-4.355 0-7.898 3.481-7.998 7.812 0.092-3.779 2.966-6.812 6.498-6.812 3.59 0 6.5 3.134 6.5 7 0 0.828 0.672 1.5 1.5 1.5s1.5-0.672 1.5-1.5c0-4.418-3.582-8-8-8zM8 16c4.355 0 7.898-3.481 7.998-7.812-0.092 3.779-2.966 6.812-6.498 6.812-3.59 0-6.5-3.134-6.5-7 0-0.828-0.672-1.5-1.5-1.5s-1.5 0.672-1.5 1.5c0 4.418 3.582 8 8 8z"></path>
            </svg>
            Loading
        </span>
    </button>
</template>
