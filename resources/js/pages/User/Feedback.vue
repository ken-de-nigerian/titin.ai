<script setup lang="ts">
    import { Head, Link } from '@inertiajs/vue3';
    import { ArrowRight, TrendingUp, Sparkles } from 'lucide-vue-next';
    import { computed, ref, watch } from 'vue';

    import Panel from '@/components/Panel.vue';
    import ScoreRing from '@/components/ScoreRing.vue';
    import SnapCarousel from '@/components/SnapCarousel.vue';
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

    const metaLine = computed(() => {
        if (props.feedback?.headline_title) {
            return `Session complete · ${props.feedback.headline_title}`;
        }

        return 'Session complete · Practice session';
    });

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
  <Head title="Session feedback" />

  <div class="feedback">
    <!-- ── Hero (PWA shell provides header) ─────────────────── -->
    <section class="feedback-hero">
      <p class="feedback-meta">{{ metaLine }}</p>

      <div class="feedback-hero-row">
        <div class="feedback-hero-left">
          <h1 class="feedback-title">{{ headlineTitle }}</h1>
          <p class="feedback-sub">{{ sessionSummaryLine }}</p>
        </div>

        <div class="feedback-hero-right">
          <div class="feedback-score-card">
            <ScoreRing v-if="hasScores" :value="score" />
            <div v-else class="feedback-score-empty">—</div>
            <div class="feedback-score-text">
              <p class="feedback-score-label">Overall score</p>
              <p class="feedback-score-value">
                <template v-if="hasScores">
                  {{ score.toFixed(1) }}<span class="feedback-score-den">/ 10</span>
                </template>
                <span v-else class="feedback-score-none">Run a session to generate scores</span>
              </p>
              <p v-if="hasScores" class="feedback-score-ready">
                <TrendingUp class="h-3 w-3" /> Coaching ready
              </p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Breakdown + insight -->
    <section class="feedback-section">
      <div class="feedback-grid">
        <div class="surface rounded-2xl p-6 shadow-xs lg:col-span-2">
          <h2 class="text-base font-semibold tracking-tight">Score breakdown</h2>
          <p class="mt-0.5 text-xs text-muted-foreground">By dimension</p>

          <div v-if="animatedBreakdown.length === 0" class="mt-6 text-sm text-muted-foreground">
            Scores appear here after your session is analyzed.
          </div>

          <div class="mt-6 space-y-4">
            <div v-for="b in animatedBreakdown" :key="b.label">
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
    </section>

    <!-- Strengths / Growth -->
    <section class="feedback-section">
      <div class="grid gap-6 md:grid-cols-2">
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
    </section>

    <!-- Improved answers -->
    <section class="feedback-section">
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

                <!-- Mobile: snap carousel -->
                <div v-if="improved.length > 0" class="mt-6 md:hidden">
                  <SnapCarousel :show-arrows="false">
                    <article
                      v-for="(item, i) in improved"
                      :key="i"
                      class="surface w-[86vw] max-w-sm flex flex-col rounded-2xl p-6 shadow-xs"
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
                  </SnapCarousel>
                </div>

                <!-- Desktop: grid -->
                <div v-if="improved.length > 0" class="mt-6 hidden gap-4 md:grid md:grid-cols-2">
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
    <section class="feedback-cta">
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
    </section>
  </div>
</template>

<style scoped>
.feedback {
  padding: 1.75rem 0 2rem;
}

.feedback-hero {
  padding-bottom: 1.25rem;
}

.feedback-meta {
  font-size: 11px;
  letter-spacing: 0.22em;
  text-transform: uppercase;
  color: var(--muted-foreground);
  margin-bottom: 0.75rem;
}

.feedback-hero-row {
  display: grid;
  gap: 1rem;
}

.feedback-title {
  font-size: 1.75rem;
  font-weight: 500;
  letter-spacing: -0.025em;
  line-height: 1.15;
}

.feedback-sub {
  margin-top: 0.6rem;
  font-size: 14px;
  color: var(--muted-foreground);
}

.feedback-score-card {
  display: flex;
  align-items: center;
  gap: 1rem;
  border: 1px solid var(--hairline);
  background: var(--surface);
  border-radius: 14px;
  padding: 1rem;
}

.feedback-score-empty {
  width: 96px;
  height: 96px;
  border-radius: 999px;
  border: 1px dashed var(--hairline);
  display: grid;
  place-items: center;
  font-size: 12px;
  color: var(--muted-foreground);
}

.feedback-score-label {
  font-size: 11px;
  letter-spacing: 0.18em;
  text-transform: uppercase;
  color: var(--muted-foreground);
}

.feedback-score-value {
  margin-top: 0.35rem;
  font-size: 34px;
  font-weight: 500;
  letter-spacing: -0.03em;
  line-height: 1;
}

.feedback-score-den {
  font-size: 14px;
  color: var(--muted-foreground);
  margin-left: 6px;
}

.feedback-score-none {
  font-size: 14px;
  color: var(--muted-foreground);
}

.feedback-score-ready {
  margin-top: 0.5rem;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 12px;
  color: var(--success);
  font-weight: 500;
}

.feedback-section {
  margin-top: 1.25rem;
}

.feedback-grid {
  display: grid;
  gap: 1rem;
}

.feedback-cta {
  margin-top: 1.5rem;
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
  gap: 0.75rem;
}

@media (min-width: 768px) {
  .feedback {
    padding: 2.5rem 0 2.5rem;
  }

  .feedback-hero-row {
    grid-template-columns: 1.4fr 1fr;
    align-items: start;
    gap: 1.5rem;
  }

  .feedback-title {
    font-size: 2.25rem;
  }

  .feedback-grid {
    grid-template-columns: 2fr 1fr;
    gap: 1.5rem;
  }
}
</style>

<style scoped>
.feedback {
  min-height: 100%;
}

.feedback-main {
  padding: 1.75rem 1.25rem 2rem;
}

@media (min-width: 768px) {
  .feedback-main {
    padding: 2.5rem 2rem 3rem;
    max-width: 1100px;
    margin: 0 auto;
  }
}
</style>
