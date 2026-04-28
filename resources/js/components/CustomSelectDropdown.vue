<script setup lang="ts">
    import { ChevronDown, Search } from 'lucide-vue-next';
    import { computed, ref, watch, onMounted, onBeforeUnmount } from 'vue';
    import InputError from '@/components/InputError.vue';

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
            options: SelectOption[];
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

    const showDropdown = ref(false);
    const searchQuery = ref('');
    const dropdownRef = ref<HTMLElement | null>(null);

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

    const filteredOptions = computed(() => {
        const query = searchQuery.value.toLowerCase();

        return props.options.filter((option) => option.label.toLowerCase().includes(query));
    });

    const triggerClass = computed(() =>
        [
            'input',
            'input-md',
            'h-9',
            'block',
            'w-full',
            'shadow-sm',
            'border',
            'input-focus',
            'flex',
            'items-center',
            'justify-between',
            'gap-2',
            'px-3',
            'text-left',
            props.disabled ? 'cursor-not-allowed opacity-60' : 'cursor-pointer',
            'rounded-md',
            isInvalid.value ? 'input-invalid' : undefined,
        ].filter(Boolean),
    );

    const selectOption = (option: SelectOption) => {
        const valueToEmit = String(option.value).trim();
        emit('update:modelValue', valueToEmit);
        showDropdown.value = false;
        emit('user-interacted');
    };

    function toggleTrigger(): void {
        if (props.disabled) {
            return;
        }

        showDropdown.value = !showDropdown.value;
        emit('user-interacted');
    }

    const handleClickOutside = (event: MouseEvent) => {
        if (dropdownRef.value && !dropdownRef.value.contains(event.target as Node)) {
            showDropdown.value = false;
        }
    };

    onMounted(() => {
        document.addEventListener('click', handleClickOutside);
    });

    onBeforeUnmount(() => {
        document.removeEventListener('click', handleClickOutside);
    });

    watch(
        () => showDropdown.value,
        (isOpened) => {
            if (isOpened) {
                searchQuery.value = '';
            }
        },
    );
</script>

<template>
    <div class="relative w-full" ref="dropdownRef">
        <button
            :id="id"
            type="button"
            :class="triggerClass"
            :disabled="disabled"
            :aria-expanded="showDropdown"
            :aria-haspopup="true"
            :aria-invalid="isInvalid ? 'true' : undefined"
            @click="toggleTrigger"
        >
            <span
                :class="[
                    'min-w-0 flex-1 truncate text-sm',
                    selectedOption ? 'text-gray-900 dark:text-gray-100' : 'text-gray-500 dark:text-gray-400',
                ]"
            >
                <slot name="default" :selectedOption="selectedOption" :selectedLabel="selectedLabel">
                    {{ selectedLabel }}
                </slot>
            </span>
            <ChevronDown
                class="size-4 shrink-0 text-gray-500 transition-transform dark:text-gray-400"
                :class="{ 'rotate-180': showDropdown }"
                aria-hidden="true"
            />
        </button>

        <div
            v-if="showDropdown"
            class="absolute left-0 right-0 top-full z-50 mt-1 max-h-80 overflow-hidden rounded-lg border border-gray-200 bg-white shadow-xl dark:border-gray-700 dark:bg-gray-900"
        >
            <div class="border-b border-gray-200 p-3 dark:border-gray-700">
                <div class="relative">
                    <Search class="absolute left-3 top-1/2 size-4 -translate-y-1/2 text-gray-500 dark:text-gray-400" />
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Filter options..."
                        class="input input-md h-9 w-full rounded-md border border-gray-200 py-2 pl-10 pr-4 text-sm shadow-sm input-focus dark:border-gray-600"
                    />
                </div>
            </div>

            <div class="max-h-64 overflow-y-auto">
                <div v-if="filteredOptions.length === 0" class="p-4 text-center text-sm text-gray-500 dark:text-gray-400">
                    No matching options found
                </div>
                <button
                    v-for="option in filteredOptions"
                    :key="String(option.value)"
                    type="button"
                    class="flex w-full cursor-pointer items-center justify-between p-3 text-left text-sm font-medium text-gray-900 transition-colors hover:bg-gray-100 dark:text-gray-100 dark:hover:bg-gray-800"
                    @click="selectOption(option)"
                >
                    <slot name="option" :option="option">
                        <span>{{ option.label }}</span>
                    </slot>
                </button>
            </div>
        </div>

        <InputError :message="displayError" />
    </div>
</template>
