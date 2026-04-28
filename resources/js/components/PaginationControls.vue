<script setup lang="ts">
    import PaginatorBar from '@/components/PaginatorBar.vue';
    import type { PaginatorBarLink } from '@/components/PaginatorBar.vue';

    const props = withDefaults(
        defineProps<{
            links: PaginatorBarLink[];
            from: number;
            to: number;
            total: number;
            preserveScroll?: boolean;
        }>(),
        { preserveScroll: true },
    );

    const emit = defineEmits<{
        goToPage: [url: string];
    }>();

    function onNavigate(url: string): void {
        emit('goToPage', url);
    }
</script>

<template>
    <div class="flex flex-col sm:flex-row sm:justify-between items-center pt-8 pb-4 space-y-4 sm:space-y-0">
        <div class="text-sm text-muted-foreground hidden sm:block">
            Showing <span class="font-semibold text-foreground">{{ props.from }}</span> to <span class="font-semibold text-foreground">{{ props.to }}</span> of <span class="font-semibold text-foreground">{{ props.total }}</span> results
        </div>

        <div class="flex w-full justify-center sm:w-auto sm:justify-end">
            <PaginatorBar
                :links="props.links"
                navigation="emit"
                :preserve-scroll="props.preserveScroll"
                @navigate="onNavigate"
            />
        </div>
    </div>
</template>
