<script setup lang="ts">
    import type { DateOption } from 'flatpickr/dist/types/options';
    import 'flatpickr/dist/flatpickr.css';
    import { computed, nextTick, onMounted, ref, useAttrs, watch } from 'vue';
    import FlatPickr from 'vue-flatpickr-component';
    import { useHydrated } from '@/composables/useHydrated';

    const attrs = useAttrs();

    const hydrated = useHydrated();
    const flatPickrRootRef = ref<InstanceType<typeof FlatPickr> | null>(null);

    const props = withDefaults(
        defineProps<{
            modelValue?: string | Date | unknown[] | null;
            placeholder?: string;
            name?: string;
            disabled?: boolean;
            /** When true, applies the same `input-invalid` border as FormInput. */
            invalid?: boolean;
            config?: Record<string, unknown>;
        }>(),
        {
            placeholder: 'Select date',
            disabled: false,
            invalid: false,
            config: () => ({}),
        },
    );

    const emit = defineEmits(['update:modelValue', 'change', 'open', 'close', 'focus']);

    /**
     * Same surface classes as FormInput (visible field is Flatpickr’s alt input).
     * `input-invalid` is not included here: Flatpickr only applies `altInputClass` when the instance
     * is created; `fp.set()` does not reliably update the alt input when `invalid` toggles — we sync
     * that class in `syncInputInvalidClass`.
     */
    const formInputSurfaceClass = computed((): string[] => {
        return [
            'flatpickr-alt-input',
            'input',
            'input-md',
            'h-9',
            'mb-0',
            'block',
            'w-full',
            'shadow-sm',
            'border',
            'input-focus',
        ];
    });

    type FlatpickrInstanceLite = {
        altInput?: HTMLInputElement | null;
        input?: HTMLInputElement | null;
    };

    function arrayifyHook<T>(value: unknown): T[] {
        if (value == null) {
            return [];
        }

        return Array.isArray(value) ? (value as T[]) : [value as T];
    }

    function syncInputInvalidClass(instance?: FlatpickrInstanceLite): void {
        const rootEl = flatPickrRootRef.value?.$el as HTMLElement | undefined;
        const sibling = rootEl?.nextElementSibling;
        const altFromSibling =
            sibling instanceof HTMLInputElement && sibling.classList.contains('flatpickr-alt-input') ? sibling : null;
        const altFromParent = rootEl?.parentElement?.querySelector('input.flatpickr-alt-input');
        const altFromDom =
            altFromSibling ??
            (altFromParent instanceof HTMLInputElement ? altFromParent : undefined) ??
            undefined;
        const alt = instance?.altInput ?? altFromDom;
        const native = instance?.input ?? (rootEl instanceof HTMLInputElement ? rootEl : undefined);

        if (alt instanceof HTMLInputElement) {
            alt.classList.toggle('input-invalid', props.invalid);
        }

        if (native instanceof HTMLInputElement) {
            native.classList.toggle('input-invalid', props.invalid);
        }
    }

    const flatpickrConfig = computed(() => {
        const fromParent = { ...props.config };
        const parentAltClass =
            typeof fromParent.altInputClass === 'string' ? String(fromParent.altInputClass).trim() : '';

        const userOnReady = fromParent.onReady;

        delete fromParent.onReady;
        delete fromParent.altInputClass;

        const onReadyInvalidSync = (_dates: unknown[], _dateStr: string, instance: FlatpickrInstanceLite): void => {
            void nextTick(() => syncInputInvalidClass(instance));
        };

        return {
            altInput: true,
            altFormat: 'F j, Y',
            dateFormat: 'Y-m-d',
            allowInput: true,
            clickOpens: true,
            disableMobile: true,
            ...fromParent,
            onReady: [...arrayifyHook<(dates: unknown[], dateStr: string, instance: FlatpickrInstanceLite) => void>(
                userOnReady,
            ), onReadyInvalidSync],
            altInputClass: [parentAltClass, formInputSurfaceClass.value.join(' ')].filter((s) => s.length > 0).join(' '),
        };
    });

    watch(
        () => props.invalid,
        () => {
            void nextTick(() => syncInputInvalidClass());
        },
    );

    onMounted(() => {
        void nextTick(() => {
            setTimeout(() => syncInputInvalidClass(), 0);
        });
    });

    const displayValue = computed((): DateOption | DateOption[] | null => {
        const v = props.modelValue;

        if (v === undefined || v === null) {
            return null;
        }

        return v as DateOption | DateOption[];
    });

    /** Plain string for the pre-mount placeholder (Flatpickr is client-only and SSR emits no input). */
    function formatModelForFallback(value: typeof props.modelValue): string {
        if (value == null || value === '') {
            return '';
        }

        if (typeof value === 'string') {
            return value;
        }

        return value.toString().slice(0, 10);

    }

    const fallbackInputValue = computed(() => formatModelForFallback(props.modelValue));

    function handleInput(value: unknown): void {
        emit('update:modelValue', value ?? null);
    }

    function handleChange(selectedDates: Date[], dateStr: string, instance: unknown): void {
        const cfg = props.config as { mode?: string } | undefined;
        const value = cfg?.mode === 'range' ? selectedDates : selectedDates[0] || null;
        emit('change', { selectedDates, dateStr, instance, value });
    }

    const handleOpen = (...args: unknown[]) => emit('open', ...args);
    const handleClose = (...args: unknown[]) => emit('close', ...args);
    const handleFocus = (event: FocusEvent) => emit('focus', event);
</script>

<template>
    <flat-pickr
        v-if="hydrated"
        ref="flatPickrRootRef"
        v-bind="attrs"
        :model-value="displayValue"
        @update:model-value="handleInput"
        :config="flatpickrConfig"
        :placeholder="placeholder"
        :name="name"
        :disabled="disabled"
        :class="[
            'input',
            'input-md',
            'h-9',
            'block',
            'w-full',
            'shadow-sm',
            'border',
            'input-focus',
            invalid ? 'input-invalid' : undefined,
        ]"
        @on-change="handleChange"
        @on-open="handleOpen"
        @on-close="handleClose"
        @focus="handleFocus"
    />
    <input
        v-else
        type="text"
        readonly
        autocomplete="off"
        v-bind="attrs"
        :value="fallbackInputValue"
        :placeholder="placeholder"
        :name="name"
        :disabled="disabled"
        :class="[
            'input',
            'input-md',
            'h-9',
            'block',
            'w-full',
            'shadow-sm',
            'border',
            'input-focus',
            invalid ? 'input-invalid' : undefined,
        ]"
        @focus="handleFocus"
    />
</template>

<style>
    /* Alt input: match FormInput surface (parent sets Tailwind input classes). */
    input.flatpickr-alt-input.input {
        background-color: inherit;
    }

    /*
     * `background-color: inherit` above beats `.input.input-invalid` in app.css (lower specificity).
     * Mirror invalid surface from `resources/css/app.css` so error border + subtle tint show on the visible field.
     */
    input.flatpickr-alt-input.input.input-invalid {
        border-color: var(--error);
        background-color: var(--error-subtle);
    }

    input.flatpickr-alt-input.input.input-invalid::placeholder {
        color: var(--error);
    }

    input.flatpickr-alt-input.input.input-invalid:focus-within {
        border-color: var(--error);
        --tw-ring-shadow: var(--tw-ring-inset,) 0 0 0 calc(1px + var(--tw-ring-offset-width)) var(--tw-ring-color, currentcolor);
        box-shadow: var(--tw-inset-shadow), var(--tw-inset-ring-shadow), var(--tw-ring-offset-shadow), var(--tw-ring-shadow), var(--tw-shadow);
        --tw-ring-color: var(--error);
        background-color: #0000;
    }

    input.flatpickr-alt-input.input.input-invalid:focus {
        --tw-ring-shadow: var(--tw-ring-inset,) 0 0 0 calc(1px + var(--tw-ring-offset-width)) var(--tw-ring-color, currentcolor);
        box-shadow: var(--tw-inset-shadow), var(--tw-inset-ring-shadow), var(--tw-ring-offset-shadow), var(--tw-ring-shadow), var(--tw-shadow);
        --tw-ring-color: var(--error);
        background-color: #0000;
    }

    /*
     * Calendar popover — aligned with CustomSelectDropdown panels and app grays
     * (rounded-lg, border-gray-200 / dark:border-gray-700, bg-white / dark:bg-gray-900, shadow-xl).
     */
    .flatpickr-calendar {
        font-family: inherit;
        font-size: 0.875rem;
        line-height: 1.25rem;
        color: #111827;
        border-radius: 0.5rem;
        border: 1px solid #e5e7eb;
        background-color: #ffffff;
        box-shadow:
            0 20px 25px -5px rgb(0 0 0 / 0.1),
            0 8px 10px -6px rgb(0 0 0 / 0.1);
        padding: 0;
        width: 20rem;
        max-width: min(20rem, calc(100vw - 2rem));
        box-sizing: border-box;
        z-index: 100 !important;
    }

    .dark .flatpickr-calendar {
        color: #f3f4f6;
        border-color: #374151;
        background-color: #111827;
        box-shadow: 0 25px 50px -12px rgb(0 0 0 / 0.45);
    }

    .flatpickr-calendar::before,
    .flatpickr-calendar::after {
        display: none;
    }

    .flatpickr-innerContainer {
        padding: 0 0.5rem 0.75rem;
    }

    /* Month toolbar — same feel as dropdown filter header strip */
    .flatpickr-months {
        display: flex;
        align-items: center;
        padding: 0.5rem 0.625rem;
        border-bottom: 1px solid #e5e7eb;
        background-color: #f9fafb;
        border-radius: 0.5rem 0.5rem 0 0;
    }

    .dark .flatpickr-months {
        border-bottom-color: #374151;
        background-color: rgb(31 41 55 / 0.55);
    }

    .flatpickr-months .flatpickr-month {
        height: auto;
        min-height: 2.25rem;
        color: #111827;
    }

    .dark .flatpickr-months .flatpickr-month {
        color: #f3f4f6;
    }

    .flatpickr-months .flatpickr-prev-month,
    .flatpickr-months .flatpickr-next-month {
        top: 0.5rem;
        height: 2rem;
        width: 2rem;
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 0.375rem;
        fill: #4b5563;
    }

    .flatpickr-months .flatpickr-prev-month:hover,
    .flatpickr-months .flatpickr-next-month:hover {
        background-color: #e5e7eb;
    }

    .dark .flatpickr-months .flatpickr-prev-month,
    .dark .flatpickr-months .flatpickr-next-month {
        fill: #9ca3af;
    }

    .dark .flatpickr-months .flatpickr-prev-month:hover,
    .dark .flatpickr-months .flatpickr-next-month:hover {
        background-color: #374151;
    }

    .flatpickr-current-month {
        padding-top: 0.25rem;
    }

    .flatpickr-current-month .flatpickr-monthDropdown-months {
        appearance: none;
        -webkit-appearance: none;
        background-color: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 0.375rem;
        color: #111827;
        font-size: 0.875rem;
        font-weight: 500;
        padding: 0.25rem 1.75rem 0.25rem 0.5rem;
        margin: 0 0.125rem;
        cursor: pointer;
    }

    .flatpickr-current-month .flatpickr-monthDropdown-months:hover {
        border-color: #d1d5db;
    }

    .dark .flatpickr-current-month .flatpickr-monthDropdown-months {
        background-color: #1f2937;
        border-color: #4b5563;
        color: #f3f4f6;
    }

    .dark .flatpickr-current-month .flatpickr-monthDropdown-months:hover {
        border-color: #6b7280;
    }

    .flatpickr-current-month .numInputWrapper {
        border-radius: 0.375rem;
        border: 1px solid #e5e7eb;
        background-color: #ffffff;
    }

    .dark .flatpickr-current-month .numInputWrapper {
        border-color: #4b5563;
        background-color: #1f2937;
    }

    .flatpickr-current-month .numInput,
    .flatpickr-current-month .cur-year {
        color: #111827;
        font-weight: 500;
        font-size: 0.875rem;
    }

    .dark .flatpickr-current-month .numInput,
    .dark .flatpickr-current-month .cur-year {
        color: #f3f4f6;
    }

    .numInput:hover {
        background-color: transparent !important;
    }

    .flatpickr-weekdays {
        background: transparent;
        padding-top: 0.5rem;
    }

    span.flatpickr-weekday {
        color: #6b7280;
        font-size: 0.6875rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .dark span.flatpickr-weekday {
        color: #9ca3af;
    }

    .flatpickr-day {
        border-radius: 0.375rem;
        color: #111827;
        font-weight: 500;
        border-color: transparent;
    }

    .dark .flatpickr-day {
        color: #f3f4f6;
    }

    .flatpickr-day:hover:not(.flatpickr-disabled):not(.today):not(.selected):not(.startRange):not(.endRange) {
        background-color: #f3f4f6 !important;
        border-color: transparent !important;
        color: #111827 !important;
    }

    .dark .flatpickr-day:hover:not(.flatpickr-disabled):not(.today):not(.selected):not(.startRange):not(.endRange) {
        background-color: #1f2937 !important;
        color: #f3f4f6 !important;
    }

    .flatpickr-day.today:not(.selected) {
        background-color: transparent !important;
        border: 1px solid var(--primary, #286cf0) !important;
        color: var(--primary, #286cf0) !important;
        font-weight: 600;
    }

    .flatpickr-day.today.selected {
        color: #ffffff !important;
    }

    .flatpickr-day.selected,
    .flatpickr-day.startRange,
    .flatpickr-day.endRange {
        background-color: var(--primary, #286cf0) !important;
        border-color: var(--primary, #286cf0) !important;
        color: #ffffff !important;
        font-weight: 600;
    }

    .flatpickr-day.selected:hover,
    .flatpickr-day.startRange:hover,
    .flatpickr-day.endRange:hover {
        background-color: var(--primary-deep, #1f56c0) !important;
        border-color: var(--primary-deep, #1f56c0) !important;
        color: #ffffff !important;
    }

    .flatpickr-day.flatpickr-disabled {
        color: #9ca3af !important;
        text-decoration: line-through;
    }

    .dark .flatpickr-day.flatpickr-disabled {
        color: #6b7280 !important;
    }

    .flatpickr-day.nextMonthDay,
    .flatpickr-day.prevMonthDay {
        color: #9ca3af;
    }

    .dark .flatpickr-day.nextMonthDay,
    .dark .flatpickr-day.prevMonthDay {
        color: #6b7280;
    }

    .flatpickr-day.inRange {
        background-color: rgb(40 108 240 / 0.12) !important;
        border-color: transparent !important;
        box-shadow: -3px 0 0 rgb(40 108 240 / 0.12), 3px 0 0 rgb(40 108 240 / 0.12);
        color: #111827 !important;
    }

    .dark .flatpickr-day.inRange {
        background-color: rgb(40 108 240 / 0.22) !important;
        color: #e5e7eb !important;
    }

    /* Optional time row */
    .flatpickr-calendar.hasTime .flatpickr-innerContainer + .flatpickr-time {
        border-top: 1px solid #e5e7eb;
    }

    .dark .flatpickr-calendar.hasTime .flatpickr-innerContainer + .flatpickr-time {
        border-top-color: #374151;
    }

    .flatpickr-time {
        background-color: #f9fafb;
        border-radius: 0 0 0.5rem 0.5rem;
    }

    .dark .flatpickr-time {
        background-color: rgb(17 24 39 / 0.9);
    }

    .flatpickr-time .flatpickr-time-separator {
        color: #6b7280;
    }

    .dark .flatpickr-time .flatpickr-time-separator {
        color: #9ca3af;
    }

    .flatpickr-time .flatpickr-am-pm {
        color: #111827;
        font-weight: 500;
    }

    .dark .flatpickr-time .flatpickr-am-pm {
        color: #f3f4f6;
    }

    .numInputWrapper span {
        border-color: #e5e7eb;
    }

    .dark .numInputWrapper span {
        border-color: #4b5563;
    }

    .numInputWrapper span.arrowUp:after {
        border-bottom-color: #374151 !important;
    }

    .dark .numInputWrapper span.arrowUp:after {
        border-bottom-color: #e5e7eb !important;
    }

    .numInputWrapper span.arrowDown:after {
        border-top-color: #374151 !important;
    }

    .dark .numInputWrapper span.arrowDown:after {
        border-top-color: #e5e7eb !important;
    }

    .numInputWrapper span:hover {
        background: #e5e7eb;
    }

    .dark .numInputWrapper span:hover {
        background: #374151;
    }
</style>
