<script setup lang="ts">
    import type { AgentState } from '@blockgain/livekit-vue';
    import { useLocalParticipant, useRoomContext, useVoiceAssistant } from '@blockgain/livekit-vue';
    import { useWindowSize } from '@vueuse/core';
    import type { Participant, TranscriptionSegment } from 'livekit-client';
    import { ConnectionState, isLocalParticipant, RoomEvent } from 'livekit-client';
    import { Mic, X, Pause, MessageSquare } from 'lucide-vue-next';
    import { computed, ref, watch } from 'vue';

    import type { OrbState } from '@/components/Orb.vue';
    import Orb from '@/components/Orb.vue';
    import TranscriptPanel from '@/components/TranscriptPanel.vue';
    import { toOrbState } from '@/composables/useLiveKit';
    import type { InterviewSessionMeta, SessionEndPayload } from '@/types/sessionFeedback';

    const props = withDefaults(
        defineProps<{
            sessionMeta?: InterviewSessionMeta;
            interviewSessionId: number;
        }>(),
        {
            sessionMeta: () => ({
                job_role: 'Interview practice',
                interview_type: 'behavioral',
                question_count: 6,
            }),
            interviewSessionId: 0,
        },
    );

    const emit = defineEmits<{
        end: [payload: SessionEndPayload];
        retryJoin: [];
    }>();

    interface TranscriptMessage {
        speaker: 'user' | 'agent';
        text: string;
        timestamp: Date;
        segmentId?: string;
    }

    const voiceAssistant = useVoiceAssistant();
    const room = useRoomContext();
    const { isMicrophoneEnabled, localParticipant } = useLocalParticipant();

    const { width: viewportW } = useWindowSize();
    const orbDisplaySize = computed(() => {
        const w = viewportW.value;

        if (w < 400) {
            return Math.max(220, w - 40);
        }

        if (w < 640) {
            return 300;
        }

        return 360;
    });

    // Transcript tracking
    const transcriptMessages = ref<TranscriptMessage[]>([]);
    const showTranscript = ref(false);

    const orbState = computed<OrbState>(() => toOrbState(voiceAssistant.state as AgentState));

    const labels: Record<OrbState, string> = {
        speaking: 'Interviewer is speaking',
        idle: 'Ready',
        listening: 'Listening',
        thinking: 'Thinking',
    };

    const latestAgentLine = ref('');
    const hasAgentCaption = computed(() => latestAgentLine.value.trim().length > 0);
    const hasInterviewerJoined = computed(() => {
        const activeRoom = room.value;

        if (!activeRoom) {
            return false;
        }

        return activeRoom.remoteParticipants.size > 0;
    });
    const waitingForInterviewer = computed(() => {
        const state = room.value?.state;

        if (state === undefined || state === ConnectionState.Disconnected) {
            return false;
        }

        if (state !== ConnectionState.Connected) {
            return true;
        }

        return !hasInterviewerJoined.value;
    });
    const isSlowJoin = ref(false);
    const hasRequestedRetry = ref(false);

    watch(
        waitingForInterviewer,
        (waiting, _, onCleanup) => {
            if (!waiting) {
                isSlowJoin.value = false;

                return;
            }

            const timeoutId = window.setTimeout(() => {
                isSlowJoin.value = true;

                if (!hasRequestedRetry.value) {
                    hasRequestedRetry.value = true;
                    emit('retryJoin');
                }
            }, 15000);

            onCleanup(() => window.clearTimeout(timeoutId));
        },
        { immediate: true },
    );

    watch(
        () => room.value,
        (r, _prev, onCleanup) => {
            if (!r) {
                return;
            }

            const seenFinalAgentIds = new Set<string>();
            const userPartialRowIndex = new Map<string, number>();

            function segmentKey(seg: TranscriptionSegment): string {
                return String(seg.id);
            }

            function upsertUserMessage(seg: TranscriptionSegment, text: string): void {
                const id = segmentKey(seg);
                const idx = userPartialRowIndex.get(id);
                const row: TranscriptMessage = {
                    speaker: 'user',
                    text,
                    timestamp: new Date(),
                    segmentId: id,
                };

                if (idx !== undefined && transcriptMessages.value[idx]?.speaker === 'user') {
                    transcriptMessages.value.splice(idx, 1, row);
                } else {
                    transcriptMessages.value.push(row);

                    if (!seg.final) {
                        userPartialRowIndex.set(id, transcriptMessages.value.length - 1);
                    }
                }

                if (seg.final) {
                    userPartialRowIndex.delete(id);
                }
            }

            const onTranscription = (segments: TranscriptionSegment[], participant?: Participant) => {
                if (!participant || segments.length === 0) {
                    return;
                }

                const isUser =
                    isLocalParticipant(participant) || participant.identity === r.localParticipant.identity;

                for (const seg of segments) {
                    const text = seg.text?.trim() ?? '';

                    if (!text) {
                        continue;
                    }

                    if (isUser) {
                        upsertUserMessage(seg, text);
                    } else {
                        latestAgentLine.value = text;

                        const id = segmentKey(seg);

                        if (seg.final && !seenFinalAgentIds.has(id)) {
                            seenFinalAgentIds.add(id);
                            transcriptMessages.value.push({
                                speaker: 'agent',
                                text,
                                timestamp: new Date(),
                                segmentId: id,
                            });
                        }
                    }
                }
            };

            r.on(RoomEvent.TranscriptionReceived, onTranscription);

            onCleanup(() => {
                r.off(RoomEvent.TranscriptionReceived, onTranscription);
            });
        },
        { immediate: true },
    );

    const isUserSpeaking = computed(
        () => isMicrophoneEnabled.value && voiceAssistant.state === 'listening',
    );

    // Animation key for text transitions
    const animationKey = ref(0);

    watch([orbState, latestAgentLine], () => {
        animationKey.value += 1;
    });

    async function toggleMic() {
        try {
            await localParticipant.value.setMicrophoneEnabled(!isMicrophoneEnabled.value);
        } catch (e) {
            console.error('Mic toggle failed:', e);
        }
    }

    function toggleTranscript() {
        showTranscript.value = !showTranscript.value;
    }

    const sessionElapsed = ref(0);

    watch(
        () => room.value?.state,
        (state, _, onCleanup) => {
            if (state !== ConnectionState.Connected || !room.value) {
                return;
            }

            sessionElapsed.value = 0;

            const id = window.setInterval(() => {
                sessionElapsed.value += 1;
            }, 1000);

            onCleanup(() => window.clearInterval(id));
        },
        { immediate: true },
    );

    const formattedTime = computed(() => {
        const mm = String(Math.floor(sessionElapsed.value / 60)).padStart(2, '0');
        const ss = String(sessionElapsed.value % 60).padStart(2, '0');

        return `${mm}:${ss}`;
    });

    const headerSubtitle = computed(() => {
        const jr = props.sessionMeta.job_role;
        const it = props.sessionMeta.interview_type.replace(/_/g, ' ');

        return `${jr} · ${it}`;
    });

    function emitEnd(): void {
        emit('end', {
            interview_session_id: props.interviewSessionId,
            messages: transcriptMessages.value.map((m) => ({
                speaker: m.speaker,
                text: m.text,
                at: m.timestamp.toISOString(),
            })),
            duration_seconds: sessionElapsed.value,
            sessionMeta: {
                job_role: props.sessionMeta.job_role,
                interview_type: props.sessionMeta.interview_type,
                question_count: props.sessionMeta.question_count,
            },
        });
    }
</script>

<template>
    <div class="relative flex min-h-screen flex-col bg-surface-2/40">
        <!-- Soft wash -->
        <div class="pointer-events-none absolute inset-0 hero-wash" />

        <!-- Top bar -->
        <div class="relative z-10 flex items-center justify-between border-b border-hairline bg-background/70 px-6 py-3.5 backdrop-blur-xl">
            <button class="inline-flex items-center gap-1.5 rounded-lg border border-hairline bg-surface px-3 py-1.5 text-xs font-medium shadow-xs transition hover:bg-surface-2" @click="emitEnd">
                <X class="h-3.5 w-3.5" />
                End
            </button>

            <div class="flex items-center gap-2 rounded-full border border-hairline bg-surface px-3 py-1.5 text-xs shadow-xs">
                <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-brand" />
                <span class="font-medium">{{ headerSubtitle }}</span>
                <span class="text-muted-foreground">·</span>
                <span class="tabular-nums text-muted-foreground">{{ formattedTime }}</span>
            </div>

            <div class="flex items-center gap-2">
                <span class="hidden items-center gap-1.5 rounded-md bg-brand-soft px-2 py-1 text-xs font-medium text-brand md:inline-flex">
                    Live score 8.4
                </span>
            </div>
        </div>

        <!-- Center -->
        <div class="relative z-10 flex flex-1 flex-col items-center justify-center px-6">
            <Orb :state="orbState" :size="orbDisplaySize" interactive/>

            <div
                v-if="waitingForInterviewer"
                class="mt-6 inline-flex items-center gap-2 rounded-full border border-hairline bg-surface px-3 py-1.5 text-xs font-medium text-muted-foreground shadow-xs"
            >
                <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-brand" />
                {{ isSlowJoin ? 'Still connecting, retrying...' : 'Interviewer is joining...' }}
            </div>

            <Transition
                v-if="true"
                :key="animationKey"
                mode="out-in"
                enter-active-class="transition-all duration-250"
                enter-from-class="opacity-0 translate-y-1.5"
                enter-to-class="opacity-100 translate-y-0"
                leave-active-class="transition-all duration-250"
                leave-from-class="opacity-100 translate-y-0"
                leave-to-class="opacity-0 -translate-y-1.5">
                <div
                    v-if="true"
                    class="mt-12 max-w-2xl text-center">
                    <p class="text-xs font-medium uppercase tracking-wider text-brand">
                        {{ labels[orbState] }}
                    </p>
                    <p
                        class="mx-auto mt-4 max-w-xl text-balance leading-snug md:text-3xl"
                        :class="
                            hasAgentCaption
                                ? 'text-2xl font-medium'
                                : 'text-lg font-normal text-muted-foreground md:text-xl'
                        ">
                        {{
                            waitingForInterviewer
                                ? (isSlowJoin
                                    ? 'Connection is taking longer than expected. We are retrying now.'
                                    : 'Setting up your interviewer now. The first question will begin automatically.')
                                : hasAgentCaption
                                ? latestAgentLine
                                : "The interviewer's words will appear here when they speak."
                        }}
                    </p>
                </div>
            </Transition>

            <!-- Progress chips -->
            <div class="mt-12 flex items-center gap-1.5">
                <div v-for="n in 6" :key="n"
                    :class="[
                        'h-1 rounded-full transition',
                        n < 3 ? 'w-8 bg-brand' : n === 3 ? 'w-12 bg-foreground' : 'w-8 bg-hairline',
                    ]"
                />
            </div>
            <p class="mt-3 text-xs text-muted-foreground">Question 3 of 6</p>
        </div>

        <!-- Bottom controls -->
        <div class="relative z-10 flex flex-col items-center gap-4 px-6 pb-10 pt-6">
            <div class="flex items-center gap-3">
                <button
                    class="grid h-11 w-11 place-items-center rounded-full border border-hairline bg-surface text-muted-foreground shadow-xs transition hover:text-foreground"
                    aria-label="Pause">
                    <Pause class="h-4 w-4" />
                </button>

                <button
                    :class="[
                        'group relative grid h-16 w-16 place-items-center rounded-full shadow-lg transition hover:scale-[1.04] active:scale-100',
                        isMicrophoneEnabled ? 'bg-foreground text-background' : 'bg-surface border border-hairline text-foreground',
                    ]"
                    aria-label="Toggle microphone"
                    @click="toggleMic">
                    <span
                        v-if="isUserSpeaking"
                        class="absolute inset-0 animate-ping rounded-full bg-brand/40"
                    />
                    <Mic class="relative h-6 w-6" />
                </button>

                <button
                    class="grid h-11 w-11 place-items-center rounded-full border border-hairline bg-surface text-muted-foreground shadow-xs transition hover:text-foreground"
                    aria-label="Show transcript"
                    @click="toggleTranscript">
                    <MessageSquare class="h-4 w-4" />
                </button>
            </div>

            <button
                class="text-xs font-medium text-muted-foreground transition hover:text-foreground cursor-pointer"
                @click="emitEnd">
                End session and view feedback
            </button>
        </div>

        <!-- Transcript Panel -->
        <TranscriptPanel
            :messages="transcriptMessages"
            :is-open="showTranscript"
            @close="showTranscript = false"
        />
    </div>
</template>
