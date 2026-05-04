<script setup lang="ts">
    import type { AgentState, CoreTrackReference } from '@blockgain/livekit-vue';
    import {
        BarVisualizer,
        useConnectionState,
        useLocalParticipant,
        useRoomContext,
        useVoiceAssistant,
    } from '@blockgain/livekit-vue';
    import type { Participant, TranscriptionSegment } from 'livekit-client';
    import {
        ConnectionQuality,
        ConnectionState,
        DisconnectReason,
        isLocalParticipant,
        RoomEvent,
    } from 'livekit-client';

    import { computed, ref, watch } from 'vue';

    import type { OrbState } from '@/components/Orb.vue';
    import Orb from '@/components/Orb.vue';
    import NotFoundEmptyState from '@/components/ui/NotFoundEmptyState.vue';
    import { toOrbState } from '@/composables/useLiveKit';
    import type { InterviewSessionMeta, SessionEndPayload } from '@/types/sessionFeedback';
    import { defaultInterviewSessionMeta } from '@/types/sessionFeedback';

    const props = withDefaults(
        defineProps<{
            sessionMeta?: InterviewSessionMeta;
            interviewSessionId: number;
        }>(),
        {
            sessionMeta: () => ({ ...defaultInterviewSessionMeta }),
            interviewSessionId: 0,
        },
    );

    const resolvedSessionMeta = computed(
        (): InterviewSessionMeta => props.sessionMeta ?? defaultInterviewSessionMeta,
    );

    const emit = defineEmits<{
        end: [payload: SessionEndPayload];
        retryJoin: [];
        connectionLost: [payload: { reasonCode: number | null }];
    }>();

    const userRequestedSessionEnd = ref(false);

    interface TranscriptMessage {
        speaker: 'user' | 'agent';
        text: string;
        timestamp: Date;
        segmentId?: string;
    }

    function sanitizeInterviewAgentSpeechText(raw: string): string {
        let s = raw.trim();

        s = s.replace(/\s*\*\*`register_primary_question`\*\*\s+with\s+`primary_question_index=\d+`[^.!?]*[.!?]?/gi, ' ');
        s = s.replace(/\bregister_primary_question\b[^.!?]*[.!?]?/gi, '');
        s = s.replace(/\s*`primary_question_index=\d+`\s*/gi, ' ');
        s = s.replace(/\bprimary_question_index\s*=\s*\d+\b/gi, '');

        return s.replace(/\s{2,}/g, ' ').trim();
    }

    const voiceAssistant = useVoiceAssistant();
    const room = useRoomContext();
    const connectionState = useConnectionState();
    const { isMicrophoneEnabled, localParticipant, microphoneTrack } = useLocalParticipant();

    const barVisualizerTrackRef = computed((): CoreTrackReference | undefined => {
        const fromAgent = voiceAssistant.audioTrack;

        if (fromAgent) {
            return fromAgent;
        }

        const lp = localParticipant.value;
        const pub = microphoneTrack.value;

        if (lp && pub) {
            return { participant: lp, publication: pub, source: pub.source };
        }

        return undefined;
    });

    const orbMobileSize = 200;
    const orbDesktopSize = 300;

    const connectionQuality = ref(ConnectionQuality.Unknown);

    watch(
        () => room.value,
        (r, _previous, onCleanup) => {
            if (!r) {
                connectionQuality.value = ConnectionQuality.Unknown;

                return;
            }

            connectionQuality.value = r.localParticipant.connectionQuality;

            const onConnectionQualityChanged = (quality: ConnectionQuality, participant: Participant) => {
                if (!isLocalParticipant(participant)) {
                    return;
                }

                connectionQuality.value = quality;
            };

            r.on(RoomEvent.ConnectionQualityChanged, onConnectionQualityChanged);

            onCleanup(() => {
                r.off(RoomEvent.ConnectionQualityChanged, onConnectionQualityChanged);
            });
        },
        { immediate: true },
    );

    const connectionBadge = computed(() => {
        const r = room.value;
        /** Same ref as LiveKit emits; unlike `Room.state`, this stays in sync under Vue */
        const state = connectionState.value;

        if (!r) {
            return {
                headline: '—',
                tail: '',
                iconClass: 'text-muted-foreground',
                title: 'Room not ready',
            } as const;
        }

        if (state === ConnectionState.Disconnected) {
            return {
                headline: 'Offline',
                tail: '',
                iconClass: 'text-muted-foreground',
                title: 'Disconnected',
            } as const;
        }

        if (state === ConnectionState.Connecting) {
            return {
                headline: 'Connecting',
                tail: '…',
                iconClass: 'text-warning animate-pulse',
                title: 'Connecting to realtime…',
            } as const;
        }

        if (
            state === ConnectionState.Reconnecting
            || state === ConnectionState.SignalReconnecting
        ) {
            return {
                headline: 'Syncing',
                tail: '…',
                iconClass: 'text-warning',
                title: 'Refreshing realtime link — playback may pause briefly.',
            } as const;
        }

        const q = connectionQuality.value;

        const title = `Connection quality: ${q}`;

        switch (q) {
            case ConnectionQuality.Excellent:
                return {
                    headline: 'Excellent',
                    tail: 'connection',
                    iconClass: 'text-success',
                    title,
                } as const;
            case ConnectionQuality.Good:
                return {
                    headline: 'Good',
                    tail: 'connection',
                    iconClass: 'text-success',
                    title,
                } as const;
            case ConnectionQuality.Poor:
                return {
                    headline: 'Poor',
                    tail: 'connection',
                    iconClass: 'text-warning',
                    title,
                } as const;
            case ConnectionQuality.Lost:
                return {
                    headline: 'Unstable',
                    tail: 'connection',
                    iconClass: 'text-destructive',
                    title,
                } as const;
            default:
                return {
                    headline: 'Checking',
                    tail: 'connection',
                    iconClass: 'text-muted-foreground',
                    title: 'Measuring connection quality…',
                } as const;
        }
    });

    watch(
        () => room.value,
        (r, _previous, onCleanup) => {
            if (!r) {
                return;
            }

            const onDisconnected = (reason?: DisconnectReason): void => {
                if (userRequestedSessionEnd.value) {
                    return;
                }

                if (reason === DisconnectReason.CLIENT_INITIATED || reason === DisconnectReason.MIGRATION) {
                    return;
                }

                const code = typeof reason === 'number' ? reason : null;
                emit('connectionLost', { reasonCode: code });
            };

            r.on(RoomEvent.Disconnected, onDisconnected);

            onCleanup(() => {
                r.off(RoomEvent.Disconnected, onDisconnected);
            });
        },
        { immediate: true },
    );

    const transcriptMessages = ref<TranscriptMessage[]>([]);
    const mobileRoomTab = ref<'session' | 'transcript'>('session');

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

    const hadRemoteParticipantEver = ref(false);

    watch(
        hasInterviewerJoined,
        (joined) => {
            if (joined) {
                hadRemoteParticipantEver.value = true;
            }
        },
        { immediate: true },
    );

    const waitingForInterviewer = computed(() => {
        const state = connectionState.value;

        if (state === ConnectionState.Disconnected) {
            return false;
        }

        if (
            state === ConnectionState.Reconnecting
            || state === ConnectionState.SignalReconnecting
        ) {
            return false;
        }

        if (hadRemoteParticipantEver.value) {
            return false;
        }

        if (state === ConnectionState.Connecting) {
            return true;
        }

        if (state !== ConnectionState.Connected) {
            return false;
        }

        return !hasInterviewerJoined.value;
    });

    const isSlowJoin = ref(false);
    const hasRequestedRetry = ref(false);

    const shouldWatchForAgentJoinTimeout = computed(
        () =>
            connectionState.value === ConnectionState.Connected
            && !hasInterviewerJoined.value
            && !hadRemoteParticipantEver.value,
    );

    watch(
        shouldWatchForAgentJoinTimeout,
        (active, _, onCleanup) => {
            if (!active) {
                isSlowJoin.value = false;

                return;
            }

            const timeoutId = window.setTimeout(() => {
                isSlowJoin.value = true;

                if (!hasRequestedRetry.value) {
                    hasRequestedRetry.value = true;
                    emit('retryJoin');
                }
            }, 45_000);

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
                        const cleaned = sanitizeInterviewAgentSpeechText(text);

                        if (cleaned !== '') {
                            /** Confirms session is live even if `remoteParticipants` is briefly empty. */
                            hadRemoteParticipantEver.value = true;
                            latestAgentLine.value = cleaned;
                        }

                        const id = segmentKey(seg);

                        if (cleaned !== '' && seg.final && !seenFinalAgentIds.has(id)) {
                            seenFinalAgentIds.add(id);
                            transcriptMessages.value.push({
                                speaker: 'agent',
                                text: cleaned,
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

    const animationKey = ref(0);

    watch([orbState, latestAgentLine], () => {
        animationKey.value += 1;
    });

    async function toggleMic(): Promise<void> {
        try {
            await localParticipant.value.setMicrophoneEnabled(!isMicrophoneEnabled.value);
        } catch (e) {
            console.error('Mic toggle failed:', e);
        }
    }

    const sessionElapsed = ref(0);

    watch(
        () => room.value,
        (r, _prev, onCleanup) => {
            let intervalId: ReturnType<typeof window.setInterval> | undefined;

            function clearTimer(): void {
                if (intervalId !== undefined) {
                    window.clearInterval(intervalId);
                    intervalId = undefined;
                }
            }

            function onConnectionState(state: ConnectionState): void {
                if (state === ConnectionState.Connected) {
                    if (intervalId === undefined) {
                        intervalId = window.setInterval(() => {
                            sessionElapsed.value += 1;
                        }, 1000);
                    }

                    return;
                }

                clearTimer();

                if (state === ConnectionState.Disconnected) {
                    sessionElapsed.value = 0;
                }
            }

            if (!r) {
                clearTimer();
                sessionElapsed.value = 0;

                return;
            }

            onConnectionState(r.state);
            r.on(RoomEvent.ConnectionStateChanged, onConnectionState);

            onCleanup(() => {
                r.off(RoomEvent.ConnectionStateChanged, onConnectionState);
                clearTimer();
            });
        },
        { immediate: true },
    );

    function formatMmSs(seconds: number): string {
        const clamped = Math.max(0, Math.floor(seconds));
        const mm = String(Math.floor(clamped / 60)).padStart(2, '0');
        const ss = String(clamped % 60).padStart(2, '0');

        return `${mm}:${ss}`;
    }

    const formattedTime = computed(() => formatMmSs(sessionElapsed.value));

    const plannedDurationSeconds = computed(() =>
        Number.isFinite(resolvedSessionMeta.value.planned_duration_seconds)
        && resolvedSessionMeta.value.planned_duration_seconds > 0
            ? Math.floor(resolvedSessionMeta.value.planned_duration_seconds)
            : 25 * 60,
    );

    const formattedPlannedClock = computed(() => formatMmSs(plannedDurationSeconds.value));

    const isPastPlannedDuration = computed(
        () => sessionElapsed.value > plannedDurationSeconds.value,
    );

    const interviewTypePhrase = computed(() => {
        const raw = resolvedSessionMeta.value.interview_type.replace(/_/g, ' ');
        const t = raw.trim();

        return t === '' ? 'Interview' : `${t.charAt(0).toUpperCase()}${t.slice(1)} round`;
    });

    const transcriptLineTimeLabel = (d: Date): string =>
        d.toLocaleTimeString(undefined, { hour: 'numeric', minute: '2-digit' });

    const sessionStatusStripe = computed((): {
        dotClass: string;
        label: string;
    } => {
        if (waitingForInterviewer.value) {
            return {
                dotClass: 'bg-warning animate-pulse',
                label: isSlowJoin.value ? 'Still connecting — retrying…' : 'Interviewer is joining…',
            };
        }

        switch (orbState.value) {
            case 'thinking':
                return {
                    dotClass: 'bg-warning animate-pulse',
                    label: 'Analyzing response',
                };
            case 'speaking':
                return {
                    dotClass: 'bg-brand animate-pulse',
                    label: 'Interviewer is speaking',
                };
            case 'listening':
                return {
                    dotClass: 'bg-brand animate-pulse',
                    label: 'Listening',
                };
            default:
                return {
                    dotClass: 'bg-muted-foreground',
                    label: labels.idle,
                };
        }
    });

    const captionBlockBody = computed(() => {
        /** Live agent text must never stay hidden behind pre-join copy during edge races. */
        if (hasAgentCaption.value) {
            return latestAgentLine.value;
        }

        if (waitingForInterviewer.value) {
            return isSlowJoin.value
                ? 'Connection is taking longer than expected. We are retrying now.'
                : 'Setting up your interviewer now. The first question will begin automatically.';
        }

        return "The interviewer's words will appear here when they speak.";
    });

    function emitEnd(): void {
        if (userRequestedSessionEnd.value) {
            return;
        }

        userRequestedSessionEnd.value = true;

        emit('end', {
            interview_session_id: props.interviewSessionId,
            messages: transcriptMessages.value.map((m) => ({
                speaker: m.speaker,
                text: m.text,
                at: m.timestamp.toISOString(),
            })),
            duration_seconds: sessionElapsed.value,
            sessionMeta: {
                job_role: resolvedSessionMeta.value.job_role,
                interview_type: resolvedSessionMeta.value.interview_type,
                question_count: resolvedSessionMeta.value.question_count,
                planned_duration_seconds: plannedDurationSeconds.value,
            },
        });
    }
</script>

<template>
    <div class="relative flex h-screen flex-col overflow-hidden bg-surface-2/40">
        <div class="pointer-events-none absolute inset-0 hero-wash opacity-60" />
        <header class="relative z-20 flex h-14 shrink-0 items-center justify-between border-b border-hairline bg-background/80 px-4 backdrop-blur-xl md:px-6">
            <div class="flex items-center gap-3">
                <button type="button" class="inline-flex items-center gap-1.5 rounded-lg border border-hairline bg-surface px-2.5 py-1.5 text-xs font-medium shadow-xs transition hover:bg-surface-2" @click.prevent="emitEnd">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-x h-3.5 w-3.5" aria-hidden="true">
                        <path d="M18 6 6 18"></path>
                        <path d="m6 6 12 12"></path></svg>
                    <span class="hidden sm:inline">End session</span>
                </button>

                <div class="hidden items-center gap-2 md:flex">
                    <div class="h-5 w-px bg-hairline"></div>
                    <span class="text-xs font-medium">{{ resolvedSessionMeta.job_role }}</span>
                    <span class="text-xs text-muted-foreground">·</span>
                    <span class="text-xs text-muted-foreground">{{ interviewTypePhrase }}</span>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <div class="hidden items-center gap-1.5 rounded-full border border-hairline bg-surface px-3 py-1 text-xs shadow-xs sm:flex"
                    :title="connectionBadge.title">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-signal h-3 w-3 shrink-0" :class="connectionBadge.iconClass" aria-hidden="true">
                        <path d="M2 20h.01"></path>
                        <path d="M7 20v-4"></path>
                        <path d="M12 20v-8"></path>
                        <path d="M17 20V8"></path>
                        <path d="M22 4v16"></path>
                    </svg>
                    <span class="font-medium">{{ connectionBadge.headline }}</span>
                    <span v-if="connectionBadge.tail" class="text-muted-foreground">{{ connectionBadge.tail }}</span>
                </div>

                <div class="flex items-center gap-1.5 rounded-full border border-hairline bg-surface px-3 py-1 text-xs shadow-xs">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-clock h-3 w-3 text-muted-foreground" aria-hidden="true">
                        <circle cx="12" cy="12" r="10"></circle>
                        <path d="M12 6v6l4 2"></path>
                    </svg>

                    <span class="tabular-nums" :class="isPastPlannedDuration ? 'text-amber-600 dark:text-amber-400' : ''"
                        :title="
                            isPastPlannedDuration
                                ? 'Past target length — wrap up whenever you’re ready.'
                                : 'Elapsed / target session length.'
                        ">
                        {{ formattedTime }}
                    </span>
                    <span class="text-muted-foreground">/ {{ formattedPlannedClock }}</span>
                </div>
            </div>
        </header>

        <div class="relative z-10 flex shrink-0 items-center gap-1 border-b border-hairline bg-background/80 px-3 py-2 backdrop-blur lg:hidden">
            <button type="button"
                :class="[
                    'flex-1 rounded-md px-3 py-1.5 text-xs font-medium capitalize transition',
                    mobileRoomTab === 'session'
                        ? 'bg-surface text-foreground shadow-xs'
                        : 'text-muted-foreground',
                ]"
                @click="mobileRoomTab = 'session'">
                Session
            </button>

            <button type="button"
                :class="[
                    'flex-1 rounded-md px-3 py-1.5 text-xs font-medium capitalize transition',
                    mobileRoomTab === 'transcript'
                        ? 'bg-surface text-foreground shadow-xs'
                        : 'text-muted-foreground',
                ]"
                @click="mobileRoomTab = 'transcript'">
                Transcript
            </button>
        </div>

        <div class="relative z-10 grid flex-1 min-h-0 grid-cols-1 lg:grid-cols-[minmax(0,1fr)_minmax(0,1fr)]">
            <aside
                :class="[
                    'order-2 min-h-0 flex-col border-t border-hairline bg-background/60 lg:order-0 lg:border-r lg:border-t-0 lg:overflow-hidden',
                    mobileRoomTab === 'transcript' ? 'flex overflow-hidden' : 'hidden',
                    'lg:flex',
                ]">
                <div class="flex shrink-0 items-center justify-between border-b border-hairline px-5 py-3">
                    <div class="flex items-center gap-2">
                        <span class="relative flex h-2 w-2">
                            <span class="absolute inset-0 animate-ping rounded-full bg-destructive/60"></span>
                            <span class="relative h-2 w-2 rounded-full bg-destructive"></span>
                        </span>
                        <h3 class="text-xs font-semibold uppercase tracking-wider">Live transcript</h3>
                    </div>
                    <span class="text-[11px] text-muted-foreground">Auto-saved</span>
                </div>

                <div class="flex min-h-0 flex-1 flex-col px-5 py-4">
                    <ul v-if="transcriptMessages.length > 0" class="min-h-0 flex-1 space-y-4 overflow-y-auto">
                        <li v-for="(msg, i) in transcriptMessages" :key="msg.segmentId ?? `${msg.speaker}-${i}-${msg.timestamp.getTime()}`" class="flex gap-3">
                            <div
                                :class="[
                                    'mt-0.5 grid h-7 w-7 shrink-0 place-items-center rounded-full text-[10px] font-semibold',
                                    msg.speaker === 'agent'
                                        ? 'bg-brand-soft text-brand'
                                        : 'bg-foreground text-background',
                                ]">
                                {{ msg.speaker === 'agent' ? 'AI' : 'You' }}
                            </div>

                            <div class="min-w-0 flex-1">
                                <div class="flex items-baseline gap-2">
                                    <span class="text-[11px] font-medium">
                                        {{ msg.speaker === 'agent' ? 'Interviewer' : 'You' }}
                                    </span>

                                    <span class="text-[10px] tabular-nums text-muted-foreground">
                                        {{ transcriptLineTimeLabel(msg.timestamp) }}
                                    </span>
                                </div>

                                <p class="mt-0.5 text-[13px] leading-relaxed text-foreground">
                                    {{ msg.text }}
                                </p>
                            </div>
                        </li>
                    </ul>

                    <div v-else class="flex min-h-0 flex-1 flex-col items-center justify-center">
                        <NotFoundEmptyState
                            class="max-w-sm border-0! bg-transparent! p-0 px-4 py-0 shadow-none rounded-none gap-3"
                            title="No transcript yet"
                            description="Transcript will appear here as you and the interviewer speak."
                        />
                    </div>
                </div>

                <div class="shrink-0 border-t border-hairline bg-surface/60 p-3">
                    <div class="rounded-xl border border-hairline bg-surface shadow-xs focus-within:ring-1 focus-within:ring-ring">
                        <textarea class="flex w-full rounded-md border-input px-3 py-2 placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50 md:text-sm min-h-15 resize-none border-0 bg-transparent text-[13px] shadow-none focus-visible:ring-0" placeholder="Type a clarifying question or note…" disabled></textarea>
                        <div class="flex items-center justify-between gap-2 px-2 pb-2">
                            <div class="flex items-center gap-1 text-[10px] text-muted-foreground">
                                <kbd class="rounded border border-hairline bg-background px-1 py-0.5 font-mono text-[9px]">⏎</kbd>
                                to send
                            </div>

                            <button type="button" disabled class="inline-flex items-center gap-1.5 rounded-lg bg-foreground px-3 py-1.5 text-xs font-medium text-background transition hover:opacity-90 disabled:opacity-40">
                                Send
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-send h-3 w-3" aria-hidden="true">
                                    <path d="M14.536 21.686a.5.5 0 0 0 .937-.024l6.5-19a.496.496 0 0 0-.635-.635l-19 6.5a.5.5 0 0 0-.024.937l7.93 3.18a2 2 0 0 1 1.112 1.11z"></path>
                                    <path d="m21.854 2.147-10.94 10.939"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </aside>

            <main
                :class="[
                    'relative order-1 min-h-0 flex-col items-center justify-between px-4 py-4 md:px-6 md:py-6 lg:order-0',
                    mobileRoomTab === 'session' ? 'flex' : 'hidden',
                    'lg:flex',
                ]">

                <div class="flex items-center gap-2 rounded-full border border-hairline bg-surface/80 px-3 py-1.5 text-xs shadow-xs backdrop-blur">
                    <span class="h-1.5 w-1.5 rounded-full" :class="sessionStatusStripe.dotClass" />
                    <span class="font-medium">{{ sessionStatusStripe.label }}</span>
                </div>

                <div class="flex flex-1 flex-col items-center justify-center">
                    <div class="md:hidden">
                        <div class="relative flex items-center justify-center" :style="{ width: `${orbMobileSize}px`, height: `${orbMobileSize}px` }">
                            <Orb :state="orbState" :size="orbMobileSize" interactive />
                        </div>
                    </div>

                    <div class="hidden md:block">
                        <div class="relative flex items-center justify-center" :style="{ width: `${orbDesktopSize}px`, height: `${orbDesktopSize}px` }">
                            <Orb :state="orbState" :size="orbDesktopSize" interactive />
                        </div>
                    </div>

                    <div :key="animationKey" class="mt-6 max-w-xl text-center transition-all duration-250 md:mt-10" style="opacity: 1; transform: none">
                        <p class="text-[11px] font-semibold uppercase tracking-wider text-brand">
                            {{ labels[orbState] }}
                        </p>

                        <p class="mx-auto mt-2 text-balance text-lg font-medium leading-snug text-foreground md:mt-3 md:text-[26px]" :class="hasAgentCaption || waitingForInterviewer ? '' : 'text-muted-foreground'">
                            {{ captionBlockBody }}
                        </p>
                    </div>

                    <div class="session-bar-visualizer mt-8 h-10 w-full max-w-md shrink-0 px-2" aria-hidden="true">
                        <BarVisualizer
                            :track-ref="barVisualizerTrackRef"
                            :state="voiceAssistant.state"
                            :bar-count="24"
                            :options="{ minHeight: 22, maxHeight: 100 }"
                        />
                    </div>
                </div>

                <div class="flex flex-col items-center gap-4">
                    <button type="button"
                        :class="[
                            'group relative grid h-14 w-14 place-items-center rounded-full transition hover:scale-[1.04] shadow-lg active:scale-100',
                            isMicrophoneEnabled ? 'bg-foreground text-background' : 'bg-surface border border-hairline text-foreground',
                        ]"
                        :aria-label="isMicrophoneEnabled ? 'Mute microphone' : 'Unmute microphone'"
                        @click="toggleMic">

                        <span v-if="isUserSpeaking" class="absolute inset-0 animate-ping rounded-full bg-brand/40"/>

                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" width="30" height="30">
                            <path d="M12 19C15.31 19 18 16.31 18 13V8C18 4.69 15.31 2 12 2C8.69 2 6 4.69 6 8V13C6 16.31 8.69 19 12 19Z" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"></path>
                            <path d="M3 11V13C3 17.97 7.03 22 12 22C16.97 22 21 17.97 21 13V11" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"></path>
                            <path d="M9.11011 7.47999C10.8901 6.82999 12.8301 6.82999 14.6101 7.47999" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"></path>
                            <path d="M10.03 10.48C11.23 10.15 12.5 10.15 13.7 10.48" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"></path>
                        </svg>
                    </button>

                    <button
                        class="text-xs font-medium text-muted-foreground transition hover:text-foreground cursor-pointer"
                        @click="emitEnd">
                        End session and view feedback
                    </button>
                </div>
            </main>
        </div>
    </div>
</template>

<style scoped>
    /*
     * BarVisualizer ships global .lk-* rules tuned for LiveKit demos; anchor bars to our tokens
     * so inactive bars contrast with the interview surface (--foreground @ ~22% opacity).
     */
    .session-bar-visualizer :deep(.lk-audio-bar-visualizer) {
        display: flex;
        height: 100%;
        min-height: 2.5rem;
        align-items: flex-end;
        justify-content: center;
        gap: 3px;
        background: transparent;
    }

    .session-bar-visualizer :deep(.lk-audio-bar) {
        box-sizing: border-box;
        flex: 0 0 auto;
        width: 3px !important;
        min-width: 3px;
        border-radius: 9999px;
        /* Wins over BarVisualizer’s var(--lk-va-bar-bg) for our light/dark surfaces */
        background-color: color-mix(in oklch, var(--foreground) 24%, transparent) !important;
        transition: background-color 0.2s ease;
    }

    .session-bar-visualizer :deep(.lk-audio-bar.lk-highlighted),
    .session-bar-visualizer :deep(.lk-audio-bar[data-lk-highlighted='true']) {
        background-color: var(--brand) !important;
    }

    /* Match LiveKit pkg: speaking state floods bars to accent */
    .session-bar-visualizer :deep(.lk-audio-bar-visualizer[data-lk-va-state='speaking'] > .lk-audio-bar) {
        background-color: var(--brand) !important;
    }

    /* Slightly stronger contrast while listening */
    .session-bar-visualizer :deep(.lk-audio-bar-visualizer[data-lk-va-state='listening'] > .lk-audio-bar:not(.lk-highlighted):not([data-lk-highlighted='true'])) {
        background-color: color-mix(in oklch, var(--foreground) 32%, transparent) !important;
    }
</style>
