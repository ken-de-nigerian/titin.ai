<script setup lang="ts">
    import { useWindowSize } from '@vueuse/core';
    import { Mic } from 'lucide-vue-next';
    import { computed } from 'vue';

    import Bubble from '@/components/Bubble.vue';
    import Orb from '@/components/Orb.vue';

    const { width } = useWindowSize();
    const mockOrbSize = computed(() => (width.value < 480 ? Math.min(160, width.value - 56) : 180));
</script>

<template>
    <div class="overflow-hidden rounded-2xl border border-hairline bg-surface shadow-md">
        <div class="flex items-center justify-between border-b border-hairline bg-surface-2/40 px-5 py-3">
            <div class="flex items-center gap-2 text-xs">
                <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-success" />
                <span class="font-medium">Senior PM · Behavioral</span>
            </div>
            <span class="text-xs tabular-nums text-muted-foreground">04:21</span>
        </div>

        <div class="grid gap-4 p-6 md:grid-cols-12">
            <div class="flex items-center justify-center rounded-xl bg-surface-2/60 py-8 md:col-span-5">
                <Orb
                    state="listening"
                    :size="mockOrbSize"
                />
            </div>
            <div class="space-y-3 md:col-span-7">
                <Bubble
                    role="Interviewer"
                    text="Walk me through a project you led that didn't go as planned."
                />
                <Bubble
                    role="You"
                    text="I was leading a 6-week launch for our checkout redesign. Two weeks in, our analytics provider went down…"
                    muted
                />
                <div class="flex items-center gap-2 rounded-xl border border-hairline bg-surface-2 px-3 py-2 text-xs">
                    <Mic class="h-3.5 w-3.5 text-brand" />
                    <div class="flex flex-1 items-end gap-0.5">
                        <span
                            v-for="(h, i) in [3, 5, 7, 4, 9, 6, 8, 5, 7, 3, 6, 8]"
                            :key="i"
                            class="w-0.5 rounded-full bg-brand"
                            :style="{ height: `${h * 2}px` }"
                        />
                    </div>
                    <span class="tabular-nums text-muted-foreground">0:18</span>
                </div>
            </div>
        </div>
    </div>
</template>
