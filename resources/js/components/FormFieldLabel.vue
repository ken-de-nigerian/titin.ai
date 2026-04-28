<script setup lang="ts">
    import { computed } from 'vue';
    import FormFieldTooltip from '@/components/FormFieldTooltip.vue';

    const props = withDefaults(
        defineProps<{
            /** Passed to the text label's `for` when the control exposes a matching `id`. */
            fieldId?: string;
            /** Help text shown in the standard field info tooltip (never nested inside `<label>`). */
            tooltip?: string;
            size?: 'default' | 'sm';
        }>(),
        {
            fieldId: undefined,
            tooltip: undefined,
            size: 'default',
        },
    );

    const textClass = computed(() =>
        [
            'text-xs',
            'font-medium',
            'text-foreground',
            props.size === 'sm' ? 'text-xs' : null,
        ].filter(Boolean),
    );

    const rootTag = computed(() => (props.fieldId ? 'label' : 'div'));
</script>

<template>
    <div class="flex flex-wrap items-center gap-1">
        <component :is="rootTag" :class="textClass" :for="fieldId">
            <slot />
        </component>
        <FormFieldTooltip v-if="tooltip" :text="tooltip" />
    </div>
</template>
