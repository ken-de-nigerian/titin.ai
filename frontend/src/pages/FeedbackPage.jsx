import { Link } from "@tanstack/react-router";
import { motion } from "framer-motion";
import { ArrowRight } from "lucide-react";
import { SiteHeader } from "@/components/SiteHeader";
import { SnapCarousel } from "@/components/SnapCarousel";

const strengths = [
  "Clear structure: situation, action, result",
  "Concrete metrics in the impact section",
  "Calm pacing under follow-up pressure",
];
const growth = [
  "Lead with outcome, then unpack the process",
  "Trim filler phrases — 'kind of', 'I guess'",
  "Anchor stories in business value, not tasks",
];

const improved = [
  {
    q: "Tell me about a time you led under ambiguity.",
    a: "I drove a 32% reduction in checkout latency by leading a six-week migration with no formal spec. I started by defining three success metrics with the CFO, then shipped in three controlled phases.",
  },
  {
    q: "What's a hard tradeoff you've made?",
    a: "I cut a feature my team had built for two months. It tested poorly. I owned the call publicly, redirected scope to retention work, and we lifted DAU by 11% the next quarter.",
  },
  {
    q: "How do you handle disagreement with a peer?",
    a: "I separate the decision from the relationship. I write the strongest version of their argument, then mine. We meet on facts, not feelings — and document the call.",
  },
];

export default function FeedbackPage() {
  const score = 8.6;

  return (
    <div className="min-h-screen">
      <SiteHeader />

      <main className="mx-auto max-w-6xl px-6 py-16 md:py-24">
        <div className="grid gap-10 border-b border-hairline pb-16 md:grid-cols-12 md:items-end">
          <div className="md:col-span-7">
            <p className="text-xs uppercase tracking-[0.25em] text-muted-foreground">Session complete</p>
            <h1 className="mt-6 text-4xl tracking-tight md:text-6xl">
              Senior PM <span className="font-serif italic text-muted-foreground">/ Behavioral</span>
            </h1>
            <p className="mt-5 max-w-md text-sm text-muted-foreground">
              24 minutes · 6 questions · Strong session. You're in the top 18% for this role and topic.
            </p>
          </div>

          <div className="md:col-span-5 md:text-right">
            <p className="text-xs uppercase tracking-[0.25em] text-muted-foreground">Overall</p>
            <motion.div
              initial={{ opacity: 0, y: 8 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ duration: 0.6 }}
              className="mt-4 flex items-baseline gap-3 md:justify-end"
            >
              <span className="text-7xl tracking-tight tabular-nums md:text-8xl">{score}</span>
              <span className="text-xl text-muted-foreground tabular-nums">/ 10</span>
            </motion.div>
          </div>
        </div>

        <div className="mt-20 grid gap-px bg-hairline md:grid-cols-2">
          <div className="bg-background p-8">
            <p className="text-xs uppercase tracking-[0.25em] text-muted-foreground">Strengths</p>
            <ul className="mt-8 space-y-5">
              {strengths.map((s, i) => (
                <li
                  key={s}
                  className="flex gap-5 border-t border-hairline pt-5 text-sm leading-relaxed first:border-0 first:pt-0"
                >
                  <span className="text-xs tabular-nums text-muted-foreground">0{i + 1}</span>
                  <span>{s}</span>
                </li>
              ))}
            </ul>
          </div>
          <div className="bg-background p-8">
            <p className="text-xs uppercase tracking-[0.25em] text-muted-foreground">Grow into</p>
            <ul className="mt-8 space-y-5">
              {growth.map((s, i) => (
                <li
                  key={s}
                  className="flex gap-5 border-t border-hairline pt-5 text-sm leading-relaxed first:border-0 first:pt-0"
                >
                  <span className="text-xs tabular-nums text-muted-foreground">0{i + 1}</span>
                  <span>{s}</span>
                </li>
              ))}
            </ul>
          </div>
        </div>

        <section className="mt-24">
          <div className="mb-12 grid gap-6 md:grid-cols-12">
            <p className="md:col-span-3 text-xs uppercase tracking-[0.25em] text-muted-foreground">
              Suggested rewrites
            </p>
            <h2 className="md:col-span-9 max-w-2xl text-3xl tracking-tight md:text-4xl">
              Improved answers,{" "}
              <span className="font-serif italic text-muted-foreground">in your voice.</span>
            </h2>
          </div>
          <SnapCarousel slideClassName="basis-[90%] md:basis-[60%] lg:basis-[50%]">
            {improved.map((item, i) => (
              <article key={i} className="surface flex h-full flex-col rounded-md p-7 min-h-[280px]">
                <div className="flex items-center justify-between text-xs text-muted-foreground">
                  <span className="uppercase tracking-[0.2em]">Question 0{i + 1}</span>
                </div>
                <p className="mt-6 text-sm">{item.q}</p>
                <div className="my-6 h-px bg-hairline" />
                <p className="text-base leading-relaxed text-muted-foreground">{item.a}</p>
              </article>
            ))}
          </SnapCarousel>
        </section>

        <div className="mt-24 flex flex-wrap justify-center gap-3 border-t border-hairline pt-12">
          <Link
            to="/interview"
            className="inline-flex items-center gap-2 rounded-full bg-foreground px-6 py-3 text-sm font-medium text-background transition hover:bg-foreground/90"
          >
            Run it again
            <ArrowRight className="h-4 w-4" />
          </Link>
          <Link
            to="/dashboard"
            className="inline-flex items-center gap-2 rounded-full border border-hairline px-6 py-3 text-sm font-medium transition hover:bg-surface"
          >
            Back to dashboard
          </Link>
        </div>
      </main>
    </div>
  );
}

