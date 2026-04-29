<script setup lang="ts">
    import { usePage } from '@inertiajs/vue3';
    import { computed } from 'vue';
    import TextLink from '@/components/TextLink.vue';
    import { useRoute } from '@/composables/useRoute';

    const props = withDefaults(defineProps<{
        dark?: boolean;
    }>(), {
        dark: false,
    });

    const page = usePage();
    const route = useRoute();
    const siteName = computed(() => page.props.name);
    const logoSource = computed(() => props.dark ? '/logos/logo-light.png' : '/logos/logo-dark.png');
</script>

<template>
    <TextLink :href="route('home')" class="inline-flex items-center">
        <img class="h-8 w-auto max-w-50 object-contain sm:h-9" :src="logoSource" :alt="siteName" />
    </TextLink>
</template>
