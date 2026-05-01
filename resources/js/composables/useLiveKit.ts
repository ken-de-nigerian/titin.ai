
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
 * Fetches a LiveKit token via the authenticated Laravel endpoint.
 */
export async function fetchLiveKitToken(
    opts?: {
        job_role?: string;
        interview_type?: string;
        question_count?: number;
        interview_mode?: string;
    },
): Promise<{ token: string; room: string; interview_session_id: number }> {
    const payload: Record<string, string | number> = {};

    if (opts?.job_role?.trim()) {
        payload.job_role = opts.job_role.trim();
    }

    if (opts?.interview_type?.trim()) {
        payload.interview_type = opts.interview_type.trim();
    }

    if (typeof opts?.question_count === 'number' && Number.isFinite(opts.question_count)) {
        payload.question_count = Math.round(opts.question_count);
    }

    if (opts?.interview_mode?.trim()) {
        payload.interview_mode = opts.interview_mode.trim();
    }

    const res = await fetch('/user/interview/token', {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify(payload),
    });
    const body = await res.json().catch(() => ({}));

    if (!res.ok) {
        const message = typeof body?.message === 'string' ? body.message : `Token request failed (${res.status})`;

        throw new Error(message);
    }

    if (typeof body?.token !== 'string' || body.token.trim() === '') {
        throw new Error('Token response missing token.');
    }

    if (typeof body?.room !== 'string' || body.room.trim() === '') {
        throw new Error('Token response missing room.');
    }

    if (typeof body?.interview_session_id !== 'number') {
        throw new Error('Token response missing interview session id.');
    }

    return {
        token: body.token,
        room: body.room,
        interview_session_id: body.interview_session_id,
    };
}

/**
 * Reactive interview session state — used outside the LiveKitRoom context
 * to manage the token and connection lifecycle.
 */
export function useInterviewSession() {
    const token = ref<string | null>(null);
    const interviewSessionId = ref<number | null>(null);
    const serverUrl = import.meta.env.VITE_LIVEKIT_URL as string;
    const isConnecting = ref(false);
    const connectError = ref<string | null>(null);

    async function connect(
        opts?: {
            job_role?: string;
            interview_type?: string;
            question_count?: number;
            interview_mode?: string;
            concise_feedback?: boolean;
        },
    ): Promise<void> {
        isConnecting.value = true;
        connectError.value = null;

        try {
            const response = await fetchLiveKitToken({
                job_role: opts?.job_role,
                interview_type: opts?.interview_type,
                question_count: opts?.question_count,
                interview_mode: opts?.interview_mode,
            });
            token.value = response.token;
            interviewSessionId.value = response.interview_session_id;
        } catch (e) {
            connectError.value = e instanceof Error ? e.message : String(e);

            throw e;
        } finally {
            isConnecting.value = false;
        }
    }

    function disconnect(): void {
        token.value = null;
        interviewSessionId.value = null;
        connectError.value = null;
    }

    const isConnected = computed(() => token.value !== null);

    return {
        token,
        interviewSessionId,
        serverUrl,
        isConnecting,
        connectError,
        isConnected,
        connect,
        disconnect,
    };
}
