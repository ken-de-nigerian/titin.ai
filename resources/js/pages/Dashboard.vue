<script setup lang="ts">
    import { Head, Link } from '@inertiajs/vue3';
    import { ArrowRight, ArrowUpRight, TrendingUp, Mic, Clock, Target, Plus, Search } from 'lucide-vue-next';

    import SiteHeader from '@/components/SiteHeader.vue';

    const history = [
        { role: 'Senior Product Manager', track: 'Behavioral', score: 8.6, date: 'Today', duration: '24m', trend: +0.4 },
        { role: 'Staff Engineer', track: 'System design', score: 7.9, date: 'Yesterday', duration: '31m', trend: +0.7 },
        { role: 'Brand Designer', track: 'Portfolio', score: 9.1, date: 'Mar 18', duration: '18m', trend: +0.2 },
        { role: 'Engineering Manager', track: 'Leadership', score: 7.2, date: 'Mar 16', duration: '27m', trend: -0.3 },
        { role: 'Data Scientist', track: 'Case study', score: 8.0, date: 'Mar 12', duration: '22m', trend: +0.5 },
    ];

    const stats = [
        { label: 'Sessions', value: '23', delta: '+4 this week', icon: Mic },
        { label: 'Average score', value: '8.2', delta: '+0.6 vs last week', icon: TrendingUp },
        { label: 'Practice time', value: '9h 12m', delta: '+1h 30m', icon: Clock },
        { label: 'Mastery', value: '62%', delta: 'Storytelling', icon: Target },
    ];

    const tracks = [
        { name: 'Behavioral', sessions: 11, color: 'bg-brand' },
        { name: 'System design', sessions: 5, color: 'bg-foreground' },
        { name: 'Portfolio', sessions: 4, color: 'bg-success' },
        { name: 'Case study', sessions: 3, color: 'bg-warning' },
    ];

    const scoreHistory = [6.4, 7.1, 7.0, 7.9, 8.2, 8.6];
    const scoreDates = ['Mar 2', 'Mar 7', 'Mar 10', 'Mar 16', 'Mar 22', 'Today'];

    const suggestions = [
        { title: 'Sharpen your storytelling', desc: 'Five behavioral prompts focused on impact-first answers.', cta: 'Start track' },
        { title: 'Mock: Senior PM loop', desc: 'A full 45-minute loop with three back-to-back rounds.', cta: 'Start mock' },
        { title: 'Filler word coaching', desc: "Targeted drills for 'um', 'kind of', and 'I guess'.", cta: 'Begin' },
    ];
</script>

<template>
    <Head title="Dashboard — Lumen" />

    <div class="min-h-screen bg-surface-2/40">
        <SiteHeader />

        <main class="mx-auto max-w-7xl px-6 py-10 md:py-14">
            <!-- Greeting -->
            <div class="flex flex-wrap items-end justify-between gap-6">
                <div>
                    <p class="text-xs font-medium uppercase tracking-wider text-muted-foreground">
                        Tuesday, March 26
                    </p>
                    <h1 class="mt-2 text-3xl font-semibold tracking-tight md:text-4xl">
                        Good evening, Alex
                    </h1>
                    <p class="mt-2 text-sm text-muted-foreground">
                        Your last session ended at 8.6. Continue where you left off, or start something new.
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <div class="hidden items-center gap-2 rounded-lg border border-hairline bg-surface px-3 py-2 text-sm text-muted-foreground shadow-xs md:flex">
                        <Search class="h-4 w-4" />
                        <input
                            placeholder="Search sessions"
                            class="w-48 bg-transparent outline-none placeholder:text-muted-foreground"
                        />
                    </div>
                    <Link
                        :href="route('interview')"
                        class="group inline-flex items-center gap-2 rounded-lg bg-foreground px-4 py-2.5 text-sm font-medium text-background shadow-sm transition hover:bg-foreground/90"
                    >
                        <Plus class="h-4 w-4" />
                        New session
                    </Link>
                </div>
            </div>

            <!-- Stats -->
            <div class="mt-10 grid gap-4 md:grid-cols-4">
                <div
                    v-for="s in stats"
                    :key="s.label"
                    class="surface rounded-xl p-5 shadow-xs"
                >
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-medium text-muted-foreground">{{ s.label }}</p>
                        <div class="grid h-7 w-7 place-items-center rounded-md bg-brand-soft text-brand">
                            <component
                                :is="s.icon"
                                class="h-3.5 w-3.5"
                            />
                        </div>
                    </div>
                    <p class="mt-4 text-2xl font-semibold tracking-tight tabular-nums">{{ s.value }}</p>
                    <p class="mt-1 text-xs text-muted-foreground">{{ s.delta }}</p>
                </div>
            </div>

            <!-- Two-column area -->
            <div class="mt-8 grid gap-6 lg:grid-cols-3">
                <!-- Score chart -->
                <div class="surface rounded-xl p-6 shadow-xs lg:col-span-2">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-base font-semibold tracking-tight">Score trajectory</h2>
                            <p class="mt-0.5 text-xs text-muted-foreground">Last 6 sessions</p>
                        </div>
                        <div class="flex items-center gap-1.5 rounded-md bg-success/10 px-2 py-1 text-xs font-medium text-success">
                            <TrendingUp class="h-3 w-3" />
                            +18%
                        </div>
                    </div>

                    <div class="mt-8 flex h-48 items-end gap-3">
                        <div
                            v-for="(v, i) in scoreHistory"
                            :key="i"
                            class="group flex flex-1 flex-col items-center gap-2"
                        >
                            <div class="relative w-full flex-1 overflow-hidden rounded-md bg-brand-soft">
                                <div
                                    class="absolute bottom-0 left-0 right-0 rounded-md bg-brand transition-all group-hover:bg-foreground"
                                    :style="{ height: `${(v / 10) * 100}%` }"
                                />
                            </div>
                            <span class="text-[10px] font-medium tabular-nums text-muted-foreground">{{ v.toFixed(1) }}</span>
                        </div>
                    </div>
                    <div class="mt-2 flex justify-between text-[10px] text-muted-foreground">
                        <span
                            v-for="d in scoreDates"
                            :key="d"
                        >
                            {{ d }}
                        </span>
                    </div>
                </div>

                <!-- Tracks -->
                <div class="surface rounded-xl p-6 shadow-xs">
                    <h2 class="text-base font-semibold tracking-tight">Practice mix</h2>
                    <p class="mt-0.5 text-xs text-muted-foreground">By interview type</p>

                    <div class="mt-6 flex h-2 overflow-hidden rounded-full bg-surface-2">
                        <div
                            v-for="t in tracks"
                            :key="t.name"
                            :class="t.color"
                            :style="{ width: `${(t.sessions / 23) * 100}%` }"
                        />
                    </div>

                    <ul class="mt-6 space-y-3">
                        <li
                            v-for="t in tracks"
                            :key="t.name"
                            class="flex items-center justify-between text-sm"
                        >
                            <span class="flex items-center gap-2.5">
                                <span :class="['h-2 w-2 rounded-full', t.color]" />
                                {{ t.name }}
                            </span>
                            <span class="text-muted-foreground tabular-nums">{{ t.sessions }}</span>
                        </li>
                    </ul>

                    <button class="mt-6 inline-flex items-center gap-1 text-xs font-medium text-brand hover:underline">
                        View detailed report <ArrowRight class="h-3 w-3" />
                    </button>
                </div>
            </div>

            <!-- History table -->
            <section class="mt-8">
                <div class="surface rounded-xl shadow-xs">
                    <div class="flex items-center justify-between border-b border-hairline px-6 py-4">
                        <h2 class="text-base font-semibold tracking-tight">Recent sessions</h2>
                        <button class="text-xs font-medium text-muted-foreground transition hover:text-foreground">
                            View all
                        </button>
                    </div>

                    <div class="divide-y divide-hairline">
                        <Link
                            v-for="(h, i) in history"
                            :key="i"
                            :href="route('feedback')"
                            class="group flex items-center gap-6 px-6 py-4 transition hover:bg-surface-2/60"
                        >
                            <div class="grid h-10 w-10 place-items-center rounded-lg bg-brand-soft text-brand">
                                <Mic class="h-4 w-4" />
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-medium">{{ h.role }}</p>
                                <p class="text-xs text-muted-foreground">{{ h.track }} · {{ h.duration }}</p>
                            </div>
                            <span class="hidden text-xs text-muted-foreground md:inline">{{ h.date }}</span>
                            <div class="flex items-center gap-1.5">
                                <span
                                    :class="[
                                        'text-xs font-medium tabular-nums',
                                        h.trend >= 0 ? 'text-success' : 'text-destructive',
                                    ]"
                                >
                                    {{ h.trend >= 0 ? '+' : '' }}{{ h.trend }}
                                </span>
                            </div>
                            <div class="w-14 text-right">
                                <span class="text-lg font-semibold tabular-nums">{{ h.score }}</span>
                            </div>
                            <ArrowUpRight class="h-4 w-4 text-muted-foreground transition group-hover:text-foreground" />
                        </Link>
                    </div>
                </div>
            </section>

            <!-- Suggested -->
            <section class="mt-8 grid gap-4 md:grid-cols-3">
                <div
                    v-for="c in suggestions"
                    :key="c.title"
                    class="surface rounded-xl p-6 shadow-xs"
                >
                    <h3 class="text-sm font-semibold tracking-tight">{{ c.title }}</h3>
                    <p class="mt-2 text-sm text-muted-foreground">{{ c.desc }}</p>
                    <button class="mt-5 inline-flex items-center gap-1.5 text-sm font-medium text-brand hover:underline">
                        {{ c.cta }} <ArrowRight class="h-3.5 w-3.5" />
                    </button>
                </div>
            </section>
        </main>
    </div>
</template>
