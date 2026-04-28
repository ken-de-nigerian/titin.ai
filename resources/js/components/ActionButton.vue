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
            <svg height="18" width="18" viewBox="0 0 16 16" fill="none" aria-hidden="true" aria-labelledby=":r1:" class="spinner animate-spin mr-1" style="height: 18px; width: 18px;">
                <circle cx="8" cy="8" r="7" stroke="currentColor" stroke-opacity="0.25" stroke-width="2" vector-effect="non-scaling-stroke"></circle>
                <path d="M15 8a7.002 7.002 0 00-7-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" vector-effect="non-scaling-stroke"></path>
            </svg>
            Loading
        </span>
    </button>
</template>
