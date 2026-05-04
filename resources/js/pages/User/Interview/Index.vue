<script setup lang="ts">
    import { LiveKitRoom, RoomAudioRenderer } from '@blockgain/livekit-vue';
    import { Head, router, usePage } from '@inertiajs/vue3';
    import type { RoomOptions } from 'livekit-client';
    import { DisconnectReason } from 'livekit-client';
    import { computed, ref, watch } from 'vue';

    import ActionButton from '@/components/ActionButton.vue';
    import InterviewRoomContent from '@/components/InterviewRoomContent.vue';
    import TextLink from '@/components/TextLink.vue';
    import { useInterviewSession } from '@/composables/useLiveKit';
    import { useRoute } from '@/composables/useRoute';
    import type { SessionEndPayload } from '@/types/sessionFeedback';
    import { derivePrimaryQuestionCountFromDuration } from '@/utils/interviewPlanning';
    import { interviewTypeOptionValuesEqual, resolveInterviewTypeLabel } from '@/utils/interviewType';

    const liveKitRoomOptions: RoomOptions = {
        adaptiveStream: true,
        dynacast: true,
        webAudioMix: true,
        audioCaptureDefaults: {
            autoGainControl: true,
            echoCancellation: true,
            noiseSuppression: true,
            channelCount: 1,
            sampleRate: 48000,
        },
    };

    const page = usePage();
    const authUser = computed(() => (page.props as any).auth?.user ?? null);
    const route = useRoute();

    const session = useInterviewSession();
    const jobRole = ref<string>((authUser.value?.job_role as string | undefined) ?? 'Software Engineer');
    const interviewSettings = computed(() => ((page.props as any).settings?.interview ?? {}) as {
        default_type?: string;
        types?: Record<string, string>;
        default_question_count?: number;
        minutes_per_primary_question?: number;
        primary_question_count_min?: number;
        primary_question_count_max?: number;
        default_mode?: string;
        modes?: Record<string, string>;
        default_duration_minutes?: number;
        min_duration_minutes?: number;
        max_duration_minutes?: number;
        duration_presets?: number[];
    });

    const interviewTypeOptions = computed(() =>
        Object.entries(interviewSettings.value.types ?? {}).map(([value, label]) => ({ value, label })),
    );
    const fallbackInterviewType = computed(
        () => interviewSettings.value.default_type ?? interviewTypeOptions.value[0]?.value ?? 'mixed',
    );
    const interviewType = ref<string>(fallbackInterviewType.value);

    watch(
        () =>
            `${String(authUser.value?.interview_type ?? '')}|${interviewTypeOptions.value.map((o) => o.value).join(',')}`,
        () => {
            const raw = authUser.value?.interview_type;
            const match = interviewTypeOptions.value.find((option) =>
                interviewTypeOptionValuesEqual(option.value, String(raw ?? '')),
            );
            interviewType.value = match?.value ?? fallbackInterviewType.value;
        },
        { immediate: true },
    );

    const interviewTypeLabel = computed(() =>
        resolveInterviewTypeLabel(interviewType.value, interviewSettings.value.types),
    );
    const interviewModeOptions = computed(() =>
        Object.entries(interviewSettings.value.modes ?? {}).map(([value, label]) => ({ value, label })),
    );
    const fallbackInterviewMode = computed(
        () => interviewSettings.value.default_mode ?? interviewModeOptions.value[0]?.value ?? 'simulation',
    );
    const interviewMode = ref<string>(
        interviewModeOptions.value.some((option) => option.value === authUser.value?.interview_mode)
            ? String(authUser.value?.interview_mode)
            : fallbackInterviewMode.value,
    );
    const effectiveDurationMinutes = computed(() => {
        const raw = authUser.value?.interview_duration_minutes;
        const minM = Number(interviewSettings.value.min_duration_minutes) || 5;
        const maxM = Number(interviewSettings.value.max_duration_minutes) || 120;

        if (raw !== null && raw !== undefined && Number.isFinite(Number(raw))) {
            return Math.min(maxM, Math.max(minM, Math.round(Number(raw))));
        }

        const def = Number(interviewSettings.value.default_duration_minutes);

        return Number.isFinite(def) ? Math.min(maxM, Math.max(minM, def)) : 25;
    });

    const interviewPlanningConfig = computed(() => ({
        minutes_per_primary_question:
            Number.isFinite(Number(interviewSettings.value.minutes_per_primary_question))
            && Number(interviewSettings.value.minutes_per_primary_question) > 0
                ? Number(interviewSettings.value.minutes_per_primary_question)
                : 2.5,
        primary_question_count_min:
            Number.isFinite(Number(interviewSettings.value.primary_question_count_min))
                ? Number(interviewSettings.value.primary_question_count_min)
                : 4,
        primary_question_count_max:
            Number.isFinite(Number(interviewSettings.value.primary_question_count_max))
                ? Number(interviewSettings.value.primary_question_count_max)
                : 20,
        default_question_count:
            Number.isFinite(Number(interviewSettings.value.default_question_count))
                ? Number(interviewSettings.value.default_question_count)
                : 6,
    }));

    const derivedQuestionCountForUi = computed(() =>
        derivePrimaryQuestionCountFromDuration(
            effectiveDurationMinutes.value,
            interviewPlanningConfig.value,
        ),
    );

    const showConnectModal = ref(true);
    const isSubmitting = ref(false);
    const isRetryingJoin = ref(false);
    const isEndingSession = ref(false);
    const interviewDropHint = ref<string | null>(null);

    const slowJoinTokenRefreshConsumed = ref(false);

    function disconnectNoticeForCode(code: number | null): string {
        if (code === DisconnectReason.DUPLICATE_IDENTITY) {
            return 'This interview was opened in another tab or window. Close the other session, then start again here.';
        }

        if (code === DisconnectReason.JOIN_FAILURE) {
            return 'Could not join the interview room (join rejected). Please try again, or return to the dashboard and start a new session.';
        }

        if (code === DisconnectReason.CONNECTION_TIMEOUT) {
            return 'The connection timed out. Check your network, then try starting the interview again.';
        }

        if (code === DisconnectReason.MEDIA_FAILURE) {
            return 'Audio failed in the browser (often a device or browser permission issue). Check your microphone permission, then try again.';
        }

        if (code === DisconnectReason.SIGNAL_CLOSE || code === DisconnectReason.SERVER_SHUTDOWN) {
            return 'The realtime service closed the connection. Please try again in a moment.';
        }

        if (code === DisconnectReason.ROOM_DELETED || code === DisconnectReason.ROOM_CLOSED) {
            return 'The interview room ended unexpectedly. You can start a new session below.';
        }

        if (code === DisconnectReason.PARTICIPANT_REMOVED) {
            return 'You were removed from the interview room. Start a new session below if you still want to practice.';
        }

        if (code === DisconnectReason.AGENT_ERROR) {
            return 'The AI interviewer encountered an error. Please try again; if it keeps happening, try again later.';
        }

        return 'The interview connection dropped. You can safely start again below — your previous attempt will not block a new session.';
    }

    function handleInterviewConnectionLost(payload: { reasonCode: number | null }): void {
        if (isEndingSession.value) {
            return;
        }

        interviewDropHint.value = disconnectNoticeForCode(payload.reasonCode);
        session.disconnect();
        showConnectModal.value = true;
    }
    const sessionMeta = computed(() => ({
        job_role: jobRole.value.trim() || 'Interview practice',
        interview_type: interviewType.value,
        question_count:
            session.questionCount.value !== null && Number.isFinite(session.questionCount.value)
                ? session.questionCount.value
                : derivedQuestionCountForUi.value,
        planned_duration_seconds:
            session.plannedDurationSeconds.value !== null && Number.isFinite(session.plannedDurationSeconds.value)
                ? session.plannedDurationSeconds.value
                : Math.round(effectiveDurationMinutes.value * 60),
    }));
    const connectOptions = computed(() => ({
        job_role: jobRole.value,
        interview_type: interviewType.value,
        interview_mode: interviewMode.value,
        concise_feedback: Boolean(authUser.value?.prefers_concise_feedback),
        duration_minutes: effectiveDurationMinutes.value,
    }));

    async function handleConnect() {
        if (isSubmitting.value) {
            return;
        }

        isSubmitting.value = true;
        interviewDropHint.value = null;

        try {
            await session.connect(connectOptions.value);
            showConnectModal.value = false;
        } catch (e) {
            console.error('Connection failed:', e);
        } finally {
            isSubmitting.value = false;
        }
    }

    async function handleRetryJoin(): Promise<void> {
        if (slowJoinTokenRefreshConsumed.value) {
            return;
        }

        if (isRetryingJoin.value || isSubmitting.value) {
            return;
        }

        slowJoinTokenRefreshConsumed.value = true;
        isRetryingJoin.value = true;
        interviewDropHint.value = null;

        try {
            session.disconnect();
            await session.connect(connectOptions.value);
            showConnectModal.value = false;
        } catch (e) {
            console.error('Retry connect failed:', e);
        } finally {
            isRetryingJoin.value = false;
        }
    }

    function handleSessionEnd(payload: SessionEndPayload) {
        if (isEndingSession.value) {
            return;
        }

        if (!payload.interview_session_id) {
            session.disconnect();

            return;
        }

        isEndingSession.value = true;

        router.post(route('user.feedback.analyze'), {
            interview_session_id: payload.interview_session_id,
            messages: payload.messages,
            duration_seconds: payload.duration_seconds,
            job_role: payload.sessionMeta.job_role,
            interview_type: payload.sessionMeta.interview_type,
            question_count: payload.sessionMeta.question_count,
        }, {
            onSuccess: () => {
                session.disconnect();
            },
            onError: () => {
                session.disconnect();
            },
            onFinish: () => {
                isEndingSession.value = false;
            },
        });
    }

    watch(
        () => session.token.value,
        (token) => {
            console.log('Token changed:', token ? 'present' : 'cleared');
        },
        { immediate: true },
    );

    watch(
        () => session.isConnected.value,
        (connected) => {
            console.log('Session connected:', connected);
        },
        { immediate: true },
    );
</script>

<template>
    <Head title="Session" />

    <div v-if="showConnectModal" class="min-h-screen bg-background flex items-center justify-center p-6">
        <div class="text-center max-w-md" style="opacity: 1; transform: none">
            <div class="flex items-center justify-center mx-auto mb-6">
                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" width="4em" height="4em">
                    <path d="M9 22H15C20 22 22 20 22 15V9C22 4 20 2 15 2H9C4 2 2 4 2 9V15C2 20 4 22 9 22Z" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"></path>
                    <path d="M7 8.25C8 9.25 9.63 9.25 10.64 8.25" stroke="currentColor" stroke-width="1.75" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                    <path d="M13.3594 8.25C14.3594 9.25 15.9894 9.25 16.9994 8.25" stroke="currentColor" stroke-width="1.75" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                    <path d="M8.4 13H15.6C16.1 13 16.5 13.4 16.5 13.9C16.5 16.39 14.49 18.4 12 18.4C9.51 18.4 7.5 16.39 7.5 13.9C7.5 13.4 7.9 13 8.4 13Z" stroke="currentColor" stroke-width="1.75" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                </svg>
            </div>

            <div v-if="interviewDropHint" class="mx-auto mb-4 max-w-md rounded-xl border border-amber-500/40 bg-amber-500/8 px-4 py-3 text-left text-sm text-foreground" role="status">
                {{ interviewDropHint }}
            </div>

            <h1 class="text-3xl font-display font-bold text-foreground mb-3">Ready to Begin?</h1>
            <p class="text-muted-foreground mb-2"><strong>{{ jobRole }}</strong> interview</p>
            <p class="text-sm text-muted-foreground mb-4">
                You'll be interviewed by our AI interviewer who will ask {{ interviewTypeLabel }} questions.
            </p>
            <p class="mx-auto mb-6 max-w-sm text-[11px] leading-relaxed text-muted-foreground">
                Session pacing uses your
                <TextLink class="underline" :href="route('user.profile.settings')">
                    Interview preferences</TextLink>:
                {{ effectiveDurationMinutes }} minutes for this profile.
                Pace is approximate — wrap up calmly if you reach that window.
            </p>

            <div class="flex items-center justify-center gap-4 mb-8 text-sm text-muted-foreground">
                <div class="flex items-center gap-2">
                    <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" width="20" height="20">
                        <path d="M12 19C15.31 19 18 16.31 18 13V8C18 4.69 15.31 2 12 2C8.69 2 6 4.69 6 8V13C6 16.31 8.69 19 12 19Z" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"></path>
                        <path d="M3 11V13C3 17.97 7.03 22 12 22C16.97 22 21 17.97 21 13V11" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"></path>
                        <path d="M9.11011 7.47999C10.8901 6.82999 12.8301 6.82999 14.6101 7.47999" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"></path>
                        <path d="M10.03 10.48C11.23 10.15 12.5 10.15 13.7 10.48" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"></path>
                    </svg>
                    <span>Voice Enabled</span>
                </div>

                <div class="flex items-center gap-2">
                    <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" width="20" height="20">
                        <path d="M3 8.25V15.75" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"></path>
                        <path d="M7.5 5.75V18.25" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"></path>
                        <path d="M12 3.25V20.75" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"></path>
                        <path d="M16.5 5.75V18.25" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"></path>
                        <path d="M21 8.25V15.75" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"></path>
                    </svg>
                    <span>AI Speaks</span>
                </div>
            </div>

            <div class="flex flex-col gap-3">
                <ActionButton
                    type="button"
                    :processing="isSubmitting"
                    class="mt-0! h-14 rounded-xl px-10 text-lg font-semibold focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                    @click="handleConnect"
                >
                    Start Interview
                </ActionButton>

                <TextLink :href="route('user.dashboard')" class="inline-flex h-10 items-center justify-center gap-2 whitespace-nowrap rounded-lg border ring-2 border-hairline bg-surface-2 px-5 py-2 text-sm font-semibold text-foreground transition hover:bg-surface-2/80 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&amp;_svg]:pointer-events-none [&amp;_svg]:size-4 [&amp;_svg]:shrink-0">
                    Cancel
                </TextLink>

                <div v-if="session.connectError.value" class="rounded-lg border border-destructive bg-destructive/10 p-3 text-sm text-destructive">
                    {{ session.connectError.value }}
                </div>
            </div>
        </div>
    </div>

    <div v-if="!showConnectModal" class="interview-shell">
        <LiveKitRoom
            v-if="session.token.value"
            :server-url="session.serverUrl"
            :token="session.token.value"
            :connect="true"
            :audio="true"
            :video="false"
            :options="liveKitRoomOptions"
        >
            <RoomAudioRenderer />
            <InterviewRoomContent
                :session-meta="sessionMeta"
                :interview-session-id="session.interviewSessionId.value ?? 0"
                @end="handleSessionEnd"
                @retry-join="handleRetryJoin"
                @connection-lost="handleInterviewConnectionLost"
            />
        </LiveKitRoom>

        <div v-else-if="!showConnectModal" class="flex min-h-screen items-center justify-center">
            <svg stroke="currentColor" fill="currentColor" stroke-width="0" version="1.1" viewBox="0 0 16 16" class="spinner animate-spin" height="4em" width="4em" xmlns="http://www.w3.org/2000/svg">
                <path d="M8 0c-4.355 0-7.898 3.481-7.998 7.812 0.092-3.779 2.966-6.812 6.498-6.812 3.59 0 6.5 3.134 6.5 7 0 0.828 0.672 1.5 1.5 1.5s1.5-0.672 1.5-1.5c0-4.418-3.582-8-8-8zM8 16c4.355 0 7.898-3.481 7.998-7.812-0.092 3.779-2.966 6.812-6.498 6.812-3.59 0-6.5-3.134-6.5-7 0-0.828-0.672-1.5-1.5-1.5s-1.5 0.672-1.5 1.5c0 4.418 3.582 8 8 8z"></path>
            </svg>
        </div>
    </div>
</template>

<style scoped>
    .interview-shell {
        height: 100dvh;
        display: flex;
        flex-direction: column;
        padding-top: env(safe-area-inset-top, 0px);
        padding-bottom: env(safe-area-inset-bottom, 0px);
    }
</style>
