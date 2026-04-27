import { Link } from "@tanstack/react-router";
import { motion } from "framer-motion";
import { ArrowRight } from "lucide-react";
import { Orb } from "@/components/Orb";
import { SiteHeader } from "@/components/SiteHeader";
import { SnapCarousel } from "@/components/SnapCarousel";

const features = [
  {
    n: "01",
    title: "Voice-first",
    desc: "Speak naturally. The interviewer listens, responds, and follows up — no typing, no scripts.",
  },
  {
    n: "02",
    title: "Honest feedback",
    desc: "Per-answer scoring on clarity, structure, and substance. Plain language, no padding.",
  },
  {
    n: "03",
    title: "Role-calibrated",
    desc: "From staff engineer to brand director. Real questions at the right bar.",
  },
  {
    n: "04",
    title: "Quiet progress",
    desc: "Track sessions, scores, and patterns over time. No streaks, no noise.",
  },
];

const demoTurns = [
  {
    role: "Interviewer",
    text: "Tell me about a time you led a project under significant ambiguity. What was the outcome?",
    tag: "Question",
  },
  {
    role: "You",
    text: "I owned the migration of our billing system without a formal spec. I scoped it in three phases — first defining success metrics with finance, then…",
    tag: "00:47",
  },
  {
    role: "Feedback",
    text: "Strong structure. Lead with impact next time — quantify the outcome before unpacking the process.",
    tag: "Score 8.4",
  },
];

export default function LandingPage() {
  return (
    <div className="min-h-screen">
      <SiteHeader />

      <section className="relative mx-auto max-w-6xl px-6 pt-24 pb-32 md:pt-36 md:pb-44">
        <div className="grid gap-16 md:grid-cols-12 md:gap-10">
          <div className="md:col-span-7">
            <motion.p
              initial={{ opacity: 0 }}
              animate={{ opacity: 1 }}
              transition={{ duration: 0.6 }}
              className="text-xs uppercase tracking-[0.25em] text-muted-foreground"
            >
              Lumen — voice interview practice
            </motion.p>
            <motion.h1
              initial={{ opacity: 0, y: 14 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ duration: 0.7, delay: 0.05 }}
              className="mt-8 max-w-3xl text-5xl leading-[1.02] tracking-tight md:text-7xl"
            >
              Practice real
              <br />
              interviews <span className="font-serif italic text-muted-foreground">with AI.</span>
            </motion.h1>
            <motion.p
              initial={{ opacity: 0, y: 14 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ duration: 0.7, delay: 0.15 }}
              className="mt-7 max-w-md text-base leading-relaxed text-muted-foreground"
            >
              A voice-first interviewer that listens, follows up, and tells you the truth — out loud.
            </motion.p>

            <motion.div
              initial={{ opacity: 0, y: 14 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ duration: 0.7, delay: 0.25 }}
              className="mt-10 flex flex-wrap items-center gap-3"
            >
              <Link
                to="/interview"
                className="group inline-flex items-center gap-2 rounded-full bg-foreground px-6 py-3 text-sm font-medium text-background transition hover:bg-foreground/90"
              >
                Start interview
                <ArrowRight className="h-4 w-4 transition group-hover:translate-x-0.5" />
              </Link>
              <Link
                to="/dashboard"
                className="inline-flex items-center gap-2 rounded-full border border-hairline px-6 py-3 text-sm font-medium transition hover:bg-surface"
              >
                View dashboard
              </Link>
            </motion.div>
          </div>

          <motion.div
            initial={{ opacity: 0, scale: 0.96 }}
            animate={{ opacity: 1, scale: 1 }}
            transition={{ duration: 1.2, delay: 0.2 }}
            className="md:col-span-5 flex items-center justify-center"
          >
            <Orb state="idle" size={360} />
          </motion.div>
        </div>
      </section>

      <section className="mx-auto max-w-6xl px-6 py-20 border-t border-hairline">
        <div className="mb-12 grid gap-6 md:grid-cols-12">
          <p className="md:col-span-3 text-xs uppercase tracking-[0.25em] text-muted-foreground">
            Principles
          </p>
          <h2 className="md:col-span-9 text-3xl font-normal tracking-tight md:text-4xl max-w-2xl">
            Built for serious preparation, not gamified practice.
          </h2>
        </div>

        <SnapCarousel>
          {features.map((f) => (
            <article key={f.n} className="surface flex h-full flex-col rounded-md p-7 min-h-[220px]">
              <p className="text-xs tabular-nums text-muted-foreground">{f.n}</p>
              <h3 className="mt-10 text-xl tracking-tight">{f.title}</h3>
              <p className="mt-3 text-sm leading-relaxed text-muted-foreground">{f.desc}</p>
            </article>
          ))}
        </SnapCarousel>
      </section>

      <section className="mx-auto max-w-6xl px-6 py-20 border-t border-hairline">
        <div className="mb-12 grid gap-6 md:grid-cols-12">
          <p className="md:col-span-3 text-xs uppercase tracking-[0.25em] text-muted-foreground">
            A session
          </p>
          <h2 className="md:col-span-9 text-3xl font-normal tracking-tight md:text-4xl max-w-2xl">
            A conversation,{" "}
            <span className="font-serif italic text-muted-foreground">not a quiz.</span>
          </h2>
        </div>

        <SnapCarousel slideClassName="basis-[90%] md:basis-[60%] lg:basis-[50%]">
          {demoTurns.map((t, i) => (
            <article key={i} className="surface flex h-full flex-col rounded-md p-7 min-h-[260px]">
              <div className="flex items-center justify-between text-xs text-muted-foreground">
                <span className="uppercase tracking-[0.2em]">{t.role}</span>
                <span className="tabular-nums">{t.tag}</span>
              </div>
              <p className="mt-8 text-lg leading-relaxed">{t.text}</p>
            </article>
          ))}
        </SnapCarousel>
      </section>

      <section className="mx-auto max-w-6xl px-6 py-32 border-t border-hairline">
        <div className="grid gap-10 md:grid-cols-12 md:items-end">
          <div className="md:col-span-8">
            <h2 className="text-4xl tracking-tight md:text-6xl max-w-xl">
              Your next interview,{" "}
              <span className="font-serif italic text-muted-foreground">rehearsed.</span>
            </h2>
            <p className="mt-5 max-w-sm text-muted-foreground">
              Five minutes. Real voice. Honest feedback.
            </p>
          </div>
          <div className="md:col-span-4 md:text-right">
            <Link
              to="/interview"
              className="inline-flex items-center gap-2 rounded-full bg-foreground px-6 py-3 text-sm font-medium text-background transition hover:bg-foreground/90"
            >
              Start your first session
              <ArrowRight className="h-4 w-4" />
            </Link>
          </div>
        </div>
      </section>

      <footer className="border-t border-hairline py-10">
        <div className="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-3 px-6 text-xs text-muted-foreground">
          <span>© {new Date().getFullYear()} Lumen</span>
          <span>Voice-first interview practice</span>
        </div>
      </footer>
    </div>
  );
}

