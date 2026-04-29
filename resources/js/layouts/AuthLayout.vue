<script setup lang="ts">
    import FlashMessages from "@/components/utilities/FlashMessages.vue";
</script>

<template>
    <div class="grid min-h-screen md:grid-cols-2">
        <!-- Left — form -->
        <slot name="form" />

        <!-- Right — marketing aside -->
        <slot name="sidebar" />
    </div>

    <FlashMessages />
</template>
