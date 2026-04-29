
import type { AgentState } from '@blockgain/livekit-vue';
import {
    useLiveKitRoom,
    useLocalParticipant,
    useMultibandTrackVolume,
    useVoiceAssistant,
} from '@blockgain/livekit-vue';
import { ref, computed } from 'vue';

export type OrbState = 'idle' | 'listening' | 'thinking' | 'speaking';

export function toOrbState(lkState: AgentState | string | undefined): OrbState {
    const s = String(lkState ?? '').toLowerCase();

    if (s.includes('speak')) {
        return 'speaking';
    }

    if (s.includes('listen')) {
        return 'listening';
    }

    if (
        s.includes('think') ||
        s.includes('process') ||
        s.includes('load') ||
        s.includes('connect') ||
        s.includes('initial')
    ) {
        return 'thinking';
    }

    return 'idle';
}

export { useLiveKitRoom, useLocalParticipant, useMultibandTrackVolume, useVoiceAssistant };

/**
 * Fetches a LiveKit token from the Python token server via the Vite proxy.
 */
export async function fetchLiveKitToken(
    name: string,
    opts?: { job_role?: string; interview_type?: string; concise_feedback?: boolean },
): Promise<string> {
    const params = new URLSearchParams({ name });

    if (opts?.job_role?.trim()) {
        params.set('job_role', opts.job_role.trim());
    }

    if (opts?.interview_type?.trim()) {
        params.set('interview_type', opts.interview_type.trim());
    }

    if (typeof opts?.concise_feedback === 'boolean') {
        params.set('concise_feedback', opts.concise_feedback ? '1' : '0');
    }

    const res = await fetch(`/api/getToken?${params.toString()}`);
    const body = await res.text();

    if (!res.ok) {
        throw new Error(body || `Token request failed (${res.status})`);
    }

    return body;
}

/**
 * Reactive interview session state — used outside the LiveKitRoom context
 * to manage the token and connection lifecycle.
 */
export function useInterviewSession() {
    const token = ref<string | null>(null);
    const serverUrl = import.meta.env.VITE_LIVEKIT_URL as string;
    const isConnecting = ref(false);
    const connectError = ref<string | null>(null);

    async function connect(
        name: string,
        opts?: { job_role?: string; interview_type?: string; concise_feedback?: boolean },
    ): Promise<void> {
        isConnecting.value = true;
        connectError.value = null;

        try {
            token.value = await fetchLiveKitToken(name, opts);
        } catch (e) {
            connectError.value = e instanceof Error ? e.message : String(e);

            throw e;
        } finally {
            isConnecting.value = false;
        }
    }

    function disconnect(): void {
        token.value = null;
        connectError.value = null;
    }

    const isConnected = computed(() => token.value !== null);

    return {
        token,
        serverUrl,
        isConnecting,
        connectError,
        isConnected,
        connect,
        disconnect,
    };
}
