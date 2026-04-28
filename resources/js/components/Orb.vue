<script setup lang="ts">
    import { Motion } from 'motion-v';
    import { computed } from 'vue';

    import { cn } from '@/lib/utils';

    export type OrbState = 'idle' | 'listening' | 'thinking' | 'speaking';

    const props = withDefaults(
        defineProps<{
            state?: OrbState;
            size?: number;
            className?: string;
            fill?: boolean;
            interactive?: boolean;
            heroRipples?: boolean;
        }>(),
        {
            state: 'idle',
            size: 320,
            className: '',
            fill: false,
            interactive: false,
            heroRipples: false,
        },
    );

    const rippleIndices = [0, 1, 2, 3];

    const stateConfig: Record<
        OrbState,
        {
            mid: number;
            duration: number;
            ringOpacity: number;
        }
    > = {
        idle: { mid: 1.012, duration: 6.0, ringOpacity: 0.5 },
        listening: { mid: 1.04, duration: 1.8, ringOpacity: 0.9 },
        thinking: { mid: 1.008, duration: 3.2, ringOpacity: 0.45 },
        speaking: { mid: 1.025, duration: 1.1, ringOpacity: 1.0 },
    };

    const cfg = computed(() => {
        const base = stateConfig[props.state];

        if (props.heroRipples && props.state === 'idle') {
            return {
                mid: Math.max(base.mid, 1.045),
                duration: 4.25,
                ringOpacity: Math.min(0.85, base.ringOpacity + 0.22),
            };
        }

        return base;
    });

    const rippleDuration = 6.8;

    const rippleAnimate = {
        scale: [1, 1.1, 1.38, 1.56],
        opacity: [0, 0.34, 0.14, 0],
    };

    const rippleTransition = (index: number) => ({
        duration: rippleDuration,
        repeat: Infinity,
        repeatType: 'loop' as const,
        ease: [0.45, 0.05, 0.55, 0.95] as [number, number, number, number],
        times: [0, 0.2, 0.55, 1],
        delay: index * (rippleDuration / rippleIndices.length),
    });

    const borderOuter = computed(() => `oklch(0.55 0.20 268 / ${0.08 + cfg.value.ringOpacity * 0.06})`);
    const borderInner = computed(() => `oklch(0.20 0.02 265 / ${0.05 + cfg.value.ringOpacity * 0.05})`);

    const boxStyle = computed(() =>
        props.fill ? { width: '100%', height: '100%' } : { width: `${props.size}px`, height: `${props.size}px` },
    );

    const coreStaticStyle = computed(() => ({
        width: '70%',
        height: '70%',
        background:
            'radial-gradient(circle at 32% 26%, oklch(0.72 0.10 268) 0%, oklch(0.42 0.14 268) 48%, oklch(0.22 0.08 268) 100%)',
        boxShadow:
            'inset 0 -32px 60px oklch(0.10 0.05 268 / 0.55), inset 0 22px 40px oklch(1 0 0 / 0.18), 0 18px 40px -10px oklch(0.42 0.14 268 / 0.35)',
    }));

    const coreAnimate = computed(() => ({
        scale: [1, cfg.value.mid, 1] as [number, number, number],
    }));

    const coreTransition = computed(() => ({
        duration: cfg.value.duration,
        repeat: Infinity,
        ease: 'easeInOut' as const,
    }));
</script>

<template>
    <div :class="cn('relative flex items-center justify-center', fill ? 'min-h-0 flex-1' : '', className)" :style="boxStyle">
        <!-- Outward ripples (hero) -->
        <div v-if="heroRipples" class="pointer-events-none absolute inset-0 z-0 flex items-center justify-center" aria-hidden="true">
            <Motion
                v-for="idx in rippleIndices"
                :key="idx"
                class="absolute rounded-full border-2 border-brand/30 shadow-[0_0_24px_oklch(0.55_0.16_268/0.18)]"
                :animate="rippleAnimate"
                :transition="rippleTransition(idx)"
                :style="{ inset: '4%', transformOrigin: 'center center', willChange: 'transform, opacity' }"
            />
        </div>

        <div
            class="absolute z-1 rounded-full"
            :style="{
                inset: '-6%',
                background: 'radial-gradient(circle, oklch(0.55 0.20 268 / 0.10), transparent 65%)',
                filter: 'blur(28px)',
                opacity: cfg.ringOpacity,
            }"
        />

        <div
            class="absolute inset-0 z-1 rounded-full border"
            :style="{ borderColor: borderOuter }"
        />

        <div
            class="absolute z-1 rounded-full border"
            :style="{ inset: '9%', borderColor: borderInner }"
        />

        <div
            class="absolute z-1 rounded-full"
            :style="{
                width: '82%',
                height: '82%',
                top: '12%',
                background: 'radial-gradient(circle, oklch(0.20 0.02 265 / 0.18), transparent 70%)',
                filter: 'blur(34px)',
            }"
        />

        <Motion
            class="relative z-2 rounded-full"
            :style="coreStaticStyle"
            :animate="coreAnimate"
            :transition="coreTransition">
            <div
                class="absolute rounded-full"
                :style="{
                    top: '9%',
                    left: '20%',
                    width: '42%',
                    height: '28%',
                    background: 'radial-gradient(ellipse, oklch(1 0 0 / 0.32), transparent 70%)',
                    filter: 'blur(10px)',
                }"
            />
            <div
                class="absolute left-0 right-0 top-1/2 h-px"
                style="background: oklch(1 0 0 / 0.08)"
            />
        </Motion>
    </div>
</template>
