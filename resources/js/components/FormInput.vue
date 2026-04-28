<script setup lang="ts">
    import { computed, ref, useSlots } from 'vue';
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
            type?: string;
            placeholder?: string;
            autocomplete?: string;
            disabled?: boolean;
            readonly?: boolean;
            autofocus?: boolean;
            helperText?: string;
            /** Optional help shown in the standard field tooltip beside the label. */
            tooltip?: string;
            error?: string | string[];
            invalid?: boolean;
            required?: boolean;
            step?: string;
            min?: string;
            inputmode?: 'none' | 'text' | 'tel' | 'url' | 'email' | 'numeric' | 'decimal' | 'search';
            /** When false, only the control (and errors) render — use with an external `FormFieldLabel`. */
            showFieldLabel?: boolean;
        }>(),
        {
            type: 'text',
            required: false,
            readonly: false,
            showFieldLabel: true,
        },
    );

    const emit = defineEmits<{
        'update:modelValue': [value: string];
        focus: [];
    }>();

    const inputRef = ref<HTMLInputElement | null>(null);
    const slots = useSlots();

    const displayError = computed(() => messageFromError(props.error));

    const isInvalid = computed(() => {
        if (props.invalid) {
            return true;
        }

        return displayError.value !== '';
    });

    defineExpose({
        focus: () => inputRef.value?.focus(),
    });

    const onInput = (event: Event) => {
        emit('update:modelValue', (event.target as HTMLInputElement).value);
    };

    const onFocus = () => {
        emit('focus');
    };
</script>

<template>
    <div>
        <div v-if="showFieldLabel" class="flex items-center justify-between">
            <FormFieldLabel :field-id="id" :tooltip="tooltip">
                {{ label }}<span v-if="required" class="text-destructive" aria-hidden="true"> *</span>
            </FormFieldLabel>
        </div>
        <input
            :id="id"
            ref="inputRef"
            :value="modelValue"
            :class="[
                'mt-1.5 w-full rounded-lg border border-hairline bg-surface px-3.5 py-2.5 text-sm shadow-xs outline-none placeholder:text-muted-foreground/60 focus:border-brand focus:ring-2 focus:ring-ring',
                isInvalid ? 'border-destructive focus:border-destructive focus:ring-destructive/20' : undefined,
            ]"
            :type="type"
            :autocomplete="autocomplete"
            :placeholder="placeholder"
            :disabled="disabled"
            :readonly="readonly"
            :autofocus="autofocus"
            :required="required"
            :step="step"
            :min="min"
            :inputmode="inputmode"
            :aria-required="required ? 'true' : undefined"
            :aria-invalid="isInvalid ? 'true' : undefined"
            @input="onInput"
            @focus="onFocus"
        />
        <p v-if="slots.helper" class="mt-1.5 text-xs text-gray-500 dark:text-gray-400">
            <slot name="helper" />
        </p>
        <p v-else-if="helperText" class="mt-1.5 text-xs text-gray-500 dark:text-gray-400">
            {{ helperText }}
        </p>
        <InputError :message="displayError" />
    </div>
</template>
