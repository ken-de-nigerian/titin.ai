<script setup lang="ts">
    import { Head, Link } from '@inertiajs/vue3';
    import { ArrowRight, TrendingUp, Sparkles, Download, Share2 } from 'lucide-vue-next';
    import { computed, ref, watch } from 'vue';

    import SiteHeader from '@/components/layouts/SiteHeader.vue';
    import Panel from '@/components/Panel.vue';
    import ScoreRing from '@/components/ScoreRing.vue';
    import { useRoute } from '@/composables/useRoute';
    import type { SessionFeedbackPayload } from '@/types/sessionFeedback';

    const route = useRoute();

    const props = defineProps<{
        feedback: SessionFeedbackPayload | null;
    }>();

    const hasScores = computed(
        () =>
            props.feedback != null &&
            props.feedback.overall_score != null &&
            (props.feedback.breakdown?.length ?? 0) > 0,
    );

    const score = computed(() =>
        props.feedback?.overall_score != null ? props.feedback.overall_score : 0,
    );

    const headlineTitle = computed(
        () => props.feedback?.headline_title ?? 'Practice session',
    );

    const sessionSummaryLine = computed(
        () =>
            props.feedback?.session_summary_line ??
            'Complete a voice session to receive tailored feedback.',
    );

    const topInsight = computed(
        () =>
            props.feedback?.top_insight ??
            'Start an interview from the home page. When you finish, we analyze your transcript with a premium model and surface scores, strengths, and suggested rewrites.',
    );

    const strengths = computed(() => props.feedback?.strengths ?? []);

    const growth = computed(() => props.feedback?.growth_areas ?? []);

    const improved = computed(() => props.feedback?.improved_answers ?? []);

    const animatedBreakdown = ref<Array<{ label: string; value: number; animated: boolean }>>([]);

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
    <Head title="Session feedback — Lumen" />

    <div class="min-h-screen bg-surface-2/40">
        <SiteHeader />

        <main class="mx-auto max-w-7xl px-6 py-10 md:py-14">
            <!-- Header card -->
            <div class="surface relative overflow-hidden rounded-2xl p-8 shadow-sm md:p-10">
                <div class="pointer-events-none absolute inset-0 hero-wash opacity-70" />
                <div class="relative grid gap-10 md:grid-cols-12 md:items-center">
                    <div class="md:col-span-7">
                        <div class="inline-flex items-center gap-2 rounded-full border border-hairline bg-surface px-3 py-1 text-xs font-medium shadow-xs">
                            <span class="h-1.5 w-1.5 rounded-full bg-success" />
                            Session complete
                        </div>
                        <h1 class="mt-5 text-balance text-3xl font-semibold tracking-tight md:text-5xl">
                            {{ headlineTitle }}
                        </h1>
                        <p class="mt-3 text-sm text-muted-foreground">
                            {{ sessionSummaryLine }}
                        </p>

                        <div class="mt-6 flex flex-wrap gap-2">
                            <button
                                type="button"
                                class="inline-flex items-center gap-1.5 rounded-lg border border-hairline bg-surface px-3 py-1.5 text-xs font-medium shadow-xs transition hover:bg-surface-2"
                            >
                                <Download class="h-3.5 w-3.5" />
                                Download report
                            </button>
                            <button
                                type="button"
                                class="inline-flex items-center gap-1.5 rounded-lg border border-hairline bg-surface px-3 py-1.5 text-xs font-medium shadow-xs transition hover:bg-surface-2"
                            >
                                <Share2 class="h-3.5 w-3.5" />
                                Share
                            </button>
                        </div>
                    </div>

                    <div class="md:col-span-5">
                        <div class="surface flex items-center gap-6 rounded-2xl p-6 shadow-md">
                            <ScoreRing
                                v-if="hasScores"
                                :value="score"
                            />
                            <div
                                v-else
                                class="grid h-24 w-24 shrink-0 place-items-center rounded-full border border-dashed border-hairline text-xs text-muted-foreground"
                            >
                                —
                            </div>
                            <div>
                                <p class="text-xs font-medium uppercase tracking-wider text-muted-foreground">
                                    Overall score
                                </p>
                                <p class="mt-1 flex items-baseline gap-1">
                                    <template v-if="hasScores">
                                        <span class="text-4xl font-semibold tabular-nums">{{ score.toFixed(1) }}</span>
                                        <span class="text-sm text-muted-foreground">/ 10</span>
                                    </template>
                                    <span
                                        v-else
                                        class="text-sm text-muted-foreground"
                                    >
                                        Run a session to generate scores
                                    </span>
                                </p>
                                <p
                                    v-if="hasScores"
                                    class="mt-1 inline-flex items-center gap-1 text-xs font-medium text-success"
                                >
                                    <TrendingUp class="h-3 w-3" /> Coaching ready
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Breakdown -->
            <div class="mt-6 grid gap-6 lg:grid-cols-3">
                <div class="surface rounded-2xl p-6 shadow-xs lg:col-span-2">
                    <h2 class="text-base font-semibold tracking-tight">Score breakdown</h2>
                    <p class="mt-0.5 text-xs text-muted-foreground">By dimension</p>

                    <div
                        v-if="animatedBreakdown.length === 0"
                        class="mt-6 text-sm text-muted-foreground"
                    >
                        Scores appear here after your session is analyzed.
                    </div>

                    <div class="mt-6 space-y-4">
                        <div
                            v-for="b in animatedBreakdown"
                            :key="b.label"
                        >
                            <div class="flex items-center justify-between text-sm">
                                <span class="font-medium">{{ b.label }}</span>
                                <span class="tabular-nums text-muted-foreground">{{ b.value.toFixed(1) }}</span>
                            </div>
                            <div class="mt-1.5 h-1.5 rounded-full bg-surface-2">
                                <div
                                    class="h-1.5 rounded-full bg-brand transition-all duration-700 ease-out"
                                    :style="{ width: b.animated ? `${(b.value / 10) * 100}%` : '0%' }"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="surface rounded-2xl p-6 shadow-xs">
                    <h2 class="text-base font-semibold tracking-tight">Top insight</h2>
                    <p class="mt-0.5 text-xs text-muted-foreground">From this session</p>

                    <p class="mt-5 text-sm leading-relaxed">
                        {{ topInsight }}
                    </p>

                    <Link
                        :href="route('user.interview.index')"
                        class="mt-6 inline-flex items-center gap-1.5 text-sm font-medium text-brand hover:underline"
                    >
                        Practice again <ArrowRight class="h-3.5 w-3.5" />
                    </Link>
                </div>
            </div>

            <!-- Strengths / Growth -->
            <div class="mt-6 grid gap-6 md:grid-cols-2">
                <Panel
                    v-if="strengths.length > 0"
                    title="What worked"
                    :items="strengths"
                    tone="success"
                />
                <div
                    v-else
                    class="surface rounded-2xl p-6 text-sm text-muted-foreground shadow-xs"
                >
                    <h3 class="text-base font-semibold tracking-tight text-foreground">What worked</h3>
                    <p class="mt-3 leading-relaxed">Strengths will list here after we analyze your session transcript.</p>
                </div>
                <Panel
                    v-if="growth.length > 0"
                    title="Where to grow"
                    :items="growth"
                    tone="brand"
                />
                <div
                    v-else
                    class="surface rounded-2xl p-6 text-sm text-muted-foreground shadow-xs"
                >
                    <h3 class="text-base font-semibold tracking-tight text-foreground">Where to grow</h3>
                    <p class="mt-3 leading-relaxed">Growth areas appear when we have enough dialogue to coach against.</p>
                </div>
            </div>

            <!-- Improved answers -->
            <section class="mt-10">
                <div class="flex items-end justify-between">
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wider text-brand">Suggested rewrites</p>
                        <h2 class="mt-2 text-2xl font-semibold tracking-tight md:text-3xl">
                            Improved answers, in your voice
                        </h2>
                    </div>
                    <Sparkles class="h-5 w-5 text-brand" />
                </div>

                <div
                    v-if="improved.length === 0"
                    class="surface mt-6 rounded-2xl p-8 text-center text-sm text-muted-foreground shadow-xs"
                >
                    Rewrites will appear when there is enough transcript to coach against.
                </div>

                <div class="mt-6 grid gap-4 md:grid-cols-2">
                    <article
                        v-for="(item, i) in improved"
                        :key="i"
                        class="surface flex flex-col rounded-2xl p-6 shadow-xs"
                    >
                        <p class="text-xs font-medium uppercase tracking-wider text-muted-foreground">
                            Question {{ i + 1 }}
                        </p>
                        <p class="mt-3 text-sm font-medium">{{ item.question }}</p>

                        <div class="mt-5 rounded-lg border border-hairline bg-surface-2/60 p-3">
                            <p class="text-[10px] font-medium uppercase tracking-wider text-muted-foreground">
                                Your answer
                            </p>
                            <p class="mt-1.5 text-sm text-muted-foreground">{{ item.your_answer_snippet }}</p>
                        </div>

                        <div class="mt-3 rounded-lg border border-brand/20 bg-brand-soft p-3">
                            <p class="text-[10px] font-medium uppercase tracking-wider text-brand">
                                Suggested rewrite
                            </p>
                            <p class="mt-1.5 text-sm leading-relaxed">{{ item.suggested_rewrite }}</p>
                        </div>
                    </article>
                </div>
            </section>

            <!-- CTA -->
            <div class="mt-10 flex flex-wrap items-center justify-center gap-3">
                <Link
                    :href="route('user.interview.index')"
                    class="inline-flex items-center gap-2 rounded-lg bg-foreground px-5 py-2.5 text-sm font-medium text-background shadow-sm transition hover:bg-foreground/90"
                >
                    Run it again
                    <ArrowRight class="h-4 w-4" />
                </Link>
                <Link
                    :href="route('user.dashboard')"
                    class="inline-flex items-center gap-2 rounded-lg border border-hairline bg-surface px-5 py-2.5 text-sm font-medium shadow-xs transition hover:bg-surface-2"
                >
                    Back to dashboard
                </Link>
            </div>
        </main>
    </div>
</template>
