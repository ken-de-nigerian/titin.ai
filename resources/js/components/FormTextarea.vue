<script setup lang="ts">
    import { computed } from 'vue';
    import FormFieldLabel from '@/components/FormFieldLabel.vue';
    import InputError from '@/components/InputError.vue';

    function messageFromError(error: string | string[] | undefined | null): string {
        if (!error) {
            return '';
        }

        const value = Array.isArray(error) ? error[0] : error;

        return (value ?? '').toString().trim();
    }

    const props = withDefaults(
        defineProps<{
            modelValue: string;
            id: string;
            label: string;
            rows?: number;
            placeholder?: string;
            autocomplete?: string;
            disabled?: boolean;
            maxlength?: number;
            tooltip?: string;
            error?: string | string[];
            invalid?: boolean;
            showCharacterCount?: boolean;
            /** Extra classes on the textarea (e.g. min height). */
            textareaClass?: string;
            /** Extra classes on the outer `form-item` wrapper. */
            itemClass?: string;
        }>(),
        {
            rows: 4,
            autocomplete: 'off',
            disabled: false,
            showCharacterCount: false,
            textareaClass: '',
            itemClass: '',
        },
    );

    const emit = defineEmits<{
        'update:modelValue': [value: string];
        focus: [];
    }>();

    const displayError = computed(() => messageFromError(props.error));

    const isInvalid = computed(() => {
        if (props.invalid) {
            return true;
        }

        return displayError.value !== '';
    });

    const onInput = (event: Event) => {
        emit('update:modelValue', (event.target as HTMLTextAreaElement).value);
    };

    const onFocus = () => {
        emit('focus');
    };
</script>

<template>
    <div :class="['form-item', 'vertical', itemClass || undefined]">
        <FormFieldLabel :field-id="id" :tooltip="tooltip">{{ label }}</FormFieldLabel>
        <textarea
            :id="id"
            :value="modelValue"
            :rows="rows"
            :maxlength="maxlength"
            :disabled="disabled"
            :placeholder="placeholder"
            :autocomplete="autocomplete"
            :class="['input', 'input-md', 'w-full', 'resize-y', 'input-focus', 'input-textarea', isInvalid ? 'input-invalid' : undefined, textareaClass || undefined]"
            :aria-invalid="isInvalid ? 'true' : undefined"
            @input="onInput"
            @focus="onFocus"
        />
        <InputError :message="displayError" class="mt-1" />
        <div v-if="showCharacterCount && maxlength != null" class="mt-1 text-xs text-gray-500 dark:text-gray-400">
            {{ modelValue.length }}/{{ maxlength }} characters
        </div>
        <slot name="after" />
    </div>
</template>
