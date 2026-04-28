<script setup lang="ts">
    import { useId } from 'vue';

    defineProps<{
        text: string;
    }>();

    const tooltipId = useId();
</script>

<template>
    <span class="group relative ml-0.5 inline-flex items-center align-middle">
        <button
            type="button"
            class="cursor-help rounded p-0.5 text-gray-500 hover:text-gray-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary dark:text-gray-400 dark:hover:text-gray-200"
            :aria-describedby="tooltipId"
            aria-label="About this field">
            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" aria-hidden="true">
                <path
                    d="M12 22C17.5 22 22 17.5 22 12C22 6.5 17.5 2 12 2C6.5 2 2 6.5 2 12C2 17.5 6.5 22 12 22Z"
                    stroke="currentColor"
                    stroke-width="1.75"
                    stroke-linecap="round"
                    stroke-linejoin="round" />
                <path d="M12 8V13" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" />
                <path d="M11.9941 16H12.0031" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
        </button>
        <span
            :id="tooltipId"
            role="tooltip"
            class="tooltip pointer-events-auto invisible absolute top-full left-1/2 z-60 mt-2 max-w-[min(22rem,calc(100vw-2rem))]! w-max -translate-x-1/2 px-3 py-2 text-left text-xs leading-snug font-normal opacity-0 shadow-lg transition-opacity duration-150 group-hover:visible group-hover:opacity-100 group-focus-within:visible group-focus-within:opacity-100">
            {{ text }}
        </span>
    </span>
</template>
