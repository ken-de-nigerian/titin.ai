<script setup lang="ts">
    import {Head, usePage} from "@inertiajs/vue3";
    import {computed} from "vue";
    import AppLayout from "@/layouts/AppLayout.vue";
    import TextLink from "@/components/TextLink.vue";
    import {useRoute} from "@/composables/useRoute";

    const page = usePage()
    const route = useRoute();

    const auth = computed(() => (page.props as any).auth)

    const greeting = computed(() => {
        const hour = new Date().getHours()
        if (hour < 12) return 'Good morning'
        if (hour < 17) return 'Good afternoon'
        return 'Good evening'
    })

    const firstName = computed(() => {
        return auth.value?.user?.name?.split(' ')[0] || 'there'
    })
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout>

        <main class="flex-1 overflow-y-auto px-5 pb-28 md:mx-auto md:w-full md:max-w-5xl md:px-0 md:pb-10">
            <section>
                <p class="text-[10px] font-medium uppercase tracking-wider text-muted-foreground">{{ new Date().toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric' }) }}</p>
                <h1 class="mt-2 text-2xl font-semibold tracking-tight md:text-3xl">{{ greeting }},
                    <span class="font-serif italic font-normal text-muted-foreground">{{ firstName }}.</span>
                </h1>
                <p class="mt-1.5 text-sm text-muted-foreground">Your last session ended at 8.6. Keep going.</p>
            </section>

            <section class="surface mt-5 relative overflow-hidden rounded-2xl border border-hairline p-5 shadow-xs">
                <div class="absolute -right-10 -top-10 h-40 w-40 rounded-full bg-brand-soft"></div>
                <div class="relative">
                    <p class="text-[11px] font-medium uppercase tracking-wider text-brand">Continue</p>
                    <h2 class="mt-1 text-lg font-semibold tracking-tight">Senior PM — Behavioral</h2>
                    <p class="mt-1 text-xs text-muted-foreground">Last score 8.6 · 3 prompts left</p>
                    <div class="mt-4 flex items-center gap-2">
                        <a href="/interview" class="inline-flex items-center gap-1.5 rounded-full bg-foreground px-4 py-2 text-xs font-medium text-background shadow-sm transition active:scale-[0.98]">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-mic h-3.5 w-3.5" aria-hidden="true">
                                <path d="M12 19v3"></path>
                                <path d="M19 10v2a7 7 0 0 1-14 0v-2"></path>
                                <rect x="9" y="2" width="6" height="13" rx="3"></rect>
                            </svg>
                            Resume
                        </a>

                        <a href="/feedback" class="inline-flex items-center gap-1 rounded-full border border-hairline bg-surface px-4 py-2 text-xs font-medium text-foreground transition active:scale-[0.98]">Last feedback
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-right h-3 w-3" aria-hidden="true">
                                <path d="m9 18 6-6-6-6"></path>
                            </svg>
                        </a>
                    </div>
                </div>
            </section>

            <section class="mt-5 grid grid-cols-2 gap-3">
                <div class="surface rounded-xl border border-hairline p-4 shadow-xs">
                    <div class="flex items-center justify-between">
                        <p class="text-[11px] font-medium text-muted-foreground">Sessions</p>
                        <div class="grid h-6 w-6 place-items-center rounded-md bg-brand-soft text-brand">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                width="24"
                                height="24"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                class="lucide lucide-mic h-3 w-3"
                                aria-hidden="true"
                            >
                                <path d="M12 19v3"></path>
                                <path d="M19 10v2a7 7 0 0 1-14 0v-2"></path>
                                <rect x="9" y="2" width="6" height="13" rx="3"></rect>
                            </svg>
                        </div>
                    </div>
                    <p class="mt-2 text-xl font-semibold tabular-nums tracking-tight">23</p>
                    <p class="mt-0.5 text-[11px] text-muted-foreground">+4</p>
                </div>
                <div class="surface rounded-xl border border-hairline p-4 shadow-xs">
                    <div class="flex items-center justify-between">
                        <p class="text-[11px] font-medium text-muted-foreground">Avg score</p>
                        <div class="grid h-6 w-6 place-items-center rounded-md bg-brand-soft text-brand">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                width="24"
                                height="24"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                class="lucide lucide-trending-up h-3 w-3"
                                aria-hidden="true"
                            >
                                <path d="M16 7h6v6"></path>
                                <path d="m22 7-8.5 8.5-5-5L2 17"></path>
                            </svg>
                        </div>
                    </div>
                    <p class="mt-2 text-xl font-semibold tabular-nums tracking-tight">8.2</p>
                    <p class="mt-0.5 text-[11px] text-muted-foreground">+0.6</p>
                </div>
                <div class="surface rounded-xl border border-hairline p-4 shadow-xs">
                    <div class="flex items-center justify-between">
                        <p class="text-[11px] font-medium text-muted-foreground">Practice time</p>
                        <div class="grid h-6 w-6 place-items-center rounded-md bg-brand-soft text-brand">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                width="24"
                                height="24"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                class="lucide lucide-clock h-3 w-3"
                                aria-hidden="true"
                            >
                                <circle cx="12" cy="12" r="10"></circle>
                                <path d="M12 6v6l4 2"></path>
                            </svg>
                        </div>
                    </div>
                    <p class="mt-2 text-xl font-semibold tabular-nums tracking-tight">9h 12m</p>
                    <p class="mt-0.5 text-[11px] text-muted-foreground">+1h 30m</p>
                </div>
                <div class="surface rounded-xl border border-hairline p-4 shadow-xs">
                    <div class="flex items-center justify-between">
                        <p class="text-[11px] font-medium text-muted-foreground">Mastery</p>
                        <div class="grid h-6 w-6 place-items-center rounded-md bg-brand-soft text-brand">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                width="24"
                                height="24"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                class="lucide lucide-target h-3 w-3"
                                aria-hidden="true"
                            >
                                <circle cx="12" cy="12" r="10"></circle>
                                <circle cx="12" cy="12" r="6"></circle>
                                <circle cx="12" cy="12" r="2"></circle>
                            </svg>
                        </div>
                    </div>
                    <p class="mt-2 text-xl font-semibold tabular-nums tracking-tight">62%</p>
                    <p class="mt-0.5 text-[11px] text-muted-foreground">Story.</p>
                </div>
            </section>

            <section class="surface mt-5 rounded-2xl border border-hairline p-5 shadow-xs">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-semibold tracking-tight">Score trajectory</h3>
                        <p class="text-[11px] text-muted-foreground">Last 6 sessions</p>
                    </div>

                    <div
                        class="flex items-center gap-1 rounded-full bg-success/10 px-2 py-0.5 text-[11px] font-medium text-success"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            width="24"
                            height="24"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            class="lucide lucide-trending-up h-3 w-3"
                            aria-hidden="true"
                        >
                            <path d="M16 7h6v6"></path>
                            <path d="m22 7-8.5 8.5-5-5L2 17"></path></svg
                        >+18%
                    </div>
                </div>
                <div class="mt-5 flex h-32 items-end gap-2">
                    <div class="flex flex-1 flex-col items-center gap-1.5">
                        <div class="relative w-full flex-1 overflow-hidden rounded-md bg-brand-soft">
                            <div
                                class="absolute bottom-0 left-0 right-0 rounded-md bg-brand"
                                style="height: 64%"
                            ></div>
                        </div>
                        <span class="text-[10px] font-medium tabular-nums text-muted-foreground">6.4</span>
                    </div>
                    <div class="flex flex-1 flex-col items-center gap-1.5">
                        <div class="relative w-full flex-1 overflow-hidden rounded-md bg-brand-soft">
                            <div
                                class="absolute bottom-0 left-0 right-0 rounded-md bg-brand"
                                style="height: 71%"
                            ></div>
                        </div>
                        <span class="text-[10px] font-medium tabular-nums text-muted-foreground">7.1</span>
                    </div>
                    <div class="flex flex-1 flex-col items-center gap-1.5">
                        <div class="relative w-full flex-1 overflow-hidden rounded-md bg-brand-soft">
                            <div
                                class="absolute bottom-0 left-0 right-0 rounded-md bg-brand"
                                style="height: 70%"
                            ></div>
                        </div>
                        <span class="text-[10px] font-medium tabular-nums text-muted-foreground">7.0</span>
                    </div>
                    <div class="flex flex-1 flex-col items-center gap-1.5">
                        <div class="relative w-full flex-1 overflow-hidden rounded-md bg-brand-soft">
                            <div
                                class="absolute bottom-0 left-0 right-0 rounded-md bg-brand"
                                style="height: 79%"
                            ></div>
                        </div>
                        <span class="text-[10px] font-medium tabular-nums text-muted-foreground">7.9</span>
                    </div>
                    <div class="flex flex-1 flex-col items-center gap-1.5">
                        <div class="relative w-full flex-1 overflow-hidden rounded-md bg-brand-soft">
                            <div
                                class="absolute bottom-0 left-0 right-0 rounded-md bg-brand"
                                style="height: 82%"
                            ></div>
                        </div>
                        <span class="text-[10px] font-medium tabular-nums text-muted-foreground">8.2</span>
                    </div>
                    <div class="flex flex-1 flex-col items-center gap-1.5">
                        <div class="relative w-full flex-1 overflow-hidden rounded-md bg-brand-soft">
                            <div
                                class="absolute bottom-0 left-0 right-0 rounded-md bg-brand"
                                style="height: 86%"
                            ></div>
                        </div>
                        <span class="text-[10px] font-medium tabular-nums text-muted-foreground">8.6</span>
                    </div>
                </div>
            </section>

            <section class="surface mt-5 rounded-2xl border border-hairline p-5 shadow-xs">
                <h3 class="text-sm font-semibold tracking-tight">Practice mix</h3>
                <div class="mt-4 flex h-2 overflow-hidden rounded-full bg-surface-2">
                    <div class="bg-brand" style="width: 47.8261%"></div>
                    <div class="bg-foreground" style="width: 21.7391%"></div>
                    <div class="bg-success" style="width: 17.3913%"></div>
                    <div class="bg-warning" style="width: 13.0435%"></div>
                </div>
                <ul class="mt-4 space-y-2.5">
                    <li class="flex items-center justify-between text-xs">
                            <span class="flex items-center gap-2"
                            ><span class="h-1.5 w-1.5 rounded-full bg-brand"></span>Behavioral</span
                            ><span class="text-muted-foreground tabular-nums">11</span>
                    </li>
                    <li class="flex items-center justify-between text-xs">
                            <span class="flex items-center gap-2"
                            ><span class="h-1.5 w-1.5 rounded-full bg-foreground"></span>System design</span
                            ><span class="text-muted-foreground tabular-nums">5</span>
                    </li>
                    <li class="flex items-center justify-between text-xs">
                            <span class="flex items-center gap-2"
                            ><span class="h-1.5 w-1.5 rounded-full bg-success"></span>Portfolio</span
                            ><span class="text-muted-foreground tabular-nums">4</span>
                    </li>
                    <li class="flex items-center justify-between text-xs">
                            <span class="flex items-center gap-2"
                            ><span class="h-1.5 w-1.5 rounded-full bg-warning"></span>Case study</span
                            ><span class="text-muted-foreground tabular-nums">3</span>
                    </li>
                </ul>
            </section>

            <section class="mt-6">
                <div class="mb-2 flex items-center justify-between px-1">
                    <h3 class="text-sm font-semibold tracking-tight">Recent sessions</h3>
                    <button class="text-[11px] font-medium text-muted-foreground hover:text-foreground">
                        See all
                    </button>
                </div>
                <div
                    class="surface divide-y divide-hairline overflow-hidden rounded-2xl border border-hairline shadow-xs"
                >
                    <a
                        href="/feedback"
                        class="flex items-center gap-3 px-4 py-3.5 transition active:bg-surface-2"
                    ><div
                        class="grid h-9 w-9 shrink-0 place-items-center rounded-lg bg-brand-soft text-brand"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            width="24"
                            height="24"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            class="lucide lucide-mic h-3.5 w-3.5"
                            aria-hidden="true"
                        >
                            <path d="M12 19v3"></path>
                            <path d="M19 10v2a7 7 0 0 1-14 0v-2"></path>
                            <rect x="9" y="2" width="6" height="13" rx="3"></rect>
                        </svg>
                    </div>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-medium">Senior Product Manager</p>
                            <p class="text-[11px] text-muted-foreground">Behavioral · 24m · Today</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-semibold tabular-nums">8.6</p>
                            <p class="text-[10px] font-medium tabular-nums text-success">+0.4</p>
                        </div>
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            width="24"
                            height="24"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            class="lucide lucide-arrow-up-right h-4 w-4 shrink-0 text-muted-foreground"
                            aria-hidden="true"
                        >
                            <path d="M7 7h10v10"></path>
                            <path d="M7 17 17 7"></path></svg></a
                    ><a
                    href="/feedback"
                    class="flex items-center gap-3 px-4 py-3.5 transition active:bg-surface-2"
                ><div
                    class="grid h-9 w-9 shrink-0 place-items-center rounded-lg bg-brand-soft text-brand"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        width="24"
                        height="24"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        class="lucide lucide-mic h-3.5 w-3.5"
                        aria-hidden="true"
                    >
                        <path d="M12 19v3"></path>
                        <path d="M19 10v2a7 7 0 0 1-14 0v-2"></path>
                        <rect x="9" y="2" width="6" height="13" rx="3"></rect>
                    </svg>
                </div>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-medium">Staff Engineer</p>
                        <p class="text-[11px] text-muted-foreground">System design · 31m · Yesterday</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-semibold tabular-nums">7.9</p>
                        <p class="text-[10px] font-medium tabular-nums text-success">+0.7</p>
                    </div>
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        width="24"
                        height="24"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        class="lucide lucide-arrow-up-right h-4 w-4 shrink-0 text-muted-foreground"
                        aria-hidden="true"
                    >
                        <path d="M7 7h10v10"></path>
                        <path d="M7 17 17 7"></path></svg></a
                ><a
                    href="/feedback"
                    class="flex items-center gap-3 px-4 py-3.5 transition active:bg-surface-2"
                ><div
                    class="grid h-9 w-9 shrink-0 place-items-center rounded-lg bg-brand-soft text-brand"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        width="24"
                        height="24"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        class="lucide lucide-mic h-3.5 w-3.5"
                        aria-hidden="true"
                    >
                        <path d="M12 19v3"></path>
                        <path d="M19 10v2a7 7 0 0 1-14 0v-2"></path>
                        <rect x="9" y="2" width="6" height="13" rx="3"></rect>
                    </svg>
                </div>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-medium">Brand Designer</p>
                        <p class="text-[11px] text-muted-foreground">Portfolio · 18m · Mar 18</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-semibold tabular-nums">9.1</p>
                        <p class="text-[10px] font-medium tabular-nums text-success">+0.2</p>
                    </div>
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        width="24"
                        height="24"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        class="lucide lucide-arrow-up-right h-4 w-4 shrink-0 text-muted-foreground"
                        aria-hidden="true"
                    >
                        <path d="M7 7h10v10"></path>
                        <path d="M7 17 17 7"></path></svg></a
                ><a
                    href="/feedback"
                    class="flex items-center gap-3 px-4 py-3.5 transition active:bg-surface-2"
                ><div
                    class="grid h-9 w-9 shrink-0 place-items-center rounded-lg bg-brand-soft text-brand"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        width="24"
                        height="24"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        class="lucide lucide-mic h-3.5 w-3.5"
                        aria-hidden="true"
                    >
                        <path d="M12 19v3"></path>
                        <path d="M19 10v2a7 7 0 0 1-14 0v-2"></path>
                        <rect x="9" y="2" width="6" height="13" rx="3"></rect>
                    </svg>
                </div>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-medium">Engineering Manager</p>
                        <p class="text-[11px] text-muted-foreground">Leadership · 27m · Mar 16</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-semibold tabular-nums">7.2</p>
                        <p class="text-[10px] font-medium tabular-nums text-destructive">-0.3</p>
                    </div>
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        width="24"
                        height="24"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        class="lucide lucide-arrow-up-right h-4 w-4 shrink-0 text-muted-foreground"
                        aria-hidden="true"
                    >
                        <path d="M7 7h10v10"></path>
                        <path d="M7 17 17 7"></path></svg
                    ></a>
                </div>
            </section>

            <section class="surface mt-5 flex items-center gap-3 rounded-2xl border border-hairline p-4 shadow-xs">
                <div
                    class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-foreground text-background"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        width="24"
                        height="24"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        class="lucide lucide-sparkles h-4 w-4"
                        aria-hidden="true"
                    >
                        <path
                            d="M11.017 2.814a1 1 0 0 1 1.966 0l1.051 5.558a2 2 0 0 0 1.594 1.594l5.558 1.051a1 1 0 0 1 0 1.966l-5.558 1.051a2 2 0 0 0-1.594 1.594l-1.051 5.558a1 1 0 0 1-1.966 0l-1.051-5.558a2 2 0 0 0-1.594-1.594l-5.558-1.051a1 1 0 0 1 0-1.966l5.558-1.051a2 2 0 0 0 1.594-1.594z"
                        ></path>
                        <path d="M20 2v4"></path>
                        <path d="M22 4h-4"></path>
                        <circle cx="4" cy="20" r="2"></circle>
                    </svg>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-medium">Sharpen your storytelling</p>
                    <p class="text-[11px] text-muted-foreground">5 prompts focused on impact-first answers</p>
                </div>
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    width="24"
                    height="24"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    class="lucide lucide-chevron-right h-4 w-4 text-muted-foreground"
                    aria-hidden="true"
                >
                    <path d="m9 18 6-6-6-6"></path>
                </svg>
            </section>
        </main>

        <TextLink aria-label="Start new session" :href="route('user.interview.index')" class="fixed bottom-[calc(env(safe-area-inset-bottom,0)+5.5rem)] right-[max(1.25rem,env(safe-area-inset-right,0px))] z-30 grid h-14 w-14 place-items-center rounded-full bg-foreground text-background shadow-[0_10px_30px_-8px_oklch(0.20_0.02_265/0.45)] transition active:scale-95 md:bottom-10 md:right-10">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-plus h-6 w-6" aria-hidden="true">
                <path d="M5 12h14"></path>
                <path d="M12 5v14"></path>
            </svg>
        </TextLink>
    </AppLayout>
</template>
