<script setup lang="ts">
    defineProps<{
        title: string;
        items: string[];
        tone: 'success' | 'brand';
    }>();
</script>

<template>
    <div class="surface rounded-2xl p-6 shadow-xs">
        <h3 class="text-base font-semibold tracking-tight">{{ title }}</h3>
        <ul class="mt-5 space-y-3">
            <li
                v-for="s in items"
                :key="s"
                class="flex items-start gap-3 text-sm">
                <div
                    :class="[
                        'mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full',
                        tone === 'success' ? 'bg-success' : 'bg-brand',
                    ]"
                />
                <span class="leading-relaxed">{{ s }}</span>
            </li>
        </ul>
    </div>
</template>
