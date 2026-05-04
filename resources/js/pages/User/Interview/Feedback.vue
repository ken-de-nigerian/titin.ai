<script setup lang="ts">
    import { Head, usePage } from '@inertiajs/vue3';
    import { TrendingUp } from 'lucide-vue-next';
    import { computed, ref, watch, withDefaults } from 'vue';

    import Panel from '@/components/Panel.vue';
    import ScoreRing from '@/components/ScoreRing.vue';
    import SnapCarousel from '@/components/SnapCarousel.vue';
    import AppLayout from '@/layouts/AppLayout.vue';
    import type {
        FeedbackScoreTierKey,
        FeedbackSessionPageMeta,
        SessionFeedbackPayload,
        SharedFeedbackScoreSettings,
    } from '@/types/sessionFeedback';
    import { formatFeedbackHeadlineTitle, resolveInterviewTypeLabel } from '@/utils/interviewType';

    const page = usePage();

    const props = withDefaults(
        defineProps<{
            feedback: SessionFeedbackPayload | null;
            sessionId: number | null;
            sessionMeta?: FeedbackSessionPageMeta | null;
        }>(),
        {
            sessionMeta: null,
        },
    );

    const interviewTypesMap = computed(
        () =>
            ((page.props as { settings?: { interview?: { types?: Record<string, string> } } }).settings?.interview
                ?.types ?? {}) as Record<string, string>,
    );

    const interviewModesMap = computed(
        () =>
            ((page.props as { settings?: { interview?: { modes?: Record<string, string> } } }).settings?.interview
                ?.modes ?? {}) as Record<string, string>,
    );

    function resolveInterviewModeLabel(raw: string | null | undefined): string {
        const k = String(raw ?? '').trim();

        if (k === '') {
            return '';
        }

        if (interviewModesMap.value[k]) {
            return interviewModesMap.value[k];
        }

        const hit = Object.keys(interviewModesMap.value).find((x) => x.toLowerCase() === k.toLowerCase());

        return hit !== undefined ? interviewModesMap.value[hit] : k.replaceAll('_', ' ');
    }

    const sessionContextLine = computed(() => {
        if (props.feedback?.headline_title && String(props.feedback.headline_title).trim() !== '') {
            return '';
        }

        const m = props.sessionMeta;

        if (! m) {
            return '';
        }

        const bits: string[] = [];

        if (m.job_role && m.job_role.trim() !== '') {
            bits.push(m.job_role.trim());
        }

        const typeLabel = resolveInterviewTypeLabel(m.interview_type, interviewTypesMap.value);

        if (typeLabel !== '') {
            bits.push(typeLabel);
        }

        const modeLabel = resolveInterviewModeLabel(m.interview_mode);

        if (modeLabel !== '') {
            bits.push(modeLabel);
        }

        return bits.join(' · ');
    });

    const hasScores = computed(
        () =>
            props.feedback != null &&
            props.feedback.overall_score != null &&
            (props.feedback.breakdown?.length ?? 0) > 0,
    );

    const score = computed(() =>
        props.feedback?.overall_score != null ? props.feedback.overall_score : 0,
    );

    const headlineTitle = computed(() => {
        const raw = props.feedback?.headline_title;

        if (raw === undefined || raw === null || String(raw).trim() === '') {
            return 'Practice session';
        }

        const formatted = formatFeedbackHeadlineTitle(String(raw), interviewTypesMap.value);

        return formatted !== '' ? formatted : 'Practice session';
    });

    const sessionSummaryLine = computed(
        () =>
            props.feedback?.session_summary_line ??
            'Complete a voice session to receive tailored feedback.',
    );

    const metaLine = computed(() => {
        if (props.feedback?.headline_title && String(props.feedback.headline_title).trim() !== '') {
            return `Session complete · ${headlineTitle.value}`;
        }

        return 'Session complete · Practice session';
    });

    const topInsight = computed(
        () =>
            props.feedback?.top_insight ??
            'Finish a voice session from Interview; when feedback is ready, read the debrief here — scores, gaps, and answer-level rewrites.',
    );

    const strengths = computed(() => props.feedback?.strengths ?? []);

    const growth = computed(() => props.feedback?.growth_areas ?? []);

    const improved = computed(() => props.feedback?.improved_answers ?? []);

    const insufficientCandidateInput = computed(
        () => Boolean(props.feedback?.insufficient_candidate_input),
    );

    const animatedBreakdown = ref<Array<{ label: string; value: number; animated: boolean }>>([]);

    const defaultFeedbackScoreSettings: SharedFeedbackScoreSettings = {
        score_tier_weak_below: 5,
        score_tier_strong_from: 7,
        score_tier_labels: {
            weak: 'Needs focus',
            mid: 'Mixed signal',
            strong: 'Strong signal',
        },
    };

    const feedbackScoreSettings = computed<SharedFeedbackScoreSettings>(() => {
        type RawFeedbackSettings = {
            score_tier_weak_below?: number;
            score_tier_strong_from?: number;
            score_tier_labels?: Partial<SharedFeedbackScoreSettings['score_tier_labels']>;
        };

        const raw = (page.props as { settings?: { feedback?: RawFeedbackSettings } }).settings?.feedback;

        if (raw === undefined) {
            return defaultFeedbackScoreSettings;
        }

        const labels = raw.score_tier_labels ?? {};

        return {
            score_tier_weak_below: Number(raw.score_tier_weak_below ?? defaultFeedbackScoreSettings.score_tier_weak_below),
            score_tier_strong_from: Number(raw.score_tier_strong_from ?? defaultFeedbackScoreSettings.score_tier_strong_from),
            score_tier_labels: {
                weak: String(labels.weak ?? defaultFeedbackScoreSettings.score_tier_labels.weak),
                mid: String(labels.mid ?? defaultFeedbackScoreSettings.score_tier_labels.mid),
                strong: String(labels.strong ?? defaultFeedbackScoreSettings.score_tier_labels.strong),
            },
        };
    });

    function feedbackTierKey(value: number): FeedbackScoreTierKey {
        const s = feedbackScoreSettings.value;

        if (value < s.score_tier_weak_below) {
            return 'weak';
        }

        if (value < s.score_tier_strong_from) {
            return 'mid';
        }

        return 'strong';
    }

    function breakdownBarClass(value: number): string {
        const tier = feedbackTierKey(value);

        if (tier === 'weak') {
            return 'bg-destructive';
        }

        if (tier === 'mid') {
            return 'bg-warning';
        }

        return 'bg-success';
    }

    function breakdownScoreTextClass(value: number): string {
        const tier = feedbackTierKey(value);

        if (tier === 'weak') {
            return 'text-destructive';
        }

        if (tier === 'mid') {
            return 'text-warning';
        }

        return 'text-success';
    }

    const overallScoreStatusLabel = computed(() => {
        const tier = feedbackTierKey(score.value);

        return feedbackScoreSettings.value.score_tier_labels[tier];
    });

    watch(
        () => props.feedback?.breakdown,
        (rows) => {
            animatedBreakdown.value = (rows ?? []).map((b) => ({
                label: b.label,
                value: b.value,
                animated: false,
            }));

            setTimeout(() => {
                animatedBreakdown.value = animatedBreakdown.value.map((b) => ({
                    ...b,
                    animated: true,
                }));
            }, 100);
        },
        { immediate: true, deep: true },
    );
</script>

<template>
    <Head title="Session feedback" />

    <AppLayout>
        <main class="flex-1 overflow-y-auto px-5 pb-28 md:mx-auto md:w-full md:max-w-5xl md:px-0 md:pb-10">
            <section>
                <p class="text-[10px] font-medium uppercase tracking-wider text-muted-foreground">
                    {{ metaLine }}
                </p>
                <div class="mt-4 grid gap-4 md:grid-cols-[minmax(0,1.4fr)_minmax(0,1fr)] md:items-start md:gap-6">
                    <div class="min-w-0">
                        <h1 class="text-2xl font-semibold tracking-tight md:text-3xl">
                            {{ headlineTitle }}
                        </h1>

                        <p class="mt-1.5 text-sm text-muted-foreground">
                            {{ sessionSummaryLine }}
                        </p>

                        <p v-if="sessionContextLine" class="mt-2 text-xs text-muted-foreground">
                            {{ sessionContextLine }}
                        </p>
                    </div>

                    <div class="flex shrink-0 items-center gap-4 rounded-2xl border border-hairline bg-surface p-4 shadow-xs">
                        <ScoreRing v-if="hasScores" :value="score" :stroke-class="breakdownScoreTextClass(score)" />

                        <div v-else class="grid h-24 w-24 shrink-0 place-items-center rounded-full border border-dashed border-hairline text-xs text-muted-foreground">
                            —
                        </div>

                        <div class="min-w-0">
                            <p class="text-[11px] font-medium uppercase tracking-wider text-muted-foreground">
                                Overall score
                            </p>

                            <p class="mt-1 text-3xl font-semibold tabular-nums tracking-tight md:text-4xl">
                                <template v-if="hasScores">
                                    <span :class="breakdownScoreTextClass(score)">{{ score.toFixed(1) }}</span>
                                    <span class="ml-1 text-sm font-medium text-muted-foreground">/ 10</span>
                                </template>
                                <span v-else class="text-sm font-normal text-muted-foreground">
                                    Run a session to generate scores
                                </span>
                            </p>

                            <p
                                v-if="hasScores"
                                class="mt-1 inline-flex items-center gap-1.5 text-xs font-medium"
                                :class="breakdownScoreTextClass(score)"
                            >
                                <TrendingUp class="h-3 w-3" /> {{ overallScoreStatusLabel }}
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <section class="mt-5">
                <div class="grid gap-6 lg:grid-cols-3 lg:gap-8">
                    <div class="surface rounded-2xl border border-hairline p-6 shadow-xs lg:col-span-2">
                        <h2 class="text-base font-semibold tracking-tight">Score breakdown</h2>
                        <p class="mt-0.5 text-xs text-muted-foreground">By dimension</p>

                        <div v-if="animatedBreakdown.length === 0" class="mt-6 text-sm text-muted-foreground">
                            Scores appear here after your session is analyzed.
                        </div>

                        <div class="mt-6 space-y-4">
                            <div v-for="b in animatedBreakdown" :key="b.label">
                                <div class="flex items-center justify-between text-sm">
                                    <span class="font-medium">{{ b.label }}</span>
                                    <span class="tabular-nums font-medium" :class="breakdownScoreTextClass(b.value)">
                                        {{ b.value.toFixed(1) }}
                                    </span>
                                </div>
                                <div class="mt-1.5 h-1.5 rounded-full bg-surface-2">
                                    <div
                                        class="h-1.5 rounded-full transition-all duration-700 ease-out"
                                        :class="breakdownBarClass(b.value)"
                                        :style="{ width: b.animated ? `${(b.value / 10) * 100}%` : '0%' }"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="surface rounded-2xl border border-hairline p-6 shadow-xs">
                        <h2 class="text-base font-semibold tracking-tight">Top insight</h2>
                        <p class="mt-0.5 text-xs text-muted-foreground">From this session</p>

                        <div v-if="insufficientCandidateInput" class="mt-5 rounded-xl border border-amber-500/35 bg-amber-500/10 px-3 py-2.5 text-xs leading-relaxed text-amber-950 dark:text-amber-100">
                            No substantive answers reached the scorer — microphone off, very short replies, or
                            transcriptions missed. Scores stay hidden until we have real dialogue to review.
                        </div>

                        <p class="mt-5 text-sm leading-relaxed">
                            {{ topInsight }}
                        </p>
                    </div>
                </div>
            </section>

            <section class="mt-5">
                <div class="grid gap-6 md:grid-cols-2 md:gap-8">
                    <Panel
                        v-if="strengths.length > 0"
                        title="What worked"
                        :items="strengths"
                        tone="success"
                    />

                    <div v-else class="surface rounded-2xl border border-hairline p-6 text-sm text-muted-foreground shadow-xs">
                        <h3 class="text-base font-semibold tracking-tight text-foreground">What worked</h3>
                        <p class="mt-3 leading-relaxed">
                            Strengths will list here after we analyze your session transcript.
                        </p>
                    </div>

                    <Panel
                        v-if="growth.length > 0"
                        title="Where to grow"
                        :items="growth"
                        tone="brand"
                    />

                    <div v-else class="surface rounded-2xl border border-hairline p-6 text-sm text-muted-foreground shadow-xs">
                        <h3 class="text-base font-semibold tracking-tight text-foreground">Where to grow</h3>
                        <p class="mt-3 leading-relaxed">
                            Growth areas appear when we have enough dialogue to coach against.
                        </p>
                    </div>
                </div>
            </section>

            <section class="mt-8">
                <div class="flex flex-wrap items-end justify-between gap-3 px-1">
                    <div class="max-w-2xl">
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wider text-brand">Suggested rewrites</p>
                            <h2 class="mt-2 text-2xl font-semibold tracking-tight md:text-3xl">Improved answers, in your voice</h2>
                        </div>
                    </div>
                </div>

                <div v-if="improved.length === 0" class="surface mt-4 rounded-2xl border border-hairline p-8 text-center text-sm leading-relaxed text-muted-foreground shadow-xs">
                    No upgrade rows this time — either the model did not flag weak Q&A pairs, or the transcript was
                    too thin. Run another session with fuller answers to get line-by-line rewrites.
                </div>

                <div v-if="improved.length > 0" class="mt-4 w-full min-w-0 md:hidden">
                    <SnapCarousel :show-arrows="false">
                        <article
                            v-for="(item, i) in improved"
                            :key="i"
                            :class="[
                                'surface flex min-h-0 min-w-0 shrink-0 grow-0 basis-[min(86vw,24rem)] flex-col rounded-2xl border border-hairline p-6 shadow-xs',
                                i < improved.length - 1 ? 'mr-4' : '',
                            ]"
                        >
                            <p class="text-xs font-medium uppercase tracking-wider text-muted-foreground">
                                Question {{ i + 1 }}
                            </p>
                            <p class="mt-3 text-sm font-medium">{{ item.question }}</p>

                            <div class="mt-5 flex flex-col gap-4">
                                <div class="rounded-lg border border-hairline bg-surface-2/60 p-3">
                                    <p class="text-[10px] font-medium uppercase tracking-wider text-muted-foreground">
                                        Your answer
                                    </p>
                                    <p class="mt-1.5 text-sm text-muted-foreground">{{ item.your_answer_snippet }}</p>
                                </div>

                                <div class="rounded-lg border border-brand/20 bg-brand-soft p-3">
                                    <p class="text-[10px] font-medium uppercase tracking-wider text-brand">
                                        Suggested rewrite
                                    </p>
                                    <p class="mt-1.5 text-sm leading-relaxed">{{ item.suggested_rewrite }}</p>
                                </div>
                            </div>
                        </article>
                    </SnapCarousel>
                </div>

                <div v-if="improved.length > 0" class="mt-4 hidden gap-4 md:grid md:grid-cols-2">
                    <article
                        v-for="(item, i) in improved"
                        :key="i"
                        class="surface flex flex-col rounded-2xl border border-hairline p-6 shadow-xs"
                    >
                        <p class="text-xs font-medium uppercase tracking-wider text-muted-foreground">
                            Question {{ i + 1 }}
                        </p>
                        <p class="mt-3 text-sm font-medium">{{ item.question }}</p>

                        <div class="mt-5 flex flex-col gap-4">
                            <div class="rounded-lg border border-hairline bg-surface-2/60 p-3">
                                <p class="text-[10px] font-medium uppercase tracking-wider text-muted-foreground">
                                    Your answer
                                </p>
                                <p class="mt-1.5 text-sm text-muted-foreground">{{ item.your_answer_snippet }}</p>
                            </div>

                            <div class="rounded-lg border border-brand/20 bg-brand-soft p-3">
                                <p class="text-[10px] font-medium uppercase tracking-wider text-brand">
                                    Suggested rewrite
                                </p>
                                <p class="mt-1.5 text-sm leading-relaxed">{{ item.suggested_rewrite }}</p>
                            </div>
                        </div>
                    </article>
                </div>
            </section>
        </main>
    </AppLayout>
</template>
