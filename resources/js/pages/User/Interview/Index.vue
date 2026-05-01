<script setup lang="ts">
    import { LiveKitRoom, RoomAudioRenderer } from '@blockgain/livekit-vue';
    import { Head, router, usePage } from '@inertiajs/vue3';
    import type { RoomOptions } from 'livekit-client';
    import { computed, ref, watch } from 'vue';

    import ActionButton from '@/components/ActionButton.vue';
    import InterviewRoomContent from '@/components/InterviewRoomContent.vue';
    import TextLink from '@/components/TextLink.vue';
    import { useInterviewSession } from '@/composables/useLiveKit';
    import { useRoute } from '@/composables/useRoute';
    import type { SessionEndPayload } from '@/types/sessionFeedback';

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
        default_mode?: string;
        modes?: Record<string, string>;
    });
    const interviewTypeOptions = computed(() =>
        Object.entries(interviewSettings.value.types ?? {}).map(([value, label]) => ({ value, label })),
    );
    const fallbackInterviewType = computed(
        () => interviewSettings.value.default_type ?? interviewTypeOptions.value[0]?.value ?? 'mixed',
    );
    const interviewType = ref<string>(
        interviewTypeOptions.value.some((option) => option.value === authUser.value?.interview_type)
            ? String(authUser.value?.interview_type)
            : fallbackInterviewType.value,
    );
    const interviewTypeLabel = computed(() => {
        const found = interviewTypeOptions.value.find((option) => option.value === interviewType.value);

        return found?.label ?? interviewType.value.replaceAll('_', ' ');
    });
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
    const questionCount = computed(() =>
        Math.min(
            20,
            Math.max(
                3,
                Number.isFinite(Number(interviewSettings.value.default_question_count))
                    ? Number(interviewSettings.value.default_question_count)
                    : 6,
            ),
        ),
    );
    const showConnectModal = ref(true);
    const isSubmitting = ref(false);
    const isRetryingJoin = ref(false);
    const sessionMeta = computed(() => ({
        job_role: jobRole.value.trim() || 'Interview practice',
        interview_type: interviewType.value,
        question_count: questionCount.value,
    }));
    const connectOptions = computed(() => ({
        job_role: jobRole.value,
        interview_type: interviewType.value,
        question_count: questionCount.value,
        interview_mode: interviewMode.value,
        concise_feedback: Boolean(authUser.value?.prefers_concise_feedback),
    }));

    async function handleConnect() {
        if (isSubmitting.value) {
            return;
        }

        isSubmitting.value = true;

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
        if (isRetryingJoin.value || isSubmitting.value) {
            return;
        }

        isRetryingJoin.value = true;

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
        if (!payload.interview_session_id) {
            session.disconnect();

            return;
        }

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

    const elapsed = ref(0);
    let timerInterval: ReturnType<typeof setInterval> | null = null;

    watch(
        () => session.isConnected.value,
        (connected) => {
            if (connected && !timerInterval) {
                timerInterval = setInterval(() => {
                    elapsed.value += 1;
                }, 1000);
            } else if (!connected && timerInterval) {
                clearInterval(timerInterval);
                timerInterval = null;
            }
        },
    );
</script>

<template>
    <Head title="Start Interview Session" />

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

            <h1 class="text-3xl font-display font-bold text-foreground mb-3">Ready to Begin?</h1>
            <p class="text-muted-foreground mb-2"><strong>{{ jobRole }}</strong> interview</p>
            <p class="text-sm text-muted-foreground mb-4">
                You'll be interviewed by our AI interviewer who will ask {{ interviewTypeLabel }} questions.
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
        <!-- Interview Room (only rendered when connected) -->
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
            />
        </LiveKitRoom>

        <!-- Placeholder when not connected -->
        <div v-else-if="!showConnectModal" class="flex min-h-screen items-center justify-center">
            <p class="text-muted-foreground">Connecting...</p>
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
