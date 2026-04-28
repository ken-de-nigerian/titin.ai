import { Link } from "@tanstack/react-router";

export function SiteFooter() {
  return (
    <footer className="border-t border-hairline bg-surface-2/40">
      <div className="mx-auto max-w-7xl px-6 py-16">
        <div className="grid gap-10 md:grid-cols-12">
          <div className="md:col-span-4">
            <Link to="/" className="flex items-center gap-2.5">
              <div className="grid h-7 w-7 place-items-center rounded-lg bg-foreground text-background">
                <svg viewBox="0 0 16 16" className="h-3.5 w-3.5" fill="none" stroke="currentColor" strokeWidth="2">
                  <circle cx="8" cy="8" r="3" />
                  <circle cx="8" cy="8" r="6.5" opacity="0.5" />
                </svg>
              </div>
              <span className="text-[15px] font-semibold tracking-tight">Lumen</span>
            </Link>
            <p className="mt-4 max-w-xs text-sm text-muted-foreground">
              Voice-first interview practice. Real conversations, honest feedback.
            </p>
          </div>

          <FooterCol title="Product" links={[
            { label: "Features", href: "#features" },
            { label: "Pricing", href: "#pricing" },
            { label: "Dashboard", href: "/dashboard" },
            { label: "Try a session", href: "/interview" },
          ]} />
          <FooterCol title="Company" links={[
            { label: "About", href: "#" },
            { label: "Customers", href: "#" },
            { label: "Careers", href: "#" },
            { label: "Contact", href: "#" },
          ]} />
          <FooterCol title="Resources" links={[
            { label: "Interview guides", href: "#" },
            { label: "Changelog", href: "#" },
            { label: "Help center", href: "#" },
            { label: "Status", href: "#" },
          ]} />
        </div>

        <div className="mt-14 flex flex-wrap items-center justify-between gap-4 border-t border-hairline pt-6 text-xs text-muted-foreground">
          <span>© {new Date().getFullYear()} Lumen, Inc. All rights reserved.</span>
          <div className="flex gap-6">
            <a href="#" className="hover:text-foreground">Privacy</a>
            <a href="#" className="hover:text-foreground">Terms</a>
            <a href="#" className="hover:text-foreground">Security</a>
          </div>
        </div>
      </div>
    </footer>
  );
}

function FooterCol({ title, links }: { title: string; links: { label: string; href: string }[] }) {
  return (
    <div className="md:col-span-2">
      <p className="text-xs font-semibold uppercase tracking-wider text-foreground">{title}</p>
      <ul className="mt-4 space-y-3 text-sm text-muted-foreground">
        {links.map((l) => (
          <li key={l.label}>
            <a href={l.href} className="transition hover:text-foreground">{l.label}</a>
          </li>
        ))}
      </ul>
    </div>
  );
}
