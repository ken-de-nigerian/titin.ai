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
    const isPasswordVisible = ref(false);
    const slots = useSlots();

    const displayError = computed(() => messageFromError(props.error));

    const isInvalid = computed(() => {
        if (props.invalid) {
            return true;
        }

        return displayError.value !== '';
    });

    const isPasswordField = computed(() => props.type === 'password');
    const currentInputType = computed(() => {
        if (!isPasswordField.value) {
            return props.type;
        }

        return isPasswordVisible.value ? 'text' : 'password';
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

    const togglePasswordVisibility = () => {
        isPasswordVisible.value = !isPasswordVisible.value;
    };
</script>

<template>
    <div>
        <div v-if="showFieldLabel" class="flex items-center justify-between">
            <FormFieldLabel :field-id="id" :tooltip="tooltip">
                {{ label }}<span v-if="required" class="text-destructive" aria-hidden="true"> *</span>
            </FormFieldLabel>
        </div>
        <div class="relative">
            <input
                :id="id"
                ref="inputRef"
                :value="modelValue"
                :class="[
                    'mt-1.5 w-full rounded-lg border border-hairline bg-surface px-3.5 py-2.5 text-sm shadow-xs outline-none placeholder:text-muted-foreground/60 focus:border-brand focus:ring-2 focus:ring-ring',
                    isPasswordField ? 'pr-11' : undefined,
                    isInvalid ? 'border-destructive focus:border-destructive focus:ring-destructive/20' : undefined,
                ]"
                :type="currentInputType"
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
            <button
                v-if="isPasswordField"
                type="button"
                class="absolute right-3 top-1/2 mt-[3px] -translate-y-1/2 text-muted-foreground transition-colors hover:text-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                :aria-label="isPasswordVisible ? 'Hide password' : 'Show password'"
                @click="togglePasswordVisibility"
            >
                <svg
                    v-if="isPasswordVisible"
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    class="h-4 w-4"
                    aria-hidden="true"
                >
                    <path d="M3 3l18 18" />
                    <path d="M10.58 10.58a2 2 0 002.83 2.83" />
                    <path d="M17.94 17.94A10.94 10.94 0 0112 20c-5 0-9.27-3.11-11-8a11.83 11.83 0 012.39-4.21" />
                    <path d="M9.9 4.24A10.94 10.94 0 0112 4c5 0 9.27 3.11 11 8a11.88 11.88 0 01-1.86 3.19" />
                </svg>
                <svg
                    v-else
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    class="h-4 w-4"
                    aria-hidden="true"
                >
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8S1 12 1 12z" />
                    <circle cx="12" cy="12" r="3" />
                </svg>
            </button>
        </div>
        <p v-if="slots.helper" class="mt-1.5 text-xs text-gray-500 dark:text-gray-400">
            <slot name="helper" />
        </p>
        <p v-else-if="helperText" class="mt-1.5 text-xs text-gray-500 dark:text-gray-400">
            {{ helperText }}
        </p>
        <InputError :message="displayError" />
    </div>
</template>
