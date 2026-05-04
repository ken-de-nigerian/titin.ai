<script setup lang="ts">
    import emblaCarouselVue from 'embla-carousel-vue';
    import { ArrowLeft, ArrowRight } from 'lucide-vue-next';
    import { computed, ref, watch } from 'vue';

    const props = defineProps<{
        showArrows?: boolean;
        slideClass?: string;
    }>();

    const showArrows = computed(() => props.showArrows ?? true);

    const [emblaRef, emblaApi] = emblaCarouselVue({
        align: 'start',
        dragFree: false,
        loop: false,
    });
    const selected = ref(0);
    const count = ref(0);

    watch(
        emblaApi,
        (api, _prev, onCleanup) => {
            if (! api) {
                return;
            }

            const onSelect = () => {
                selected.value = api.selectedScrollSnap();
            };

            const onReInit = () => {
                count.value = api.scrollSnapList().length;
                onSelect();
            };

            count.value = api.scrollSnapList().length;
            onSelect();
            api.on('select', onSelect);
            api.on('reInit', onReInit);

            onCleanup(() => {
                api.off('select', onSelect);
                api.off('reInit', onReInit);
            });

            requestAnimationFrame(() => {
                api.reInit();
            });
        },
        { immediate: true },
    );

    const scrollTo = (i: number) => emblaApi.value?.scrollTo(i);
    const prev = () => emblaApi.value?.scrollPrev();
    const next = () => emblaApi.value?.scrollNext();
</script>

<template>
    <div class="relative">
        <div
            ref="emblaRef"
            class="min-w-0 w-full overflow-hidden"
        >
            <div class="flex pl-1 pr-1">
                <slot />
            </div>
        </div>

        <div
            class="mt-8 flex items-center justify-between border-t pt-5"
            style="border-color: var(--hairline)"
        >
            <div
                class="flex items-center gap-3 text-xs tabular-nums"
                style="color: var(--muted-foreground)"
            >
                <span style="color: var(--foreground)">{{ String(selected + 1).padStart(2, '0') }}</span>
                <span>/</span>
                <span>{{ String(count).padStart(2, '0') }}</span>
            </div>
            <div class="flex gap-1">
                <button
                    v-for="i in count"
                    :key="i"
                    :aria-label="`Go to slide ${i}`"
                    class="transition-all"
                    :style="{
                        height: '2px',
                        width: i - 1 === selected ? '2rem' : '1rem',
                        background: i - 1 === selected ? 'var(--foreground)' : 'var(--hairline)',
                    }"
                    @click="scrollTo(i - 1)"
                />
            </div>
            <div
                v-if="showArrows"
                class="flex gap-1"
            >
                <button
                    class="grid h-9 w-9 place-items-center rounded-full border transition hover:opacity-80"
                    style="border-color: var(--hairline)"
                    aria-label="Previous"
                    @click="prev"
                >
                    <ArrowLeft class="h-4 w-4" />
                </button>
                <button
                    class="grid h-9 w-9 place-items-center rounded-full border transition hover:opacity-80"
                    style="border-color: var(--hairline)"
                    aria-label="Next"
                    @click="next"
                >
                    <ArrowRight class="h-4 w-4" />
                </button>
            </div>
        </div>
    </div>
</template>
