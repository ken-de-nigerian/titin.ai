import { Link } from "@tanstack/react-router";
import { ArrowRight } from "lucide-react";

export function SiteHeader() {
  return (
    <header className="sticky top-0 z-40 w-full border-b border-hairline bg-background/80 backdrop-blur-xl">
      <div className="mx-auto flex max-w-7xl items-center justify-between px-6 py-3.5">
        <Link to="/" className="flex items-center gap-2.5">
          <div className="grid h-7 w-7 place-items-center rounded-lg bg-foreground text-background">
            <svg viewBox="0 0 16 16" className="h-3.5 w-3.5" fill="none" stroke="currentColor" strokeWidth="2">
              <circle cx="8" cy="8" r="3" />
              <circle cx="8" cy="8" r="6.5" opacity="0.5" />
            </svg>
          </div>
          <span className="text-[15px] font-semibold tracking-tight">Lumen</span>
        </Link>

        <nav className="hidden items-center gap-8 text-sm text-muted-foreground md:flex">
          <Link to="/" activeOptions={{ exact: true }} activeProps={{ className: "text-foreground" }} className="transition hover:text-foreground">Product</Link>
          <a href="#features" className="transition hover:text-foreground">Features</a>
          <a href="#pricing" className="transition hover:text-foreground">Pricing</a>
          <Link to="/dashboard" activeProps={{ className: "text-foreground" }} className="transition hover:text-foreground">Dashboard</Link>
        </nav>

        <div className="flex items-center gap-2">
          <Link
            to="/auth"
            className="hidden text-sm text-muted-foreground transition hover:text-foreground md:inline-block"
          >
            Sign in
          </Link>
          <Link
            to="/interview"
            className="group inline-flex items-center gap-1.5 rounded-lg bg-foreground px-3.5 py-2 text-sm font-medium text-background shadow-sm transition hover:bg-foreground/90"
          >
            Start free
            <ArrowRight className="h-3.5 w-3.5 transition group-hover:translate-x-0.5" />
          </Link>
        </div>
      </div>
    </header>
  );
}
