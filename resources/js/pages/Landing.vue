<script setup lang="ts">
    import { Head, Link } from '@inertiajs/vue3';
    import { ArrowRight, Check, Mic, BarChart3, Sparkles, Target, Clock, Shield, Star, ChevronDown, Play } from 'lucide-vue-next';
    import { useWindowSize } from '@vueuse/core';
    import { computed, ref } from 'vue';

    import Orb from '@/components/Orb.vue';
    import ProductMockSession from '@/components/ProductMockSession.vue';
    import SiteFooter from '@/components/SiteFooter.vue';
    import SiteHeader from '@/components/SiteHeader.vue';

    const logos = ['Stripe', 'Linear', 'Notion', 'Figma', 'Vercel', 'Ramp'];

    const features = [
        { icon: Mic, title: 'Real voice conversations', desc: 'Speak naturally. The interviewer listens, follows up, and adapts to where you take the answer.' },
        { icon: BarChart3, title: 'Structured feedback', desc: 'Per-answer scoring across clarity, structure, depth, and impact. No vague compliments.' },
        { icon: Target, title: 'Role-calibrated', desc: 'From staff engineer to brand director — the right questions at the right bar.' },
        { icon: Sparkles, title: 'Improved answers', desc: 'Get rewrites in your voice, ready to rehearse before the real thing.' },
        { icon: Clock, title: 'Fits your schedule', desc: '10-minute warmups or full 45-minute loops. Practice on your own time.' },
        { icon: Shield, title: 'Private by default', desc: 'Your sessions are yours. Encrypted, never used to train models.' },
    ];

    const steps = [
        { n: '01', title: 'Pick a role', desc: 'Choose your role, level, and the type of interview — behavioral, system design, case, or portfolio.' },
        { n: '02', title: 'Talk it through', desc: 'Have a real spoken conversation. The AI follows up like a hiring manager would.' },
        { n: '03', title: 'Get the report', desc: 'Within seconds: scores, strengths, growth areas, and rewritten answers in your voice.' },
    ];

    const testimonials = [
        { quote: "I did six sessions before my Stripe loop. Got the offer. Lumen is the closest thing to a real interview I've used.", name: 'Maya R.', role: 'Senior PM, fintech' },
        { quote: "The feedback is brutal in the best way. It caught things my prep partner missed for a year.", name: 'Daniel K.', role: 'Staff Engineer' },
        { quote: "Voice changes everything. Typing answers never felt like practice — this does.", name: 'Priya S.', role: 'Engineering Manager' },
        { quote: "Used it daily for two weeks. My structured-storytelling score went from 6.1 to 8.7.", name: 'Jordan M.', role: 'Product Designer' },
    ];

    const tiers = [
        {
            name: 'Free',
            price: '$0',
            cadence: 'forever',
            desc: 'For trying it out before a single interview.',
            features: ['3 sessions / month', 'Behavioral track', 'Basic feedback report', 'Single role'],
            cta: 'Start free',
            featured: false,
        },
        {
            name: 'Pro',
            price: '$24',
            cadence: 'per month',
            desc: 'For active job seekers preparing for real loops.',
            features: ['Unlimited sessions', 'All interview tracks', 'Full feedback + rewrites', 'Progress tracking', 'Voice analysis', 'Priority support'],
            cta: 'Start 7-day trial',
            featured: true,
        },
        {
            name: 'Teams',
            price: '$12',
            cadence: 'per seat / month',
            desc: 'For bootcamps, coaches, and career programs.',
            features: ['Everything in Pro', 'Cohort management', 'Coach review tools', 'Shared question banks', 'SSO + admin controls'],
            cta: 'Contact sales',
            featured: false,
        },
    ];

    const { width: windowWidth } = useWindowSize();
    const heroOrbSize = computed(() => {
        const w = windowWidth.value;
        if (w < 380) {
            return Math.max(240, w - 32);
        }
        if (w < 768) {
            return Math.min(340, Math.round(w * 0.72));
        }
        return 420;
    });

    const faqs = [
        { q: 'How is this different from typing into ChatGPT?', a: "You speak. Out loud. Under time pressure. That's the whole point — practicing the actual skill of interviewing, not the skill of writing answers.", open: ref(true) },
        { q: 'Does it work for technical interviews?', a: 'Yes. Behavioral, system design, product sense, case studies, and portfolio reviews. Coding interviews are on the roadmap.', open: ref(false) },
        { q: 'Will my recordings be used to train AI models?', a: 'No. Your audio and transcripts are encrypted and never used for training. You can delete any session at any time.', open: ref(false) },
        { q: 'Can I cancel anytime?', a: 'Yes — month-to-month, no contracts. Cancel from settings and you keep access through the end of the billing period.', open: ref(false) },
        { q: 'Do you offer student pricing?', a: 'Yes. Verified students get 50% off Pro. Email us with your .edu address.', open: ref(false) },
    ];
</script>

<template>
    <Head title="Lumen — Practice real interviews with AI" />

    <div class="min-h-screen">
        <SiteHeader />

        <!-- HERO -->
        <section class="relative overflow-hidden">
            <div class="absolute inset-0 hero-wash" />
            <div
                class="absolute inset-x-0 top-0 z-0 h-150 bg-dots opacity-40"
                style="mask-image: radial-gradient(ellipse at top, black 30%, transparent 70%)"
            />

            <div class="relative mx-auto max-w-7xl px-6 pt-20 pb-24 md:pt-28 md:pb-32">
                <div class="grid items-center gap-16 md:grid-cols-12">
                    <div class="md:col-span-7">
                        <div class="inline-flex items-center gap-2 rounded-full border border-hairline bg-surface px-3 py-1 text-xs font-medium text-muted-foreground shadow-xs">
                            <span class="h-1.5 w-1.5 rounded-full bg-brand" />
                            New · Voice analysis & filler-word coaching
                            <ArrowRight class="h-3 w-3" />
                        </div>

                        <h1 class="mt-6 text-5xl font-semibold leading-[1.05] tracking-tight md:text-6xl lg:text-7xl">
                            Practice interviews
                            <br />
                            that <span class="font-serif italic font-normal text-brand">feel real.</span>
                        </h1>

                        <p class="mt-6 max-w-xl text-lg leading-relaxed text-muted-foreground">
                            Lumen is a voice-first AI interviewer. Speak your answers, get honest, structured feedback,
                            and walk into your next loop ready.
                        </p>

                        <div class="mt-8 flex flex-wrap items-center gap-3">
                            <Link
                                :href="route('interview')"
                                class="group inline-flex items-center gap-2 rounded-lg bg-foreground px-5 py-3 text-sm font-medium text-background shadow-md transition hover:bg-foreground/90"
                            >
                                Start a free session
                                <ArrowRight class="h-4 w-4 transition group-hover:translate-x-0.5" />
                            </Link>
                            <a
                                href="#demo"
                                class="inline-flex items-center gap-2 rounded-lg border border-hairline bg-surface px-5 py-3 text-sm font-medium shadow-xs transition hover:bg-surface-2"
                            >
                                <Play class="h-3.5 w-3.5" />
                                Watch 60s demo
                            </a>
                        </div>

                        <div class="mt-8 flex items-center gap-4 text-xs text-muted-foreground">
                            <div class="flex -space-x-2">
                                <div
                                    v-for="(c, i) in ['A', 'M', 'K', 'S']"
                                    :key="i"
                                    class="grid h-7 w-7 place-items-center rounded-full border-2 border-background bg-surface-2 text-[10px] font-medium"
                                >
                                    {{ c }}
                                </div>
                            </div>
                            <div class="flex items-center gap-1">
                                <Star
                                    v-for="i in 5"
                                    :key="i"
                                    class="h-3.5 w-3.5 fill-foreground text-foreground"
                                />
                            </div>
                            <span>4.9 from 2,400+ candidates</span>
                        </div>
                    </div>

                    <div class="flex min-h-[260px] w-full items-center justify-center md:col-span-5 md:min-h-[420px]">
                        <div class="relative shrink-0">
                            <div
                                class="pointer-events-none absolute inset-[-12%] rounded-full bg-brand/15 blur-3xl md:inset-[-18%]"
                            />
                            <Orb
                                class="relative z-10 mx-auto"
                                state="idle"
                                hero-ripples
                                :size="heroOrbSize"
                            />
                        </div>
                    </div>
                </div>
            </div>

            <!-- logos -->
            <div class="relative border-y border-hairline bg-surface/50 backdrop-blur">
                <div class="mx-auto max-w-7xl px-6 py-8">
                    <p class="text-center text-xs uppercase tracking-wider text-muted-foreground">
                        Trusted by candidates interviewing at
                    </p>
                    <div class="mt-6 grid grid-cols-3 items-center gap-8 md:grid-cols-6">
                        <div
                            v-for="l in logos"
                            :key="l"
                            class="text-center text-lg font-semibold tracking-tight text-muted-foreground/70"
                        >
                            {{ l }}
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- FEATURES GRID -->
        <section
            id="features"
            class="mx-auto max-w-7xl px-6 py-24 md:py-32"
        >
            <div class="mx-auto max-w-2xl text-center">
                <p class="text-sm font-medium text-brand">Features</p>
                <h2 class="mt-3 text-4xl font-semibold tracking-tight md:text-5xl">
                    Everything you need to walk in <span class="font-serif italic font-normal text-brand">prepared.</span>
                </h2>
                <p class="mt-5 text-lg text-muted-foreground">
                    Six pieces of the puzzle, designed to remove every excuse for not practicing.
                </p>
            </div>

            <div class="mt-16 grid gap-px overflow-hidden rounded-2xl border border-hairline bg-hairline shadow-sm md:grid-cols-3">
                <div
                    v-for="f in features"
                    :key="f.title"
                    class="bg-surface p-7 transition hover:bg-surface-2"
                >
                    <div class="grid h-10 w-10 place-items-center rounded-lg bg-brand-soft text-brand">
                        <component
                            :is="f.icon"
                            class="h-5 w-5"
                        />
                    </div>
                    <h3 class="mt-5 text-base font-semibold tracking-tight">{{ f.title }}</h3>
                    <p class="mt-2 text-sm leading-relaxed text-muted-foreground">{{ f.desc }}</p>
                </div>
            </div>
        </section>

        <!-- PRODUCT MOCK -->
        <section
            id="demo"
            class="mx-auto max-w-7xl px-6 pb-24 md:pb-32"
        >
            <div class="rounded-3xl border border-hairline bg-surface-2/60 p-6 shadow-lg md:p-12">
                <div class="grid items-center gap-10 md:grid-cols-12">
                    <div class="md:col-span-5">
                        <p class="text-sm font-medium text-brand">Live session</p>
                        <h2 class="mt-3 text-3xl font-semibold tracking-tight md:text-4xl">
                            A conversation, not a quiz.
                        </h2>
                        <p class="mt-4 text-muted-foreground">
                            The interviewer listens, asks follow-ups, and only moves on when you've actually
                            answered the question. No checkboxes. No timers ticking down.
                        </p>
                        <ul class="mt-7 space-y-3 text-sm">
                            <li
                                v-for="t in [
                                    'Real-time transcription as you speak',
                                    'Adaptive follow-ups based on your answer',
                                    'Pause anytime — the AI waits with you',
                                ]"
                                :key="t"
                                class="flex items-start gap-2.5"
                            >
                                <div class="mt-0.5 grid h-4 w-4 shrink-0 place-items-center rounded-full bg-brand text-background">
                                    <Check class="h-2.5 w-2.5" />
                                </div>
                                <span class="text-foreground">{{ t }}</span>
                            </li>
                        </ul>
                        <Link
                            :href="route('interview')"
                            class="mt-8 inline-flex items-center gap-2 text-sm font-medium text-brand hover:underline"
                        >
                            Try a 5-minute session <ArrowRight class="h-4 w-4" />
                        </Link>
                    </div>

                    <div class="md:col-span-7">
                        <ProductMockSession />
                    </div>
                </div>
            </div>
        </section>

        <!-- HOW IT WORKS -->
        <section class="mx-auto max-w-7xl px-6 py-24 md:py-32">
            <div class="grid gap-12 md:grid-cols-12">
                <div class="md:col-span-4">
                    <p class="text-sm font-medium text-brand">How it works</p>
                    <h2 class="mt-3 text-4xl font-semibold tracking-tight md:text-5xl">
                        Three steps. Then you're <span class="font-serif italic font-normal text-brand">talking.</span>
                    </h2>
                    <p class="mt-5 text-muted-foreground">
                        No setup, no pretests, no calibration call. Open the app and start.
                    </p>
                </div>

                <div class="md:col-span-8">
                    <div class="space-y-3">
                        <div
                            v-for="s in steps"
                            :key="s.n"
                            class="surface flex items-start gap-6 rounded-2xl p-7 shadow-xs"
                        >
                            <span class="font-serif text-3xl text-brand">{{ s.n }}</span>
                            <div>
                                <h3 class="text-lg font-semibold tracking-tight">{{ s.title }}</h3>
                                <p class="mt-1.5 text-sm text-muted-foreground">{{ s.desc }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- TESTIMONIALS -->
        <section class="border-y border-hairline bg-surface-2/40 py-24 md:py-32">
            <div class="mx-auto max-w-7xl px-6">
                <div class="mx-auto max-w-2xl text-center">
                    <p class="text-sm font-medium text-brand">Loved by candidates</p>
                    <h2 class="mt-3 text-4xl font-semibold tracking-tight md:text-5xl">
                        People who got the offer.
                    </h2>
                </div>

                <div class="mt-14 grid gap-5 md:grid-cols-2">
                    <figure
                        v-for="(t, i) in testimonials"
                        :key="i"
                        class="surface flex flex-col rounded-2xl p-7 shadow-xs"
                    >
                        <div class="flex gap-0.5">
                            <Star
                                v-for="j in 5"
                                :key="j"
                                class="h-3.5 w-3.5 fill-foreground text-foreground"
                            />
                        </div>
                        <blockquote class="mt-5 text-lg leading-relaxed">"{{ t.quote }}"</blockquote>
                        <figcaption class="mt-6 flex items-center gap-3 border-t border-hairline pt-5">
                            <div class="grid h-9 w-9 place-items-center rounded-full bg-surface-2 text-sm font-medium">
                                {{ t.name.charAt(0) }}
                            </div>
                            <div>
                                <p class="text-sm font-medium">{{ t.name }}</p>
                                <p class="text-xs text-muted-foreground">{{ t.role }}</p>
                            </div>
                        </figcaption>
                    </figure>
                </div>
            </div>
        </section>

        <!-- PRICING -->
        <section
            id="pricing"
            class="mx-auto max-w-7xl px-6 py-24 md:py-32"
        >
            <div class="mx-auto max-w-2xl text-center">
                <p class="text-sm font-medium text-brand">Pricing</p>
                <h2 class="mt-3 text-4xl font-semibold tracking-tight md:text-5xl">
                    Simple, honest pricing.
                </h2>
                <p class="mt-5 text-lg text-muted-foreground">
                    Start free. Upgrade when you have a real interview on the calendar.
                </p>
            </div>

            <div class="mt-14 grid gap-6 md:grid-cols-3">
                <div
                    v-for="t in tiers"
                    :key="t.name"
                    :class="[
                        'relative flex flex-col rounded-2xl border p-8 shadow-sm transition',
                        t.featured
                            ? 'border-foreground bg-foreground text-background shadow-lg'
                            : 'border-hairline bg-surface',
                    ]"
                >
                    <span
                        v-if="t.featured"
                        class="absolute -top-3 left-1/2 -translate-x-1/2 rounded-full bg-brand px-3 py-1 text-[11px] font-semibold uppercase tracking-wider text-background"
                    >
                        Most popular
                    </span>
                    <h3 class="text-lg font-semibold">{{ t.name }}</h3>
                    <p :class="['mt-1 text-sm', t.featured ? 'text-background/70' : 'text-muted-foreground']">
                        {{ t.desc }}
                    </p>
                    <div class="mt-6 flex items-baseline gap-1.5">
                        <span class="text-5xl font-semibold tracking-tight tabular-nums">{{ t.price }}</span>
                        <span :class="['text-sm', t.featured ? 'text-background/70' : 'text-muted-foreground']">
                            {{ t.cadence }}
                        </span>
                    </div>

                    <Link
                        :href="route('interview')"
                        :class="[
                            'mt-7 inline-flex w-full items-center justify-center gap-2 rounded-lg px-4 py-2.5 text-sm font-medium transition',
                            t.featured
                                ? 'bg-background text-foreground hover:bg-background/90'
                                : 'bg-foreground text-background hover:bg-foreground/90',
                        ]"
                    >
                        {{ t.cta }}
                    </Link>

                    <ul class="mt-8 space-y-3 text-sm">
                        <li
                            v-for="f in t.features"
                            :key="f"
                            class="flex items-start gap-2.5"
                        >
                            <Check :class="['mt-0.5 h-4 w-4 shrink-0', t.featured ? 'text-background' : 'text-brand']" />
                            <span>{{ f }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </section>

        <!-- FAQ -->
        <section class="mx-auto max-w-4xl px-6 py-24 md:py-32">
            <div class="text-center">
                <p class="text-sm font-medium text-brand">FAQ</p>
                <h2 class="mt-3 text-4xl font-semibold tracking-tight md:text-5xl">
                    Questions, answered.
                </h2>
            </div>

            <div class="mt-12 divide-y divide-hairline rounded-2xl border border-hairline bg-surface shadow-xs">
                <div
                    v-for="(f, i) in faqs"
                    :key="i"
                    class="px-6"
                >
                    <button
                        class="flex w-full items-center justify-between gap-6 py-5 text-left"
                        @click="f.open.value = !f.open.value"
                    >
                        <span class="text-base font-medium">{{ f.q }}</span>
                        <ChevronDown
                            :class="[
                                'h-4 w-4 shrink-0 text-muted-foreground transition',
                                f.open.value ? 'rotate-180' : '',
                            ]"
                        />
                    </button>
                    <p
                        v-if="f.open.value"
                        v-fade-up
                        class="pb-5 pr-10 text-sm leading-relaxed text-muted-foreground"
                    >
                        {{ f.a }}
                    </p>
                </div>
            </div>
        </section>

        <!-- FINAL CTA -->
        <section class="mx-auto max-w-7xl px-6 pb-24">
            <div
                class="relative overflow-hidden rounded-3xl border border-hairline bg-foreground px-8 py-16 text-background shadow-xl md:px-16 md:py-24"
            >
                <div
                    class="absolute inset-0 opacity-30"
                    style="background: radial-gradient(60% 60% at 80% 20%, oklch(0.55 0.20 268 / 0.6), transparent 60%)"
                />
                <div class="relative grid items-center gap-10 md:grid-cols-12">
                    <div class="md:col-span-8">
                        <h2 class="text-4xl font-semibold tracking-tight md:text-5xl">
                            Your next interview, <span class="font-serif italic font-normal text-background/80">rehearsed.</span>
                        </h2>
                        <p class="mt-4 max-w-md text-background/70">
                            Five minutes to your first session. No card required.
                        </p>
                    </div>
                    <div class="md:col-span-4 md:text-right">
                        <Link
                            :href="route('interview')"
                            class="inline-flex items-center gap-2 rounded-lg bg-background px-6 py-3 text-sm font-semibold text-foreground transition hover:bg-background/90"
                        >
                            Start free
                            <ArrowRight class="h-4 w-4" />
                        </Link>
                    </div>
                </div>
            </div>
        </section>

        <SiteFooter />
    </div>
</template>
