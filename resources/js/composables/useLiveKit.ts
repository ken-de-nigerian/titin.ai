import type { AgentState } from '@blockgain/livekit-vue';
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
        s.includes('initial')
    ) {
        return 'thinking';
    }

    return 'idle';
}

/**
 * Fetches a LiveKit token via the authenticated Laravel endpoint.
 */
export async function fetchLiveKitToken(
    opts?: {
        job_role?: string;
        interview_type?: string;
        question_count?: number;
        interview_mode?: string;
        duration_minutes?: number;
    },
): Promise<{
        token: string;
        room: string;
        interview_session_id: number;
        question_count: number;
        planned_duration_seconds: number;
    }> {
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

    if (typeof opts?.duration_minutes === 'number' && Number.isFinite(opts.duration_minutes)) {
        payload.duration_minutes = Math.round(opts.duration_minutes);
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

    if (typeof body?.question_count !== 'number' || ! Number.isFinite(body.question_count)) {
        throw new Error('Token response missing question count.');
    }

    if (typeof body?.planned_duration_seconds !== 'number' || ! Number.isFinite(body.planned_duration_seconds)) {
        throw new Error('Token response missing planned duration.');
    }

    return {
        token: body.token,
        room: body.room,
        interview_session_id: body.interview_session_id,
        question_count: body.question_count,
        planned_duration_seconds: body.planned_duration_seconds,
    };
}

/**
 * Reactive interview session state — used outside the LiveKitRoom context
 * to manage the token and connection lifecycle.
 */
function liveKitServerUrl(): string {
    const raw = import.meta.env.VITE_LIVEKIT_URL;

    return typeof raw === 'string' ? raw.trim() : '';
}

export function useInterviewSession() {
    const token = ref<string | null>(null);
    const interviewSessionId = ref<number | null>(null);
    const questionCount = ref<number | null>(null);
    const plannedDurationSeconds = ref<number | null>(null);
    const serverUrl = liveKitServerUrl();
    const isConnecting = ref(false);
    const connectError = ref<string | null>(null);

    async function connect(
        opts?: {
            job_role?: string;
            interview_type?: string;
            question_count?: number;
            interview_mode?: string;
            concise_feedback?: boolean;
            duration_minutes?: number;
        },
    ): Promise<void> {
        isConnecting.value = true;
        connectError.value = null;

        if (serverUrl === '') {
            const message = 'Interview room is not configured (missing VITE_LIVEKIT_URL). Check your environment and rebuild the frontend.';
            connectError.value = message;

            throw new Error(message);
        }

        try {
            const response = await fetchLiveKitToken({
                job_role: opts?.job_role,
                interview_type: opts?.interview_type,
                question_count: opts?.question_count,
                interview_mode: opts?.interview_mode,
                duration_minutes: opts?.duration_minutes,
            });
            token.value = response.token;
            interviewSessionId.value = response.interview_session_id;
            questionCount.value = response.question_count;
            plannedDurationSeconds.value = response.planned_duration_seconds;
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
        questionCount.value = null;
        plannedDurationSeconds.value = null;
        connectError.value = null;
    }

    const isConnected = computed(() => token.value !== null);

    return {
        token,
        interviewSessionId,
        questionCount,
        plannedDurationSeconds,
        serverUrl,
        isConnecting,
        connectError,
        isConnected,
        connect,
        disconnect,
    };
}
