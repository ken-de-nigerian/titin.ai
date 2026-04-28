<script setup lang="ts">
    import { Check } from 'lucide-vue-next';
    import { ref } from 'vue';

    const props = defineProps<{
        value: number;
    }>();

    const r = 28;
    const c = 2 * Math.PI * r;
    const pct = Math.min(props.value / 10, 1);
    const animated = ref(false);

    setTimeout(() => {
        animated.value = true;
    }, 100);
</script>

<template>
    <div class="relative h-20 w-20 shrink-0">
        <svg
            viewBox="0 0 72 72"
            class="h-20 w-20 -rotate-90"
        >
            <circle
                :cx="36"
                :cy="36"
                :r="r"
                stroke="currentColor"
                class="text-surface-2"
                stroke-width="6"
                fill="none"
            />
            <circle
                :cx="36"
                :cy="36"
                :r="r"
                stroke="currentColor"
                class="text-brand"
                stroke-width="6"
                fill="none"
                stroke-linecap="round"
                :stroke-dasharray="c"
                :stroke-dashoffset="animated ? c * (1 - pct) : c"
                style="transition: stroke-dashoffset 1s ease-out"
            />
        </svg>
        <div class="absolute inset-0 grid place-items-center">
            <Check class="h-5 w-5 text-brand" />
        </div>
    </div>
</template>
