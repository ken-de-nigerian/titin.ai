import { useCallback, useMemo, useState } from "react";
import "./ConnectInterviewModal.css";

export function ConnectInterviewModal({ onClose, onConnected }) {
  const [name, setName] = useState("");
  const [loading, setLoading] = useState(false);
  const [errorText, setErrorText] = useState("");

  const canSubmit = useMemo(() => name.trim().length > 0 && !loading, [name, loading]);

  const getToken = useCallback(async (userName) => {
    setLoading(true);
    setErrorText("");
    try {
      const response = await fetch(`/api/getToken?name=${encodeURIComponent(userName)}`);
      const bodyText = await response.text();
      if (!response.ok) {
        throw new Error(bodyText || `Token request failed (${response.status})`);
      }
      onConnected(bodyText);
    } catch (e) {
      setErrorText(e instanceof Error ? e.message : String(e));
    } finally {
      setLoading(false);
    }
  }, [onConnected]);

  const handleSubmit = (e) => {
    e.preventDefault();
    const trimmed = name.trim();
    if (!trimmed) return;
    getToken(trimmed);
  };

  return (
    <div className="cimOverlay" role="dialog" aria-modal="true">
      <div className="cimCard surface rounded-md p-7">
        <button
          type="button"
          className="cimClose"
          onClick={onClose}
          aria-label="Close"
        >
          ×
        </button>
        <p className="text-xs uppercase tracking-[0.25em] text-muted-foreground">Start session</p>
        <h2 className="mt-6 text-2xl tracking-tight">Enter your name</h2>
        <p className="mt-3 text-sm text-muted-foreground">We’ll use it as your interview identity.</p>

        <form onSubmit={handleSubmit} className="mt-10 space-y-4">
          <input
            value={name}
            onChange={(e) => setName(e.target.value)}
            placeholder="Alex Morgan"
            autoFocus
            className="w-full rounded-md border border-input bg-background px-4 py-3 text-sm outline-none placeholder:text-muted-foreground/50 focus:border-ring"
          />

          {errorText ? (
            <div className="rounded-md border border-destructive/30 bg-destructive/10 p-3 text-sm text-destructive">
              {errorText}
            </div>
          ) : null}

          <button
            type="submit"
            disabled={!canSubmit}
            className="inline-flex w-full items-center justify-center rounded-full bg-foreground px-5 py-3 text-sm font-medium text-background transition hover:bg-foreground/90 disabled:opacity-70"
          >
            {loading ? "Connecting…" : "Join interview"}
          </button>
        </form>
      </div>
    </div>
  );
}

