<script setup lang="ts">
    import { Head, usePage } from '@inertiajs/vue3';
    import { Motion } from 'motion-v';
    import { computed, defineAsyncComponent } from 'vue';

    const ScoreTrajectoryApexChart = defineAsyncComponent(() => import('@/components/dashboard/ScoreTrajectoryApexChart.vue'));

    const continueCardRippleIndices = [0, 1, 2, 3] as const;

    const continueCardRippleDuration = 6.8;

    const continueCardRippleAnimate = {
        scale: [1, 1.16, 1.52, 1.78],
        opacity: [0, 0.5, 0.24, 0],
    };

    function continueCardRippleTransition(index: number) {
        return {
            duration: continueCardRippleDuration,
            repeat: Infinity,
            repeatType: 'loop' as const,
            ease: [0.45, 0.05, 0.55, 0.95] as [number, number, number, number],
            times: [0, 0.2, 0.55, 1],
            delay: index * (continueCardRippleDuration / continueCardRippleIndices.length),
        };
    }
    import TextLink from '@/components/TextLink.vue';
    import NotFoundEmptyState from '@/components/ui/NotFoundEmptyState.vue';
    import { useRoute } from '@/composables/useRoute';
    import AppLayout from '@/layouts/AppLayout.vue';
    import { resolveInterviewTypeLabel } from '@/utils/interviewType';

    const page = usePage();
    const route = useRoute();

    const interviewTypesMap = computed(
        () => ((page.props as { settings?: { interview?: { types?: Record<string, string> } } }).settings?.interview?.types ?? {}) as Record<string, string>,
    );

    function sessionInterviewTypeLabel(type: string): string {
        return resolveInterviewTypeLabel(type, interviewTypesMap.value);
    }
    const props = defineProps<{
        sessionStats: {
            total_sessions: number;
            average_score: number | null;
            total_duration_seconds: number;
            last_score: number | null;
        };
        recentSessions: Array<{
            id: number;
            job_role: string;
            interview_type: string;
            duration_seconds: number;
            overall_score: number | null;
            ended_at: string | null;
            ended_at_human: string | null;
        }>;
        scoreTrajectory: {
            points: Array<{ ended_at: string | null; score: number | null }>;
            trend: { direction: 'up' | 'down' | 'flat'; label: string; percent: number | null } | null;
        };
    }>();

    const hasTrajectoryPoints = computed(() => props.scoreTrajectory.points.length > 0);

    const hasTrajectoryScores = computed(() => props.scoreTrajectory.points.some((p) => p.score !== null));

    const scoreTrendBadgeClass = computed(() => {
        const trend = props.scoreTrajectory.trend;

        if (trend === null) {
            return '';
        }

        if (trend.direction === 'up') {
            return 'bg-success/10 text-success';
        }

        if (trend.direction === 'down') {
            return 'bg-destructive/10 text-destructive';
        }

        return 'bg-surface-2 text-muted-foreground';
    });

    const auth = computed(() => (page.props as any).auth);

    const greeting = computed(() => {
        const hour = new Date().getHours();

        if (hour < 12) {
            return 'Good morning';
        }

        if (hour < 17) {
            return 'Good afternoon';
        }

        return 'Good evening';
    });

    const firstName = computed(() => {
        return auth.value?.user?.name?.split(' ')[0] || 'there';
    });

    const totalPracticeTime = computed(() => {
        const totalSeconds = props.sessionStats.total_duration_seconds;
        const hours = Math.floor(totalSeconds / 3600);
        const minutes = Math.floor((totalSeconds % 3600) / 60);

        return `${hours}h ${minutes}m`;
    });

    const recentSummary = computed(() => {
        if (props.sessionStats.last_score === null) {
            return 'Start your first interview session to build your performance timeline.';
        }

        return `Your latest completed session scored ${props.sessionStats.last_score.toFixed(1)}. Keep going.`;
    });

    const latestSession = computed(() => props.recentSessions.at(0) ?? null);

    const continueTitle = computed(() => {
        if (latestSession.value === null) {
            return 'Begin your first interview session';
        }

        return `${latestSession.value.job_role} — ${sessionInterviewTypeLabel(latestSession.value.interview_type)}`;
    });

    const continueSubtitle = computed(() => {
        if (latestSession.value === null) {
            return 'No completed sessions yet. Complete your first session to begin building your interview history.';
        }

        const durationMinutes = Math.max(1, Math.round(latestSession.value.duration_seconds / 60));
        const score = latestSession.value.overall_score !== null ? latestSession.value.overall_score.toFixed(1) : '—';

        return `Last score ${score} · ${durationMinutes}m`;
    });

    const continueLabel = computed(() => {
        if (latestSession.value === null) {
            return 'Interview';
        }

        return 'Continue';
    });
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
                <p class="mt-1.5 text-sm text-muted-foreground">{{ recentSummary }}</p>
            </section>

            <section class="surface mt-5 relative overflow-hidden rounded-2xl border border-hairline p-5 shadow-xs">
                <div class="pointer-events-none absolute -right-10 -top-10 z-0 h-40 w-40" aria-hidden="true">
                    <div class="absolute inset-0 z-0 flex items-center justify-center">
                        <Motion
                            v-for="idx in continueCardRippleIndices"
                            :key="idx"
                            class="absolute rounded-full border-2 border-brand/35 shadow-[0_0_32px_oklch(0.55_0.14_268/0.28)]"
                            :animate="continueCardRippleAnimate"
                            :transition="continueCardRippleTransition(idx)"
                            :style="{ inset: '2%', transformOrigin: 'center center', willChange: 'transform, opacity' }"
                        />
                    </div>
                    <div class="relative z-10 h-full w-full rounded-full bg-brand-soft"></div>
                </div>

                <div class="relative">
                    <p class="text-[11px] font-medium uppercase tracking-wider text-brand">{{ continueLabel }}</p>
                    <h2 class="mt-1 text-lg font-semibold tracking-tight">{{ continueTitle }}</h2>
                    <p class="mt-1 text-xs text-muted-foreground">{{ continueSubtitle }}</p>
                    <div class="mt-4 flex items-center gap-2">
                        <TextLink :href="route('user.interview.index')" class="inline-flex items-center gap-1.5 rounded-full bg-foreground px-4 py-2 text-xs font-medium text-background shadow-sm transition active:scale-[0.98]">
                            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" width="20" height="20">
                                <path d="M12 19C15.31 19 18 16.31 18 13V8C18 4.69 15.31 2 12 2C8.69 2 6 4.69 6 8V13C6 16.31 8.69 19 12 19Z" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"></path>
                                <path d="M3 11V13C3 17.97 7.03 22 12 22C16.97 22 21 17.97 21 13V11" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"></path>
                                <path d="M9.11011 7.47999C10.8901 6.82999 12.8301 6.82999 14.6101 7.47999" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"></path>
                                <path d="M10.03 10.48C11.23 10.15 12.5 10.15 13.7 10.48" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"></path>
                            </svg>
                            Begin interview
                        </TextLink>

                        <TextLink v-if="latestSession" :href="route('user.feedback.show', { session: latestSession.id })" class="inline-flex items-center gap-1 rounded-full border border-hairline bg-surface px-4 py-2 text-xs font-medium text-foreground transition active:scale-[0.98]">Last feedback
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-right h-3 w-3" aria-hidden="true">
                                <path d="m9 18 6-6-6-6"></path>
                            </svg>
                        </TextLink>
                    </div>
                </div>
            </section>

            <section class="mt-5 grid grid-cols-2 gap-3 md:grid-cols-4">
                <div class="surface rounded-xl border border-hairline p-4 shadow-xs">
                    <div class="flex items-center justify-between">
                        <p class="text-[11px] font-medium text-muted-foreground">Sessions</p>
                        <div class="grid h-6 w-6 place-items-center rounded-md bg-brand-soft text-brand">
                            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" width="20" height="20">
                                <path d="M12 19C15.31 19 18 16.31 18 13V8C18 4.69 15.31 2 12 2C8.69 2 6 4.69 6 8V13C6 16.31 8.69 19 12 19Z" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"></path>
                                <path d="M3 11V13C3 17.97 7.03 22 12 22C16.97 22 21 17.97 21 13V11" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"></path>
                                <path d="M9.11011 7.47999C10.8901 6.82999 12.8301 6.82999 14.6101 7.47999" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"></path>
                                <path d="M10.03 10.48C11.23 10.15 12.5 10.15 13.7 10.48" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"></path>
                            </svg>
                        </div>
                    </div>
                    <p class="mt-2 text-xl font-semibold tabular-nums tracking-tight">{{ sessionStats.total_sessions }}</p>
                    <p class="mt-0.5 text-[11px] text-muted-foreground">Completed</p>
                </div>

                <div class="surface rounded-xl border border-hairline p-4 shadow-xs">
                    <div class="flex items-center justify-between">
                        <p class="text-[11px] font-medium text-muted-foreground">Avg score</p>
                        <div class="grid h-6 w-6 place-items-center rounded-md bg-brand-soft text-brand">
                            <svg viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg" width="20" height="20">
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M2.90533 2.90533C1.9898 3.82086 1.5 5.32397 1.5 7.75V13.75C1.5 16.176 1.9898 17.6791 2.90533 18.5947C3.82086 19.5102 5.32397 20 7.75 20H13.75C16.176 20 17.6791 19.5102 18.5947 18.5947C19.5102 17.6791 20 16.176 20 13.75V7.75C20 5.32397 19.5102 3.82086 18.5947 2.90533C17.6791 1.9898 16.176 1.5 13.75 1.5H7.75C5.32397 1.5 3.82086 1.9898 2.90533 2.90533ZM1.84467 1.84467C3.17914 0.510201 5.17603 0 7.75 0H13.75C16.324 0 18.3209 0.510201 19.6553 1.84467C20.9898 3.17914 21.5 5.17603 21.5 7.75V13.75C21.5 16.324 20.9898 18.3209 19.6553 19.6553C18.3209 20.9898 16.324 21.5 13.75 21.5H7.75C5.17603 21.5 3.17914 20.9898 1.84467 19.6553C0.510201 18.3209 0 16.324 0 13.75V7.75C0 5.17603 0.510201 3.17914 1.84467 1.84467Z" fill="currentColor"></path>
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M14.3906 6.94965C14.6835 7.24254 14.6835 7.71742 14.3906 8.01031L7.85064 14.5503C7.55775 14.8432 7.08288 14.8432 6.78998 14.5503C6.49709 14.2574 6.49709 13.7825 6.78998 13.4896L13.33 6.94965C13.6229 6.65676 14.0977 6.65676 14.3906 6.94965Z" fill="currentColor"></path>
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M7.73001 7.41C7.46491 7.41 7.25 7.62492 7.25 7.89001C7.25 8.15509 7.46489 8.37 7.73001 8.37C7.99511 8.37 8.20999 8.15511 8.20999 7.89001C8.20999 7.62489 7.99508 7.41 7.73001 7.41ZM5.75 7.89001C5.75 6.79649 6.63649 5.91 7.73001 5.91C8.82356 5.91 9.70999 6.79652 9.70999 7.89001C9.70999 8.98354 8.82353 9.87 7.73001 9.87C6.63651 9.87 5.75 8.98356 5.75 7.89001Z" fill="currentColor"></path>
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M14.27 13.13C14.0049 13.13 13.79 13.3449 13.79 13.61C13.79 13.8751 14.0049 14.09 14.27 14.09C14.5351 14.09 14.75 13.8751 14.75 13.61C14.75 13.3449 14.5351 13.13 14.27 13.13ZM12.29 13.61C12.29 12.5165 13.1765 11.63 14.27 11.63C15.3635 11.63 16.25 12.5164 16.25 13.61C16.25 14.7035 15.3635 15.59 14.27 15.59C13.1765 15.59 12.29 14.7035 12.29 13.61Z" fill="currentColor"></path>
                            </svg>
                        </div>
                    </div>
                    <p class="mt-2 text-xl font-semibold tabular-nums tracking-tight">
                        {{ sessionStats.average_score !== null ? sessionStats.average_score.toFixed(1) : '—' }}
                    </p>
                    <p class="mt-0.5 text-[11px] text-muted-foreground">Across completed sessions</p>
                </div>

                <div class="surface rounded-xl border border-hairline p-4 shadow-xs">
                    <div class="flex items-center justify-between">
                        <p class="text-[11px] font-medium text-muted-foreground">Practice time</p>
                        <div class="grid h-6 w-6 place-items-center rounded-md bg-brand-soft text-brand">
                            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em">
                                <path d="M11.9997 23.0106C5.93168 23.0106 0.999512 18.0784 0.999512 12.0104C0.999512 5.94243 5.93168 1.01026 11.9997 1.01026C18.0677 1.01026 22.9998 5.94243 22.9998 12.0104C22.9998 18.0784 18.0677 23.0106 11.9997 23.0106ZM11.9997 2.54517C6.78099 2.54517 2.53442 6.79175 2.53442 12.0104C2.53442 17.2291 6.78099 21.4757 11.9997 21.4757C17.2184 21.4757 21.4649 17.2291 21.4649 12.0104C21.4649 6.79175 17.2184 2.54517 11.9997 2.54517Z" fill="currentColor"></path>
                                <path d="M15.7964 16.0317C15.6634 16.0317 15.5304 16.001 15.4076 15.9191L12.2354 14.0261C11.4475 13.5554 10.8643 12.5219 10.8643 11.6111V7.4157C10.8643 6.99616 11.2122 6.64825 11.6317 6.64825C12.0513 6.64825 12.3992 6.99616 12.3992 7.4157V11.6111C12.3992 11.9795 12.7061 12.5219 13.0234 12.706L16.1955 14.5991C16.5639 14.814 16.6764 15.2847 16.4615 15.6531C16.3081 15.8987 16.0522 16.0317 15.7964 16.0317Z" fill="currentColor"></path>
                            </svg>
                        </div>
                    </div>
                    <p class="mt-2 text-xl font-semibold tabular-nums tracking-tight">{{ totalPracticeTime }}</p>
                    <p class="mt-0.5 text-[11px] text-muted-foreground">Total interview time</p>
                </div>

                <div class="surface rounded-xl border border-hairline p-4 shadow-xs">
                    <div class="flex items-center justify-between">
                        <p class="text-[11px] font-medium text-muted-foreground">Mastery</p>
                        <div class="grid h-6 w-6 place-items-center rounded-md bg-brand-soft text-brand">
                            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" width="20" height="20">
                                <path d="M9 8L9.51585 9.39406C10.1923 11.222 10.5305 12.136 11.1972 12.8028C11.864 13.4695 12.778 13.8077 14.6059 14.4841L16 15L14.6059 15.5159C12.778 16.1923 11.864 16.5305 11.1972 17.1972C10.5305 17.864 10.1923 18.778 9.51585 20.6059L9 22L8.48415 20.6059C7.80775 18.778 7.46953 17.864 6.80276 17.1972C6.13604 16.5305 5.22204 16.1923 3.39406 15.5159L2 15L3.39406 14.4841C5.22204 13.8077 6.13604 13.4695 6.80276 12.8028C7.46953 12.136 7.80775 11.222 8.48415 9.39406L9 8Z" stroke="currentColor" stroke-width="1.75" stroke-linejoin="round"></path>
                                <path d="M17 3L17.2948 3.79661C17.6813 4.84116 17.8746 5.36345 18.2556 5.74444C18.6366 6.12544 19.1588 6.31871 20.2034 6.70523L21 7L20.2034 7.29477C19.1588 7.68129 18.6366 7.87456 18.2556 8.25556C17.8746 8.63655 17.6813 9.15884 17.2948 10.2034L17 11L16.7052 10.2034C16.3187 9.15884 16.1254 8.63655 15.7444 8.25556C15.3634 7.87456 14.8412 7.68129 13.7966 7.29477L13 7L13.7966 6.70523C14.8412 6.31871 15.3634 6.12544 15.7444 5.74444C16.1254 5.36345 16.3187 4.84116 16.7052 3.79661L17 3Z" stroke="currentColor" stroke-width="1.75" stroke-linejoin="round"></path>
                            </svg>
                        </div>
                    </div>
                    <p class="mt-2 text-xl font-semibold tabular-nums tracking-tight">
                        {{ sessionStats.average_score !== null ? `${Math.round((sessionStats.average_score / 10) * 100)}%` : '—' }}
                    </p>
                    <p class="mt-0.5 text-[11px] text-muted-foreground">Derived from average score</p>
                </div>
            </section>

            <div class="">
                <section class="surface mt-5 rounded-2xl border border-hairline p-5 shadow-xs">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <h3 class="text-sm font-semibold tracking-tight">Score trajectory</h3>
                            <p class="text-[11px] text-muted-foreground">Based on all completed sessions over time</p>
                        </div>

                        <div v-if="scoreTrajectory.trend" class="flex shrink-0 items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-medium tabular-nums" :class="scoreTrendBadgeClass">
                            <svg v-if="scoreTrajectory.trend.direction === 'up'" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-3 w-3" aria-hidden="true">
                                <path d="M16 7h6v6"></path>
                                <path d="m22 7-8.5 8.5-5-5L2 17"></path>
                            </svg>

                            <svg v-else-if="scoreTrajectory.trend.direction === 'down'" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-3 w-3" aria-hidden="true">
                                <path d="M16 17h6v-6"></path>
                                <path d="m22 17-8.5-8.5-5 5L2 7"></path>
                            </svg>

                            <svg v-else xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-3 w-3" aria-hidden="true">
                                <path d="M5 12h14"></path>
                            </svg>

                            {{ scoreTrajectory.trend.label }}
                        </div>
                    </div>

                    <ScoreTrajectoryApexChart v-if="hasTrajectoryPoints && hasTrajectoryScores" :points="scoreTrajectory.points" />
                    <p v-else-if="!hasTrajectoryPoints" class="mt-5 text-xs text-muted-foreground">Complete interview sessions with feedback to see your score trend.</p>
                    <p v-else class="mt-5 text-xs text-muted-foreground">Scores will plot here once feedback exists for your completed sessions.</p>
                </section>
            </div>

            <section class="mt-6">
                <template v-if="recentSessions.length > 0">
                    <div class="mb-2 flex items-center justify-between px-1">
                        <div>
                            <h3 class="text-sm font-semibold tracking-tight">Recent sessions</h3>
                            <p class="text-[11px] text-muted-foreground">Track your latest progress and feedbacks</p>
                        </div>
                        <TextLink :href="route('user.feedback.index')" class="text-sm font-medium text-muted-foreground hover:text-foreground">See all</TextLink>
                    </div>

                    <div class="surface divide-y divide-hairline overflow-hidden rounded-2xl border border-hairline shadow-xs">
                        <TextLink v-for="session in recentSessions" :key="session.id" :href="route('user.feedback.show', { session: session.id })" class="flex items-center gap-3 px-4 py-3.5 transition active:bg-surface-2">
                            <div class="grid h-9 w-9 shrink-0 place-items-center rounded-lg bg-brand-soft text-brand">
                                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" width="20" height="20">
                                    <path d="M12 19C15.31 19 18 16.31 18 13V8C18 4.69 15.31 2 12 2C8.69 2 6 4.69 6 8V13C6 16.31 8.69 19 12 19Z" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"></path>
                                    <path d="M3 11V13C3 17.97 7.03 22 12 22C16.97 22 21 17.97 21 13V11" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"></path>
                                    <path d="M9.11011 7.47999C10.8901 6.82999 12.8301 6.82999 14.6101 7.47999" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"></path>
                                    <path d="M10.03 10.48C11.23 10.15 12.5 10.15 13.7 10.48" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"></path>
                                </svg>
                            </div>

                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-medium">{{ session.job_role }}</p>
                                <p class="flex items-center gap-1.5 text-[11px] text-muted-foreground">
                                    <span>{{ sessionInterviewTypeLabel(session.interview_type) }}</span>
                                    <span>·</span>
                                    <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em">
                                        <path d="M7.89767 5.49011C7.47714 5.49011 7.12842 5.14138 7.12842 4.72086V1.64386C7.12842 1.22334 7.47714 0.874615 7.89767 0.874615C8.31819 0.874615 8.66692 1.22334 8.66692 1.64386V4.72086C8.66692 5.14138 8.31819 5.49011 7.89767 5.49011Z" fill="currentColor"></path>
                                        <path d="M16.1027 5.49011C15.6822 5.49011 15.3335 5.14138 15.3335 4.72086V1.64386C15.3335 1.22334 15.6822 0.874615 16.1027 0.874615C16.5233 0.874615 16.872 1.22334 16.872 1.64386V4.72086C16.872 5.14138 16.5233 5.49011 16.1027 5.49011Z" fill="currentColor"></path>
                                        <path d="M20.7178 9.68492H3.28146C2.86093 9.68492 2.51221 9.33619 2.51221 8.91567C2.51221 8.49515 2.86093 8.14642 3.28146 8.14642H20.7178C21.1383 8.14642 21.487 8.49515 21.487 8.91567C21.487 9.33619 21.1383 9.68492 20.7178 9.68492Z" fill="currentColor"></path>
                                        <path d="M16.1024 22.9264H7.89709C4.15341 22.9264 1.99951 20.7725 1.99951 17.0288V8.31069C1.99951 4.56701 4.15341 2.41311 7.89709 2.41311H16.1024C19.8461 2.41311 22 4.56701 22 8.31069V17.0288C22 20.7725 19.8461 22.9264 16.1024 22.9264ZM7.89709 3.95161C4.96369 3.95161 3.53801 5.37729 3.53801 8.31069V17.0288C3.53801 19.9623 4.96369 21.3879 7.89709 21.3879H16.1024C19.0358 21.3879 20.4615 19.9623 20.4615 17.0288V8.31069C20.4615 5.37729 19.0358 3.95161 16.1024 3.95161H7.89709Z" fill="currentColor"></path>
                                        <path d="M8.41043 14.4647C8.27709 14.4647 8.14376 14.4339 8.02068 14.3826C7.8976 14.3313 7.78479 14.2595 7.68222 14.1672C7.58991 14.0647 7.51809 13.9518 7.46681 13.8288C7.41553 13.7057 7.38477 13.5724 7.38477 13.439C7.38477 13.1723 7.4976 12.9057 7.68222 12.7108C7.78479 12.6185 7.8976 12.5467 8.02068 12.4954C8.2053 12.4134 8.41044 12.3928 8.61557 12.4339C8.67711 12.4441 8.73864 12.4646 8.80018 12.4954C8.86172 12.5159 8.92326 12.5467 8.9848 12.5877C9.03609 12.6287 9.08736 12.6698 9.13864 12.7108C9.17967 12.7621 9.23095 12.8134 9.26172 12.8646C9.30275 12.9262 9.33354 12.9877 9.35405 13.0493C9.38482 13.1108 9.40534 13.1723 9.41559 13.2339C9.42585 13.3057 9.4361 13.3672 9.4361 13.439C9.4361 13.7057 9.32326 13.9724 9.13864 14.1672C8.94377 14.3519 8.6771 14.4647 8.41043 14.4647Z" fill="currentColor"></path>
                                        <path d="M11.9998 14.4655C11.7331 14.4655 11.4664 14.3527 11.2716 14.1681C11.2305 14.1168 11.1895 14.0655 11.1485 14.0142C11.1075 13.9527 11.0767 13.8912 11.0562 13.8297C11.0254 13.7682 11.0049 13.7066 10.9946 13.6451C10.9844 13.5733 10.9741 13.5116 10.9741 13.4398C10.9741 13.3065 11.0049 13.1732 11.0562 13.0501C11.1075 12.927 11.1793 12.8143 11.2716 12.7117C11.5588 12.4245 12.0203 12.3321 12.3895 12.4962C12.5229 12.5475 12.6254 12.6194 12.728 12.7117C12.9126 12.9066 13.0255 13.1732 13.0255 13.4398C13.0255 13.5116 13.0152 13.5733 13.0049 13.6451C12.9947 13.7066 12.9742 13.7682 12.9434 13.8297C12.9229 13.8912 12.8921 13.9527 12.8511 14.0142C12.8101 14.0655 12.769 14.1168 12.728 14.1681C12.6254 14.2604 12.5229 14.3323 12.3895 14.3836C12.2665 14.4348 12.1331 14.4655 11.9998 14.4655Z" fill="currentColor"></path>
                                        <path d="M15.0262 14.4655C14.7595 14.4655 14.4928 14.3527 14.2979 14.1681C14.2569 14.1168 14.2159 14.0655 14.1749 14.0142C14.1338 13.9527 14.1031 13.8912 14.0825 13.8297C14.0518 13.7682 14.0313 13.7066 14.021 13.6451C14.0107 13.5733 14.0005 13.5116 14.0005 13.4398C14.0005 13.3065 14.0313 13.1732 14.0825 13.0501C14.1338 12.927 14.2056 12.8143 14.2979 12.7117C14.5851 12.4245 15.0467 12.3321 15.4159 12.4962C15.5492 12.5475 15.6518 12.6194 15.7544 12.7117C15.939 12.9066 16.0518 13.1732 16.0518 13.4398C16.0518 13.5116 16.0416 13.5733 16.0313 13.6451C16.021 13.7066 16.0005 13.7682 15.9698 13.8297C15.9493 13.8912 15.9185 13.9527 15.8775 14.0142C15.8364 14.0655 15.7954 14.1168 15.7544 14.1681C15.6518 14.2604 15.5492 14.3323 15.4159 14.3836C15.2928 14.4348 15.1595 14.4655 15.0262 14.4655Z" fill="currentColor"></path>
                                        <path d="M12.0257 17.9251C11.759 17.9251 11.4923 17.8123 11.2974 17.6277C11.2564 17.5764 11.2154 17.5251 11.1744 17.4738C11.1333 17.4123 11.1026 17.3509 11.0821 17.2893C11.0513 17.2278 11.0308 17.1662 11.0205 17.1047C11.0103 17.0329 11 16.9713 11 16.8995C11 16.7661 11.0308 16.6328 11.0821 16.5097C11.1333 16.3866 11.2051 16.2739 11.2974 16.1714C11.5846 15.8842 12.0462 15.7918 12.4154 15.9559C12.5488 16.0071 12.6513 16.079 12.7539 16.1714C12.9385 16.3662 13.0513 16.6328 13.0513 16.8995C13.0513 16.9713 13.0411 17.0329 13.0308 17.1047C13.0206 17.1662 13 17.2278 12.9693 17.2893C12.9488 17.3509 12.918 17.4123 12.877 17.4738C12.8359 17.5251 12.7949 17.5764 12.7539 17.6277C12.6513 17.72 12.5488 17.7919 12.4154 17.8432C12.2923 17.8945 12.159 17.9251 12.0257 17.9251Z" fill="currentColor"></path>
                                        <path d="M8.41043 18.0553C8.27709 18.0553 8.14376 18.0247 8.02068 17.9734C7.8976 17.9221 7.78479 17.8502 7.68222 17.7579C7.58991 17.6553 7.51809 17.5529 7.46681 17.4195C7.41553 17.2964 7.38477 17.163 7.38477 17.0297C7.38477 16.763 7.4976 16.4964 7.68222 16.3015C7.78479 16.2092 7.8976 16.1373 8.02068 16.0861C8.40017 15.9219 8.85146 16.0144 9.13864 16.3015C9.17967 16.3528 9.23095 16.4041 9.26172 16.4554C9.30275 16.5169 9.33354 16.5784 9.35405 16.6399C9.38482 16.7015 9.40534 16.763 9.41559 16.8348C9.42585 16.8963 9.4361 16.9681 9.4361 17.0297C9.4361 17.2963 9.32326 17.563 9.13864 17.7579C8.94377 17.9425 8.6771 18.0553 8.41043 18.0553Z" fill="currentColor"></path>
                                    </svg>
                                    <span>{{ session.ended_at_human ?? '—' }}</span>
                                    <span>·</span>
                                    <span>{{ Math.max(1, Math.round(session.duration_seconds / 60)) }}m</span>
                                </p>
                            </div>

                            <div class="text-right">
                                <p class="text-sm font-semibold tabular-nums">{{ session.overall_score !== null ? session.overall_score.toFixed(1) : '—' }}</p>
                            </div>
                        </TextLink>
                    </div>
                </template>

                <template v-else>
                    <NotFoundEmptyState title="No recent sessions yet" description="Start your first interview to build your performance history and unlock feedback.">
                        <TextLink :href="route('user.interview.index')" class="inline-flex items-center gap-1.5 rounded-full bg-foreground px-4 py-2 text-xs font-medium text-background shadow-sm transition active:scale-[0.98]">
                            Begin interview
                        </TextLink>
                    </NotFoundEmptyState>
                </template>
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
