import { useCallback, useMemo, useState } from "react";
import { LiveKitRoom, RoomAudioRenderer } from "@livekit/components-react";
import "@livekit/components-styles";
import InterviewRoom from "./InterviewRoom";
import "./InterviewModal.css";

export default function InterviewModal({ onClose }) {
  const [step, setStep] = useState("name"); // name | room
  const [name, setName] = useState("");
  const [token, setToken] = useState(null);
  const [loading, setLoading] = useState(false);
  const [errorText, setErrorText] = useState("");

  const serverUrl = useMemo(() => import.meta.env.VITE_LIVEKIT_URL, []);

  const getToken = useCallback(async (userName) => {
    setLoading(true);
    setErrorText("");
    try {
      const response = await fetch(`/api/getToken?name=${encodeURIComponent(userName)}`);
      const bodyText = await response.text();
      if (!response.ok) {
        throw new Error(bodyText || `Token request failed (${response.status})`);
      }
      setToken(bodyText);
      setStep("room");
    } catch (e) {
      setErrorText(e instanceof Error ? e.message : String(e));
    } finally {
      setLoading(false);
    }
  }, []);

  const handleSubmit = (e) => {
    e.preventDefault();
    const trimmed = name.trim();
    if (!trimmed || loading) return;
    getToken(trimmed);
  };

  return (
    <div className="imOverlay" role="dialog" aria-modal="true">
      <div className="imModal">
        {step === "name" ? (
          <form className="imNameForm" onSubmit={handleSubmit}>
            <button type="button" className="imClose" onClick={onClose} aria-label="Close">
              ×
            </button>
            <p className="imKicker">Start session</p>
            <h2 className="imTitle">Enter your name</h2>
            <p className="imSubTitle">We’ll use it as your interview identity.</p>

            <input
              className="imInput"
              value={name}
              onChange={(e) => setName(e.target.value)}
              placeholder="Alex Morgan"
              autoFocus
              required
            />

            {errorText ? <div className="imError">{errorText}</div> : null}

            <button className="imPrimary" type="submit" disabled={loading}>
              {loading ? "Connecting…" : "Join interview"}
            </button>
          </form>
        ) : token ? (
          <LiveKitRoom
            serverUrl={serverUrl}
            token={token}
            connect={true}
            video={false}
            audio={true}
            onDisconnected={() => {
              onClose();
            }}
          >
            <RoomAudioRenderer />
            <InterviewRoom onEnd={onClose} />
          </LiveKitRoom>
        ) : null}
      </div>
    </div>
  );
}

