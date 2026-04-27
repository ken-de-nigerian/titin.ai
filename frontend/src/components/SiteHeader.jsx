import { Link } from "@tanstack/react-router";

export function SiteHeader() {
  return (
    <header className="sticky top-0 z-40 w-full border-b border-hairline bg-background/80 backdrop-blur-md">
      <div className="mx-auto flex max-w-6xl items-center justify-between px-6 py-4">
        <Link to="/" className="flex items-center gap-2">
          <div className="h-2 w-2 rounded-full bg-foreground" />
          <span className="text-sm font-medium tracking-tight">Lumen</span>
        </Link>
        <nav className="hidden items-center gap-8 text-sm text-muted-foreground md:flex">
          <Link
            to="/"
            activeOptions={{ exact: true }}
            activeProps={{ className: "text-foreground" }}
            className="transition hover:text-foreground"
          >
            Overview
          </Link>
          <Link
            to="/dashboard"
            activeProps={{ className: "text-foreground" }}
            className="transition hover:text-foreground"
          >
            Dashboard
          </Link>
          <Link
            to="/interview"
            activeProps={{ className: "text-foreground" }}
            className="transition hover:text-foreground"
          >
            Interview
          </Link>
        </nav>
        <Link
          to="/auth"
          className="rounded-full border border-hairline px-4 py-1.5 text-sm font-medium transition hover:bg-surface"
        >
          Sign in
        </Link>
      </div>
    </header>
  );
}

