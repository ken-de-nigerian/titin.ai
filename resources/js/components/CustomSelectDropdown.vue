<script setup lang="ts">
    import { Check, ChevronDown } from 'lucide-vue-next';
    import { computed } from 'vue';
    import InputError from '@/components/InputError.vue';
    import UiDropdown from '@/components/ui/UiDropdown.vue';

    function messageFromError(error: string | string[] | undefined | null): string {
        if (!error) {
            return '';
        }

        const value = Array.isArray(error) ? error[0] : error;

        return (value ?? '').toString().trim();
    }

    interface SelectOption {
        value: string;
        label: string;
        [key: string]: unknown;
    }

    const props = withDefaults(
        defineProps<{
            modelValue: string | null | undefined;
            options: readonly SelectOption[];
            placeholder?: string;
            id?: string;
            error?: string | string[];
            invalid?: boolean;
            disabled?: boolean;
        }>(),
        {
            placeholder: undefined,
            id: undefined,
            error: undefined,
            invalid: undefined,
            disabled: false,
        },
    );

    const emit = defineEmits<{
        'update:modelValue': [value: string];
        'user-interacted': [];
    }>();

    const displayError = computed(() => messageFromError(props.error));

    const isInvalid = computed(() => {
        if (props.invalid) {
            return true;
        }

        return displayError.value !== '';
    });

    const selectedOption = computed(() => {
        if (!props.modelValue && props.modelValue !== '0') {
            return null;
        }

        const modelValue = String(props.modelValue).trim();
        const found = props.options.find((opt) => {
            const optValue = String(opt.value).trim();

            return optValue === modelValue;
        });

        return found || null;
    });

    const selectedLabel = computed(() => {
        return selectedOption.value?.label || props.placeholder || 'Select an option';
    });

    const triggerClass = computed(() =>
        [
            'h-9',
            'w-full',
            'flex',
            'items-center',
            'justify-between',
            'gap-2',
            'px-3',
            'text-left',
            props.disabled ? 'cursor-not-allowed opacity-60' : 'cursor-pointer',
            'rounded-lg',
            'border',
            'border-input',
            'bg-background',
            'text-sm',
            'text-foreground',
            'shadow-xs',
            'outline-none',
            'transition',
            'hover:bg-surface-2/40',
            'focus-visible:border-ring',
            'focus-visible:ring-2',
            'focus-visible:ring-ring/20',
            isInvalid.value ? 'border-destructive ring-2 ring-destructive/20' : undefined,
        ].filter(Boolean),
    );

    const menuClass = computed(() =>
        [
            'z-50',
            'z-[220]',
            'max-h-[min(320px,50vh)]',
            'min-w-32',
            'overflow-x-hidden',
            'overflow-y-auto',
            'rounded-xl',
            'bg-popover',
            'text-popover-foreground',
            'p-1',
            'ring-1',
            'ring-foreground/10',
            'shadow-sm',
            'outline-none',
            'border'
        ].join(' '),
    );

    const selectOption = (option: SelectOption, close: () => void) => {
        const valueToEmit = String(option.value).trim();
        emit('update:modelValue', valueToEmit);
        close();
        emit('user-interacted');
    };
</script>

<template>
    <div class="w-full">
        <UiDropdown
            align="stretch"
            :teleport-to-body="true"
            :menu-class="menuClass"
        >
            <template #trigger="{ open, toggle }">
                <button
                    :id="id"
                    type="button"
                    :class="triggerClass"
                    :disabled="disabled"
                    :aria-expanded="open"
                    aria-label="Select an option"
                    role="combobox"
                    :aria-invalid="isInvalid ? 'true' : undefined"
                    @click.stop="!disabled && toggle()"
                >
                    <span class="min-w-0 flex-1 truncate text-sm" :class="selectedOption ? 'text-foreground' : 'text-muted-foreground'">
                        <slot name="default" :selected-option="selectedOption" :selected-label="selectedLabel">
                            {{ selectedLabel }}
                        </slot>
                    </span>
                    <ChevronDown class="size-4 shrink-0 text-muted-foreground transition-transform duration-200" :class="open ? 'rotate-180' : ''" aria-hidden="true" />
                </button>
            </template>

            <template #menu="{ close }">
                <ul class="m-0 list-none p-0" role="presentation">
                    <li
                        v-for="option in options"
                        :key="String(option.value)"
                        role="menuitem"
                        tabindex="-1"
                        class="group/dropdown-menu-item relative flex cursor-default items-center gap-1.5 rounded-md px-1.5 py-1 text-sm outline-hidden select-none transition-colors focus:bg-accent focus:text-accent-foreground hover:bg-accent hover:text-accent-foreground"
                        :class="String(option.value).trim() === String(modelValue ?? '').trim() ? 'bg-accent font-medium text-accent-foreground' : ''"
                        :aria-selected="String(option.value).trim() === String(modelValue ?? '').trim()"
                        @click="selectOption(option, close)">
                        <slot name="option" :option="option">
                            <span>{{ option.label }}</span>
                        </slot>
                        <Check
                            v-if="String(option.value).trim() === String(modelValue ?? '').trim()"
                            class="ml-auto size-4 text-current"
                            aria-hidden="true"
                        />
                    </li>
                </ul>
            </template>
        </UiDropdown>
        <InputError :message="displayError" />
    </div>
</template>
