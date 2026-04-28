<script setup lang="ts">
    defineProps<{
        message?: string;
    }>();
</script>

<template>
    <p v-show="message" class="mt-1.5 text-xs font-medium text-destructive">
        {{ message }}
    </p>
</template>
