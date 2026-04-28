<script setup lang="ts">
    defineProps<{
        role: string;
        text: string;
        muted?: boolean;
    }>();
</script>

<template>
    <div :class="[ 'rounded-xl border border-hairline p-3', muted ? 'bg-surface-2/60' : 'bg-surface',]">
        <p :class="[ 'text-[10px] font-medium uppercase tracking-wider', muted ? 'text-muted-foreground' : 'text-brand',]">
            {{ role }}
        </p>
        <p class="mt-1.5 text-sm leading-snug">{{ text }}</p>
    </div>
</template>
