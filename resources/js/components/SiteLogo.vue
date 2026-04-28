<script setup lang="ts">
    import { usePage } from '@inertiajs/vue3';
    import { computed } from 'vue';
    import TextLink from '@/components/TextLink.vue';
    import { useRoute } from '@/composables/useRoute';

    withDefaults(defineProps<{
        dark?: boolean;
    }>(), {
        dark: false,
    });

    const page = usePage();
    const route = useRoute();
    const siteName = computed(() => page.props.name);
</script>

<template>
    <TextLink :href="route('home')" class="flex items-center gap-2.5">
        <div
            class="grid h-7 w-7 place-items-center rounded-lg transition"
            :class="dark ? 'bg-[oklch(0.97_0.005_265)]' : 'bg-foreground'"
        >
            <svg
                viewBox="0 0 16 16"
                class="h-3.5 w-3.5"
                fill="none"
                stroke-width="2"
                :class="dark ? 'stroke-[oklch(0.13_0.025_265)]' : 'stroke-background'"
            >
                <circle cx="8" cy="8" r="3"/>
                <circle cx="8" cy="8" r="6.5" opacity="0.5"/>
            </svg>
        </div>
        <span
            class="text-[15px] font-semibold tracking-tight transition"
            :class="dark ? 'text-[oklch(0.97_0.005_265)]' : 'text-foreground'"
        >
            {{ siteName }}
        </span>
    </TextLink>
</template>
