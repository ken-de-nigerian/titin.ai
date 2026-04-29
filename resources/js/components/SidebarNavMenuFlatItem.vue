<script setup lang="ts">
    import TextLink from "@/components/TextLink.vue";
    import { useSidebarNavActive  } from '@/composables/useSidebarNavActive';
    import type {UseSidebarNavActiveReturn} from '@/composables/useSidebarNavActive';

    const props = defineProps<{
        href: string;
        activeMatchHrefs?: string[];
    }>();

    const sidebarNav: UseSidebarNavActiveReturn = useSidebarNavActive();

    function navIsActive(): boolean {
        const hrefs = [props.href, ...(props.activeMatchHrefs ?? [])];

        return sidebarNav.isNavActiveAny(hrefs);
    }
</script>

<template>
    <TextLink class="flex items-start gap-4 rounded-xl px-4 py-3.5 text-base transition bg-surface-2 font-medium text-foreground" :class="{ 'active shadow-sm border': navIsActive() }" :href="props.href" preserve-scroll>
        <slot />
    </TextLink>
</template>
