<script setup lang="ts">
    import { computed } from 'vue';

    export type OrbState = 'idle' | 'listening' | 'thinking' | 'speaking';

    const props = defineProps<{
        state?: OrbState;
        size?: number;
        className?: string;
    }>();

    const state = computed(() => props.state ?? 'idle');
    const size = computed(() => props.size ?? 320);

    const stateConfig: Record<
        OrbState,
        { scale: [number, number, number]; duration: number; ringOpacity: number }
    > = {
        idle: { scale: [1, 1.012, 1], duration: 6.0, ringOpacity: 0.5 },
        listening: { scale: [1, 1.04, 1], duration: 1.8, ringOpacity: 0.9 },
        thinking: { scale: [1, 1.008, 1], duration: 3.2, ringOpacity: 0.45 },
        speaking: { scale: [1, 1.025, 1], duration: 1.1, ringOpacity: 1.0 },
    };

    const cfg = computed(() => stateConfig[state.value]);

    const outerHaloStyle = computed(() => ({
        inset: '-6%',
        background: 'radial-gradient(circle, oklch(0.55 0.20 268 / 0.10), transparent 65%)',
        filter: 'blur(28px)',
        opacity: cfg.value.ringOpacity,
    }));

    const outerRingStyle = computed(() => ({
        borderColor: `oklch(0.55 0.20 268 / ${0.08 + cfg.value.ringOpacity * 0.06})`,
    }));

    const innerRingStyle = computed(() => ({
        inset: '9%',
        borderColor: `oklch(0.20 0.02 265 / ${0.05 + cfg.value.ringOpacity * 0.05})`,
    }));

    const ambientShadowStyle = computed(() => ({
        width: '82%',
        height: '82%',
        top: '12%',
        background: 'radial-gradient(circle, oklch(0.20 0.02 265 / 0.18), transparent 70%)',
        filter: 'blur(34px)',
    }));

    const coreStyle = computed(() => ({
        width: '70%',
        height: '70%',
        background:
            'radial-gradient(circle at 32% 26%, oklch(0.72 0.10 268) 0%, oklch(0.42 0.14 268) 48%, oklch(0.22 0.08 268) 100%)',
        boxShadow:
            'inset 0 -32px 60px oklch(0.10 0.05 268 / 0.55), inset 0 22px 40px oklch(1 0 0 / 0.18), 0 18px 40px -10px oklch(0.42 0.14 268 / 0.35)',
        '--scale-start': cfg.value.scale[0],
        '--scale-mid': cfg.value.scale[1],
        '--scale-end': cfg.value.scale[2],
        '--duration': `${cfg.value.duration}s`,
    }));

    const highlightStyle = computed(() => ({
        top: '9%',
        left: '20%',
        width: '42%',
        height: '28%',
        background: 'radial-gradient(ellipse, oklch(1 0 0 / 0.32), transparent 70%)',
        filter: 'blur(10px)',
    }));

    const equatorStyle = computed(() => ({
        background: 'oklch(1 0 0 / 0.08)',
    }));
</script>

<template>
    <div
        :class="['relative flex items-center justify-center', className]"
        :style="{ width: `${size}px`, height: `${size}px` }"
    >
        <!-- outer soft halo -->
        <div
            class="absolute rounded-full"
            :style="outerHaloStyle"
        />

        <!-- concentric rings -->
        <div
            class="absolute inset-0 rounded-full border"
            :style="outerRingStyle"
        />
        <div
            class="absolute rounded-full border"
            :style="innerRingStyle"
        />

        <!-- ambient shadow -->
        <div
            class="absolute rounded-full"
            :style="ambientShadowStyle"
        />

        <!-- core sphere -->
        <div
            :class="['orb-core relative rounded-full', `orb-core--${state}`]"
            :style="coreStyle"
        >
            <!-- highlight -->
            <div
                class="absolute rounded-full"
                :style="highlightStyle"
            />
            <!-- equator -->
            <div
                class="absolute left-0 right-0 top-1/2 h-px"
                :style="equatorStyle"
            />
        </div>
    </div>
</template>

<style scoped>
    .orb-core {
        transform-origin: center;
    }

    .orb-core--idle {
        animation: orb-breathe var(--duration) ease-in-out infinite;
    }
    .orb-core--listening {
        animation: orb-breathe var(--duration) ease-in-out infinite;
    }
    .orb-core--thinking {
        animation: orb-breathe var(--duration) ease-in-out infinite;
    }
    .orb-core--speaking {
        animation: orb-breathe var(--duration) ease-in-out infinite;
    }

    @keyframes orb-breathe {
        0%,
        100% {
            transform: scale(var(--scale-start, 1));
        }
        50% {
            transform: scale(var(--scale-mid, 1.012));
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .orb-core {
            animation: none !important;
        }
    }
</style>
