import { Link } from "@tanstack/react-router";
import { useState } from "react";
import { ArrowRight } from "lucide-react";

export default function AuthPage() {
  const [mode, setMode] = useState("signin");

  return (
    <div className="relative flex min-h-screen flex-col items-center justify-center px-6 py-12">
      <Link to="/" className="absolute top-6 left-6 flex items-center gap-2">
        <div className="h-2 w-2 rounded-full bg-foreground" />
        <span className="text-sm font-medium tracking-tight">Lumen</span>
      </Link>

      <div className="w-full max-w-sm">
        <p className="text-xs uppercase tracking-[0.25em] text-muted-foreground">
          {mode === "signin" ? "Sign in" : "Create account"}
        </p>
        <h1 className="mt-6 text-3xl tracking-tight">
          {mode === "signin" ? (
            "Welcome back."
          ) : (
            <>
              Two minutes to your{" "}
              <span className="font-serif italic text-muted-foreground">first session.</span>
            </>
          )}
        </h1>

        <form onSubmit={(e) => e.preventDefault()} className="mt-12 space-y-7">
          {mode === "signup" && (
            <div className="border-b border-hairline pb-3">
              <label className="text-xs uppercase tracking-[0.2em] text-muted-foreground">Name</label>
              <input
                type="text"
                placeholder="Alex Morgan"
                className="mt-2 w-full bg-transparent text-sm outline-none placeholder:text-muted-foreground/50"
              />
            </div>
          )}
          <div className="border-b border-hairline pb-3">
            <label className="text-xs uppercase tracking-[0.2em] text-muted-foreground">Email</label>
            <input
              type="email"
              placeholder="you@company.com"
              className="mt-2 w-full bg-transparent text-sm outline-none placeholder:text-muted-foreground/50"
            />
          </div>
          <div className="border-b border-hairline pb-3">
            <label className="text-xs uppercase tracking-[0.2em] text-muted-foreground">Password</label>
            <input
              type="password"
              placeholder="••••••••"
              className="mt-2 w-full bg-transparent text-sm outline-none placeholder:text-muted-foreground/50"
            />
          </div>

          <button
            type="submit"
            className="group mt-2 flex w-full items-center justify-center gap-2 rounded-full bg-foreground px-4 py-3 text-sm font-medium text-background transition hover:bg-foreground/90"
          >
            {mode === "signin" ? "Sign in" : "Create account"}
            <ArrowRight className="h-4 w-4 transition group-hover:translate-x-0.5" />
          </button>
        </form>

        <div className="my-8 flex items-center gap-3 text-xs uppercase tracking-[0.2em] text-muted-foreground">
          <div className="h-px flex-1 bg-hairline" />
          or
          <div className="h-px flex-1 bg-hairline" />
        </div>

        <button className="w-full rounded-full border border-hairline px-4 py-3 text-sm font-medium transition hover:bg-surface">
          Continue with Google
        </button>

        <p className="mt-10 text-center text-sm text-muted-foreground">
          {mode === "signin" ? "New to Lumen?" : "Already have an account?"}{" "}
          <button
            onClick={() => setMode(mode === "signin" ? "signup" : "signin")}
            className="font-medium text-foreground underline-offset-4 hover:underline"
          >
            {mode === "signin" ? "Create account" : "Sign in"}
          </button>
        </p>
      </div>
    </div>
  );
}

