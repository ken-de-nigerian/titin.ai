<script setup lang="ts">
    import { useId } from 'vue';

    defineProps<{
        title: string;
        description?: string;
    }>();

    const uid = useId();
    const gridGradientId = `${uid}-not-found-grid-fade`;
    const gridMaskId = `${uid}-not-found-grid-mask`;
</script>

<template>
    <div class="surface rounded-xl border border-hairline p-4 flex flex-col items-center gap-4 px-6 py-8 text-center shadow-xs">
        <div class="flex flex-col items-center text-center">
            <div class="relative flex flex-col items-center justify-center" style="height: 240px; width: 240px">
                <span class="absolute left-0 top-0">
                    <svg xmlns="http://www.w3.org/2000/svg" width="240" height="240" viewBox="0 0 240 240" aria-hidden="true">
                        <defs>
                            <radialGradient :id="gridGradientId" cx="50%" cy="50%" r="60%">
                                <stop offset="50%" stop-color="white" stop-opacity="1" />
                                <stop offset="100%" stop-color="white" stop-opacity="0" />
                            </radialGradient>
                            <mask :id="gridMaskId">
                                <rect width="240" height="240" :fill="`url(#${gridGradientId})`" />
                            </mask>
                        </defs>

                        <g class="text-hairline" stroke="currentColor" stroke-width="1" :mask="`url(#${gridMaskId})`">
                            <line x1="0" y1="0" x2="0" y2="240" />
                            <line x1="40" y1="0" x2="40" y2="240" />
                            <line x1="80" y1="0" x2="80" y2="240" />
                            <line x1="120" y1="0" x2="120" y2="240" />
                            <line x1="160" y1="0" x2="160" y2="240" />
                            <line x1="200" y1="0" x2="200" y2="240" />
                            <line x1="240" y1="0" x2="240" y2="240" />
                            <line x1="0" y1="0" x2="240" y2="0" />
                            <line x1="0" y1="40" x2="240" y2="40" />
                            <line x1="0" y1="80" x2="240" y2="80" />
                            <line x1="0" y1="120" x2="240" y2="120" />
                            <line x1="0" y1="160" x2="240" y2="160" />
                            <line x1="0" y1="200" x2="240" y2="200" />
                            <line x1="0" y1="240" x2="240" y2="240" />
                        </g>
                    </svg>
                </span>

                <div class="z-1">
                    <div class="flex size-48 items-center justify-center rounded-full bg-radial from-background via-transparent via-90%">
                        <svg xmlns="http://www.w3.org/2000/svg" xml:space="preserve" x="0" y="0" viewBox="0 0 250 250" height="100" width="100" aria-hidden="true">
                            <path
                                d="M15.5 99C15.5 113.676 42.25 131.458 96 132"
                                class="fill-none stroke-5 stroke-linecap-round text-muted-foreground/40"
                                stroke="currentColor" />
                            <path
                                d="M15.5 152C15.5 166.819 38.035 183 92.6859 183C100.109 183 107.176 182.629 114 181.898"
                                class="fill-none stroke-5 stroke-linecap-round text-muted-foreground/40"
                                stroke="currentColor" />
                            <path
                                d="M176.9 201.4C176.9 203.75 176.9 205.75 175.99 208.33C169.79 224.95 140.19 236.55 95.32 236.55C52.27 236.55 14.45 224.72 15.48 203.39V46.39"
                                class="fill-none stroke-5 stroke-linecap-round text-foreground/70"
                                stroke="currentColor" />
                            <path d="M176.9 46.41V75.03" class="fill-none stroke-5 stroke-linecap-round text-foreground/70" stroke="currentColor" stroke-linecap="round" />
                            <path
                                d="M176.9 46.41C176.9 63.16 146.15 77.68 95.77 77.68C52.72 77.68 15.45 65.85 15.45 46.41C15.45 29.66 43.83 12.77 96.65 12.77C141.32 12.77 176.9 27.29 176.9 46.41Z"
                                class="fill-none stroke-5 stroke-linecap-round text-foreground/70"
                                stroke="currentColor" />
                            <path
                                d="M195.7 97.61C174.09 76.79 140.89 78.75 122.69 98.28C101.66 119.11 101.06 152.82 121.8 175.46C141.13 197.18 174.75 195.16 193.93 176.94C194.63 176.28 195.29 175.58 195.95 174.88L204.54 184.12L209.47 178.92L201.74 171.19C217.32 150.76 218.59 120.24 195.7 97.61Z"
                                class="fill-none stroke-5 stroke-linecap-round text-foreground/70 [stroke-dasharray:10_5]"
                                stroke="currentColor" />
                            <path
                                d="M232.733 196.975L214.315 179.089C212.747 177.491 210.887 177.839 209.914 178.932L204.186 185.079C202.767 186.639 202.426 188.642 204.254 190.474L223.441 209.117C226.763 212.598 231.599 210.766 233.564 207.557C235.684 204.512 235.498 199.988 232.733 196.975Z"
                                class="fill-none stroke-5 stroke-linecap-round text-foreground/70"
                                stroke="currentColor" />
                            <path
                                d="M185.7 119.9L178.39 113.09L159.86 130.6L143.36 113.09L135.78 120.45L152.67 137.64L135.78 156.17L143.36 164.16L160.55 145.63L178.39 163.8L185.7 155.81L169.21 138.3L185.7 120.45V119.9Z"
                                class="stroke-5 stroke-linecap-round text-brand fill-brand-soft"
                                stroke="currentColor" />
                        </svg>
                    </div>
                </div>
            </div>
            <div class="z-1 flex flex-col items-center text-center" style="margin-top: -40px">
                <span class="text-lg font-semibold tracking-tight">{{ title }}</span>
                <p v-if="description" class="mt-2 max-w-sm px-2 text-center text-sm text-muted-foreground">
                    {{ description }}
                </p>
            </div>
        </div>
        <div class="flex w-full justify-center text-center">
            <slot />
        </div>
    </div>
</template>
