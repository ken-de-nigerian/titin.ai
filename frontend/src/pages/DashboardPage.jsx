import { Link } from "@tanstack/react-router";
import { ArrowRight, ArrowUpRight } from "lucide-react";
import { SiteHeader } from "@/components/SiteHeader";

const history = [
  { role: "Senior Product Manager", track: "Behavioral", score: 8.6, date: "Today", duration: "24m" },
  { role: "Staff Engineer", track: "System design", score: 7.9, date: "Yesterday", duration: "31m" },
  { role: "Brand Designer", track: "Portfolio", score: 9.1, date: "Mar 18", duration: "18m" },
  { role: "Engineering Manager", track: "Leadership", score: 7.2, date: "Mar 16", duration: "27m" },
  { role: "Data Scientist", track: "Case study", score: 8.0, date: "Mar 12", duration: "22m" },
];

const stats = [
  { label: "Sessions", value: "23" },
  { label: "Avg score", value: "8.2" },
  { label: "This week", value: "4" },
  { label: "Total time", value: "9h 12m" },
];

export default function DashboardPage() {
  return (
    <div className="min-h-screen">
      <SiteHeader />

      <main className="mx-auto max-w-6xl px-6 py-16 md:py-24">
        <div className="grid gap-10 md:grid-cols-12 md:items-end">
          <div className="md:col-span-8">
            <p className="text-xs uppercase tracking-[0.25em] text-muted-foreground">Tuesday, March 26</p>
            <h1 className="mt-6 text-4xl tracking-tight md:text-5xl">
              Good evening, <span className="font-serif italic text-muted-foreground">Alex.</span>
            </h1>
            <p className="mt-4 max-w-md text-muted-foreground">
              Your last session ended at 8.6. Continue where you left off, or start something new.
            </p>
          </div>
          <div className="md:col-span-4 md:text-right">
            <Link
              to="/interview"
              className="group inline-flex items-center gap-2 rounded-full bg-foreground px-5 py-3 text-sm font-medium text-background transition hover:bg-foreground/90"
            >
              Start new interview
              <ArrowRight className="h-4 w-4 transition group-hover:translate-x-0.5" />
            </Link>
          </div>
        </div>

        <div className="mt-16 grid grid-cols-2 divide-x divide-y divide-hairline border border-hairline md:grid-cols-4 md:divide-y-0">
          {stats.map((s) => (
            <div key={s.label} className="p-6">
              <p className="text-xs uppercase tracking-[0.2em] text-muted-foreground">{s.label}</p>
              <p className="mt-6 text-3xl tracking-tight tabular-nums">{s.value}</p>
            </div>
          ))}
        </div>

        <section className="mt-20">
          <div className="mb-6 flex items-end justify-between border-b border-hairline pb-4">
            <h2 className="text-xl tracking-tight">Recent sessions</h2>
            <button className="text-xs uppercase tracking-[0.2em] text-muted-foreground transition hover:text-foreground">
              View all
            </button>
          </div>

          <div className="no-scrollbar -mx-6 flex snap-x snap-mandatory gap-4 overflow-x-auto px-6 pb-2">
            {history.map((h, i) => (
              <Link
                key={i}
                to="/feedback"
                className="surface group block w-[300px] shrink-0 snap-start rounded-md p-6 transition hover:border-muted-foreground/40"
              >
                <div className="flex items-center justify-between text-xs text-muted-foreground">
                  <span>{h.date}</span>
                  <span className="tabular-nums">{h.duration}</span>
                </div>
                <h3 className="mt-10 text-base leading-tight">{h.role}</h3>
                <p className="mt-1 text-sm text-muted-foreground">{h.track}</p>
                <div className="mt-12 flex items-end justify-between">
                  <div>
                    <p className="text-xs uppercase tracking-[0.2em] text-muted-foreground">Score</p>
                    <p className="mt-2 text-4xl tracking-tight tabular-nums">{h.score}</p>
                  </div>
                  <ArrowUpRight className="h-5 w-5 text-muted-foreground transition group-hover:text-foreground" />
                </div>
              </Link>
            ))}
          </div>
        </section>

        <section className="mt-20">
          <div className="mb-6 flex items-end justify-between border-b border-hairline pb-4">
            <h2 className="text-xl tracking-tight">Progress</h2>
            <p className="text-xs uppercase tracking-[0.2em] text-muted-foreground">Last 6 sessions</p>
          </div>

          <div className="grid gap-px bg-hairline md:grid-cols-3">
            <div className="bg-background p-6 md:col-span-2">
              <p className="text-xs uppercase tracking-[0.2em] text-muted-foreground">Score trajectory</p>
              <div className="mt-10 flex h-44 items-end gap-4">
                {[6.4, 7.1, 7.0, 7.9, 8.2, 8.6].map((v, i) => (
                  <div key={i} className="flex flex-1 flex-col items-center gap-3">
                    <div
                      className="w-full bg-foreground"
                      style={{ height: `${(v / 10) * 100}%`, opacity: 0.35 + (i / 6) * 0.65 }}
                    />
                    <span className="text-[10px] tabular-nums text-muted-foreground">{v.toFixed(1)}</span>
                  </div>
                ))}
              </div>
            </div>
            <div className="bg-background p-6">
              <p className="text-xs uppercase tracking-[0.2em] text-muted-foreground">Focus area</p>
              <p className="mt-10 text-2xl tracking-tight">Storytelling</p>
              <p className="mt-3 text-sm text-muted-foreground">Lead with impact. Quantify before process.</p>
              <div className="mt-10 h-px w-full bg-hairline">
                <div className="h-px bg-foreground" style={{ width: "62%" }} />
              </div>
              <p className="mt-3 text-xs tabular-nums text-muted-foreground">62% mastery</p>
            </div>
          </div>
        </section>
      </main>
    </div>
  );
}

