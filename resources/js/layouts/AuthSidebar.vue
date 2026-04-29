<script setup lang="ts">
    import SiteLogo from '@/components/SiteLogo.vue';

    // Each bar has a unique duration + delay so they animate independently — like real speech
    const bars = [
        { delay: '0ms',    duration: '880ms'  },
        { delay: '150ms',  duration: '620ms'  },
        { delay: '60ms',   duration: '1050ms' },
        { delay: '210ms',  duration: '740ms'  },
        { delay: '30ms',   duration: '580ms'  },
        { delay: '170ms',  duration: '970ms'  },
        { delay: '90ms',   duration: '710ms'  },
        { delay: '250ms',  duration: '830ms'  },
        { delay: '15ms',   duration: '1100ms' },
        { delay: '130ms',  duration: '660ms'  },
        { delay: '75ms',   duration: '920ms'  },
        { delay: '195ms',  duration: '780ms'  },
        { delay: '45ms',   duration: '1020ms' },
        { delay: '230ms',  duration: '640ms'  },
        { delay: '110ms',  duration: '860ms'  },
        { delay: '280ms',  duration: '750ms'  },
        { delay: '20ms',   duration: '990ms'  },
        { delay: '160ms',  duration: '680ms'  },
        { delay: '85ms',   duration: '1130ms' },
        { delay: '310ms',  duration: '720ms'  },
    ];
</script>

<template>
    <div class="relative hidden overflow-hidden md:flex md:flex-col" style="background: oklch(0.13 0.025 265);">

        <!-- Noise texture -->
        <div
            class="pointer-events-none absolute inset-0 opacity-[0.03]"
            style="background-image: url('data:image/svg+xml,%3Csvg viewBox=%220 0 256 256%22 xmlns=%22http://www.w3.org/2000/svg%22%3E%3Cfilter id=%22noise%22%3E%3CfeTurbulence type=%22fractalNoise%22 baseFrequency=%220.9%22 numOctaves=%224%22 stitchTiles=%22stitch%22/%3E%3C/filter%3E%3Crect width=%22100%25%22 height=%22100%25%22 filter=%22url(%23noise)%22/%3E%3C/svg%3E'); background-size: 200px;"
        />

        <!-- Indigo glow — top right -->
        <div class="pointer-events-none absolute -right-24 -top-24 h-80 w-80 rounded-full opacity-20" style="background: radial-gradient(circle, oklch(0.55 0.20 268), transparent 70%);" />
        <!-- Subtle glow — bottom left -->
        <div class="pointer-events-none absolute -bottom-32 -left-16 h-72 w-72 rounded-full opacity-10" style="background: radial-gradient(circle, oklch(0.55 0.20 268), transparent 70%);" />

        <div class="relative flex flex-1 flex-col justify-between px-12 py-14 lg:px-14">

            <!-- Logo -->
            <div>
                <SiteLogo :dark="true" />

                <div class="mt-12">
                    <p class="text-xs font-medium uppercase tracking-widest" style="color: oklch(0.55 0.20 268);">
                        AI Interview Coach
                    </p>
                    <h2 class="mt-3 text-[1.75rem] font-semibold leading-snug tracking-tight" style="color: oklch(0.97 0.005 265);">
                        The gap between<br>
                        <span style="color: oklch(0.72 0.15 268);">good</span> and <span style="color: oklch(0.97 0.005 265);">hired</span><br>
                        is practice.
                    </h2>
                    <p class="mt-4 text-sm leading-relaxed" style="color: oklch(0.60 0.015 265);">
                        Real voice conversations. Honest feedback. No fluff.
                    </p>
                </div>
            </div>

            <!-- Mock session UI -->
            <div class="my-10">
                <div class="rounded-2xl border p-5" style="background: oklch(0.18 0.025 265); border-color: oklch(0.30 0.02 265);">

                    <!-- Session header -->
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="h-2 w-2 animate-pulse rounded-full" style="background: oklch(0.62 0.15 155);" />
                            <span class="text-xs font-medium" style="color: oklch(0.62 0.15 155);">Live session</span>
                        </div>
                        <span class="text-xs tabular-nums" style="color: oklch(0.45 0.015 265);">04:32</span>
                    </div>

                    <!-- Interviewer question -->
                    <div class="mt-4 flex gap-3">
                        <div
                            class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full text-[10px] font-semibold"
                            style="background: oklch(0.55 0.20 268 / 20%); color: oklch(0.72 0.15 268);"
                        >
                            AI
                        </div>
                        <div class="flex-1">
                            <p class="text-xs font-medium" style="color: oklch(0.55 0.015 265);">Interviewer</p>
                            <p class="mt-1 text-sm leading-relaxed" style="color: oklch(0.85 0.01 265);">
                                "Tell me about a time you had to influence a decision without direct authority."
                            </p>
                        </div>
                    </div>

                    <!-- Divider -->
                    <div class="my-4 h-px" style="background: oklch(0.25 0.02 265);" />

                    <!-- Animated speech waveform -->
                    <div class="flex items-center gap-2">
                        <div class="flex items-center gap-0.75" style="height: 20px;">
                            <div
                                v-for="(bar, i) in bars"
                                :key="i"
                                class="speech-bar w-0.75 rounded-full"
                                :style="{
                                    animationDelay: bar.delay,
                                    animationDuration: bar.duration,
                                    background: 'oklch(0.55 0.20 268 / 80%)',
                                }"
                            />
                        </div>
                        <span class="text-xs" style="color: oklch(0.45 0.015 265);">You're speaking…</span>
                    </div>
                </div>

                <!-- Score tiles -->
                <div class="mt-3 grid grid-cols-3 gap-2">
                    <div
                        v-for="item in [{ label: 'Clarity', score: '8.4' }, { label: 'Structure', score: '7.9' }, { label: 'Impact', score: '9.1' }]"
                        :key="item.label"
                        class="rounded-xl px-3 py-3 text-center"
                        style="background: oklch(0.18 0.025 265); border: 1px solid oklch(0.25 0.02 265);"
                    >
                        <p class="text-base font-semibold" style="color: oklch(0.97 0.005 265);">{{ item.score }}</p>
                        <p class="mt-0.5 text-[11px]" style="color: oklch(0.45 0.015 265);">{{ item.label }}</p>
                    </div>
                </div>
            </div>

            <!-- Testimonial -->
            <div>
                <div class="mb-6 h-px" style="background: oklch(0.22 0.02 265);" />
                <blockquote>
                    <p class="text-sm leading-relaxed" style="color: oklch(0.70 0.01 265);">
                        "Six sessions before my Stripe loop. Got the offer."
                    </p>
                    <footer class="mt-3 flex items-center gap-2.5">
                        <div
                            class="grid h-7 w-7 place-items-center rounded-full text-xs font-semibold"
                            style="background: oklch(0.55 0.20 268 / 15%); color: oklch(0.72 0.15 268);"
                        >
                            M
                        </div>
                        <div>
                            <p class="text-xs font-medium" style="color: oklch(0.65 0.01 265);">Maya R.</p>
                            <p class="text-[11px]" style="color: oklch(0.42 0.01 265);">Senior PM, fintech</p>
                        </div>
                    </footer>
                </blockquote>
            </div>

        </div>
    </div>
</template>

<style scoped>
@keyframes speech-bar {
    0%   { height: 3px;  opacity: 0.4; }
    100% { height: 18px; opacity: 1;   }
}

.speech-bar {
    animation-name: speech-bar;
    animation-timing-function: ease-in-out;
    animation-iteration-count: infinite;
    animation-direction: alternate;
    height: 3px;
}
</style>
