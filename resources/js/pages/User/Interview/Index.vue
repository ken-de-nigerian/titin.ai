<script setup lang="ts">
    import { LiveKitRoom, RoomAudioRenderer } from '@blockgain/livekit-vue';
    import { Head, router, usePage } from '@inertiajs/vue3';
    import type { RoomOptions } from 'livekit-client';
    import { computed, ref, watch } from 'vue';

    import InterviewRoomContent from '@/components/InterviewRoomContent.vue';
import CustomSelectDropdown from '@/components/CustomSelectDropdown.vue';
    import { useInterviewSession } from '@/composables/useLiveKit';
    import { useRoute } from '@/composables/useRoute';
    import type { SessionEndPayload } from '@/types/sessionFeedback';

    /** Playback / subscription defaults — does not replace TTS quality on the agent worker. */
    const liveKitRoomOptions: RoomOptions = {
        adaptiveStream: true,
        dynacast: true,
        webAudioMix: true,
    };

    const page = usePage();
    const authUser = computed(() => (page.props as any).auth?.user ?? null);
    const route = useRoute();

    const session = useInterviewSession();
    const userName = ref<string>((authUser.value?.name as string | undefined) ?? '');
    const jobRole = ref<string>((authUser.value?.job_role as string | undefined) ?? 'Software Engineer');
    const interviewSettings = computed(() => ((page.props as any).settings?.interview ?? {}) as {
        default_type?: string;
        types?: Record<string, string>;
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
    const showConnectModal = ref(true);
    const isSubmitting = ref(false);
    const sessionMeta = computed(() => ({
        job_role: jobRole.value.trim() || 'Interview practice',
        interview_type: interviewType.value,
        question_count: 6,
    }));

    async function handleConnect() {
        const trimmed = userName.value.trim();

        if (!trimmed || isSubmitting.value) {
            return;
        }

        isSubmitting.value = true;

        try {
            await session.connect(trimmed, {
                job_role: jobRole.value,
                interview_type: interviewType.value,
                concise_feedback: Boolean(authUser.value?.prefers_concise_feedback),
            });
            showConnectModal.value = false;
        } catch (e) {
            console.error('Connection failed:', e);
        } finally {
            isSubmitting.value = false;
        }
    }

    function handleSessionEnd(payload: SessionEndPayload) {
        router.post(route('user.feedback.analyze'), {
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
    <Head title="Session — Lumen" />

    <div class="interview-shell">
        <!-- Connect Modal -->
        <div
            v-if="showConnectModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-6"
            role="dialog"
            aria-modal="true"
        >
            <div class="surface w-full max-w-md rounded-2xl p-8 shadow-xl">
                <button
                    type="button"
                    class="absolute top-4 right-4 text-2xl leading-none opacity-50 transition hover:opacity-100"
                    aria-label="Close"
                    @click="router.visit('/')"
                >
                    ×
                </button>
                <p class="text-xs font-medium uppercase tracking-wider text-brand">
                    Start session
                </p>
                <h2 class="mt-4 text-2xl font-semibold tracking-tight">
                    Your practice interview
                </h2>
                <p class="mt-2 text-sm text-muted-foreground">
                    We tailor coaching to the role and style you choose.
                </p>

                <form
                    class="mt-8 space-y-4"
                    @submit.prevent="handleConnect"
                >
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-muted-foreground">Your name</label>
                        <input
                            v-model="userName"
                            placeholder="Alex Morgan"
                            autofocus
                            class="w-full rounded-lg border border-input bg-background px-4 py-3 text-sm outline-none transition focus:border-ring focus:ring-2 focus:ring-ring/20"
                        />
                    </div>

                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-muted-foreground">Role you are preparing for</label>
                        <input
                            v-model="jobRole"
                            placeholder="Software Engineer"
                            class="w-full rounded-lg border border-input bg-background px-4 py-3 text-sm outline-none transition focus:border-ring focus:ring-2 focus:ring-ring/20"
                        />
                    </div>

                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-muted-foreground">Interview focus</label>
                        <CustomSelectDropdown
                            id="interview_type"
                            v-model="interviewType"
                            :options="interviewTypeOptions"
                        >
                            <template #default="{ selectedLabel }">{{ selectedLabel }}</template>
                        </CustomSelectDropdown>
                    </div>

                    <div
                        v-if="session.connectError.value"
                        class="rounded-lg border border-destructive bg-destructive/10 p-3 text-sm text-destructive"
                    >
                        {{ session.connectError.value }}
                    </div>

                    <button
                        type="submit"
                        :disabled="!userName.trim() || isSubmitting"
                        class="inline-flex w-full items-center justify-center rounded-lg bg-foreground px-5 py-3 text-sm font-medium text-background shadow-sm transition hover:bg-foreground/90 disabled:opacity-50"
                    >
                        {{ isSubmitting ? 'Connecting…' : 'Join interview' }}
                    </button>
                </form>
            </div>
        </div>

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
                @end="handleSessionEnd"
            />
        </LiveKitRoom>

        <!-- Placeholder when not connected -->
        <div
            v-else-if="!showConnectModal"
            class="flex min-h-screen items-center justify-center"
        >
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

<style scoped>
.interview-shell {
  height: 100dvh;
  display: flex;
  flex-direction: column;
  padding-top: env(safe-area-inset-top, 0px);
  padding-bottom: env(safe-area-inset-bottom, 0px);
}
</style>
