import { useMemo } from "react";
import { useVoiceAssistant } from "@livekit/components-react";
import Orb from "./Orb";
import SimpleVoiceAssistant from "./SimpleVoiceAssistant";
import "./InterviewRoom.css";

function toOrbState(lkState) {
  const s = String(lkState || "").toLowerCase();
  if (s.includes("speak")) return "speaking";
  if (s.includes("listen")) return "listening";
  if (s.includes("think") || s.includes("process") || s.includes("load")) return "thinking";
  return "idle";
}

export default function InterviewRoom({ onEnd }) {
  const { state, agentTranscriptions } = useVoiceAssistant();

  const latestAgentLine = useMemo(() => {
    const last = agentTranscriptions?.[agentTranscriptions.length - 1];
    return last?.text || "";
  }, [agentTranscriptions]);

  const orbState = toOrbState(state);

  return (
    <div className="irShell">
      <div className="irTopBar">
        <button type="button" className="irExit" onClick={onEnd} aria-label="End session">
          ×
        </button>
        <div className="irStatus">
          <span className="irPulseDot" />
          <span className="irStatusText">Live interview</span>
        </div>
        <div className="irSpacer" />
      </div>

      <div className="irCenter">
        <Orb state={orbState} size={340} />
        <div className="irPrompt">
          <div className="irLabel">{orbState === "speaking" ? "Interviewer" : "Session"}</div>
          <div className="irLine">
            {latestAgentLine || "When you’re ready, start speaking. The interviewer will guide you."}
          </div>
        </div>
      </div>

      <div className="irBottom">
        <SimpleVoiceAssistant />
      </div>
    </div>
  );
}

