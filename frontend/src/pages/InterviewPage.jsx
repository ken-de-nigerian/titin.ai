import { useNavigate } from "@tanstack/react-router";
import { useLayoutEffect, useMemo, useRef, useState } from "react";
import { Mic } from "lucide-react";
import { AnimatePresence, motion } from "framer-motion";
import { Orb } from "@/components/Orb";
import { useIsSpeaking, useLocalParticipant, useMultibandTrackVolume, useTrackTranscription, useVoiceAssistant } from "@livekit/components-react";
import { Track } from "livekit-client";

const labels = {
  speaking: "Interviewer",
  idle: "Ready",
  listening: "Listening",
  thinking: "Thinking",
};

function toOrbState(lkState) {
  const s = String(lkState || "").toLowerCase();
  if (s.includes("speak")) return "speaking";
  if (s.includes("listen")) return "listening";
  if (s.includes("think") || s.includes("process") || s.includes("load")) return "thinking";
  return "idle";
}

export default function InterviewPage({ onEnd, roleLabel = "Senior PM · Behavioral" }) {
  const { state, agentTranscriptions, audioTrack } = useVoiceAssistant();
  const navigate = useNavigate();
  const { localParticipant, isMicrophoneEnabled, lastMicrophoneError, microphoneTrack } = useLocalParticipant();
  const agentWindowRef = useRef(null);
  const agentInnerRef = useRef(null);
  const [agentOffset, setAgentOffset] = useState(0);

  const agentBands = useMultibandTrackVolume(audioTrack, { bands: 12, loPass: 100, hiPass: 200 });
  const localMicRef = useMemo(() => {
    if (!microphoneTrack) return undefined;
    return {
      participant: localParticipant,
      source: Track.Source.Microphone,
      publication: microphoneTrack,
    };
  }, [localParticipant, microphoneTrack]);
  const localBands = useMultibandTrackVolume(localMicRef, { bands: 12, loPass: 100, hiPass: 200 });

  const energy = useMemo(() => {
    const avg = (arr) => {
      if (!arr?.length) return 0;
      return arr.reduce((a, b) => a + b, 0) / arr.length;
    };
    const combined = Math.max(avg(agentBands), avg(localBands));
    return Math.max(0, Math.min(1, combined));
  }, [agentBands, localBands]);

  const orbState = toOrbState(state);
  const isAgentSpeaking = state === "speaking";
  const isUserSpeaking = useIsSpeaking(localParticipant);

  const latestAgentLine = useMemo(() => {
    const last = agentTranscriptions?.[agentTranscriptions.length - 1];
    return last?.text || "";
  }, [agentTranscriptions]);

  useLayoutEffect(() => {
    const windowEl = agentWindowRef.current;
    const innerEl = agentInnerRef.current;
    if (!windowEl || !innerEl) return;

    const measure = () => {
      const overflow = Math.max(0, innerEl.scrollHeight - windowEl.clientHeight);
      setAgentOffset(overflow);
    };

    measure();
    const ro = new ResizeObserver(measure);
    ro.observe(windowEl);
    ro.observe(innerEl);
    return () => ro.disconnect();
  }, [latestAgentLine]);

  const { segments: userSegments } = useTrackTranscription(localMicRef);
  const latestUserLine = useMemo(() => {
    const last = userSegments?.[userSegments.length - 1];
    return last?.text || "";
  }, [userSegments]);

  return (
    <div className="relative flex min-h-screen flex-col">
      <div className="flex items-center justify-between border-b border-hairline px-6 py-5">
        <div className="h-9 w-9" />
        <div className="flex items-center gap-3 text-xs text-muted-foreground">
          <span className="h-1.5 w-1.5 animate-pulse rounded-full bg-foreground" />
          <span className="uppercase tracking-[0.2em]">{roleLabel}</span>
        </div>
        <div className="h-9 w-9" />
      </div>

      <div className="flex flex-1 flex-col items-center justify-center px-6">
        <Orb state={orbState} size={380} energy={energy} />

        <AnimatePresence mode="wait">
          <motion.div
            key={orbState + (latestAgentLine ? "t" : "e")}
            initial={{ opacity: 0, y: 6 }}
            animate={{ opacity: 1, y: 0 }}
            exit={{ opacity: 0, y: -6 }}
            transition={{ duration: 0.25 }}
            className="mt-16 max-w-2xl text-center"
          >
            <p className="text-xs uppercase tracking-[0.3em] text-muted-foreground">
              {labels[orbState] ?? "Session"}
            </p>
            <div ref={agentWindowRef} className="lkAgentFadeWindow mx-auto mt-6 max-w-xl">
              <motion.div
                ref={agentInnerRef}
                animate={{ y: -agentOffset }}
                transition={{ duration: 0.35, ease: "easeOut" }}
              >
                <p className="text-balance text-2xl leading-snug md:text-3xl">
                  {latestAgentLine || "Listening. When you’re ready, answer out loud."}
                </p>
              </motion.div>
            </div>
            {latestUserLine ? (
              <p className="mx-auto mt-6 max-w-xl text-balance text-sm leading-relaxed text-muted-foreground">
                <span className="uppercase tracking-[0.2em]">You</span> — {latestUserLine}
              </p>
            ) : null}
          </motion.div>
        </AnimatePresence>
      </div>

      <div className="flex flex-col items-center gap-5 px-6 pb-14 pt-6">
        <div className="relative">
          <motion.div
            aria-hidden="true"
            className="pointer-events-none absolute -inset-2 rounded-full border border-hairline"
            animate={{
              scale: isUserSpeaking ? [1, 1.08, 1] : isAgentSpeaking ? [1, 1.04, 1] : 1,
              opacity: isUserSpeaking ? [0.35, 0.85, 0.35] : isAgentSpeaking ? [0.25, 0.55, 0.25] : 0,
            }}
            transition={{
              duration: isUserSpeaking ? 0.9 : 1.4,
              repeat: isUserSpeaking || isAgentSpeaking ? Infinity : 0,
              ease: "easeInOut",
            }}
          />

          <button
            type="button"
            onClick={async () => {
              try {
                await localParticipant.setMicrophoneEnabled(!isMicrophoneEnabled);
              } catch (e) {
                console.error(e);
              }
            }}
            className={[
              "group relative grid h-16 w-16 place-items-center rounded-full transition hover:scale-[1.03] active:scale-100",
              isMicrophoneEnabled
                ? "bg-foreground text-background"
                : "bg-background text-foreground border border-hairline hover:bg-surface",
            ].join(" ")}
            aria-label="Toggle microphone"
          >
            <Mic className="h-6 w-6" />
          </button>
        </div>

        {lastMicrophoneError ? (
          <div className="max-w-md text-center text-xs text-destructive">
            Microphone error: {lastMicrophoneError.message}
          </div>
        ) : null}

        <button
          type="button"
          onClick={() => {
            onEnd?.();
            navigate({ to: "/feedback" });
          }}
          className="text-xs uppercase tracking-[0.25em] text-muted-foreground transition hover:text-foreground"
        >
          End session
        </button>
      </div>
    </div>
  );
}

