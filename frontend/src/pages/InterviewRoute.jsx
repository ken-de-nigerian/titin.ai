import { useCallback, useMemo, useState } from "react";
import { LiveKitRoom, RoomAudioRenderer } from "@livekit/components-react";
import "@livekit/components-styles";
import { ConnectInterviewModal } from "@/components/ConnectInterviewModal";
import InterviewPage from "@/pages/InterviewPage";

export default function InterviewRoute() {
  const serverUrl = useMemo(() => import.meta.env.VITE_LIVEKIT_URL, []);
  const [token, setToken] = useState(null);

  const handleEnd = useCallback(() => {
    setToken(null);
  }, []);

  return (
    <div className="min-h-screen">
      {!token ? (
        <ConnectInterviewModal onClose={() => window.history.back()} onConnected={setToken} />
      ) : (
        <LiveKitRoom
          serverUrl={serverUrl}
          token={token}
          connect={true}
          video={false}
          audio={true}
          onDisconnected={handleEnd}
        >
          <RoomAudioRenderer />
          <InterviewPage onEnd={handleEnd} />
        </LiveKitRoom>
      )}
    </div>
  );
}

