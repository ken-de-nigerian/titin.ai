<script setup lang="ts">
    import { X } from 'lucide-vue-next';
    import { computed } from 'vue';

    interface TranscriptMessage {
        speaker: 'user' | 'agent';
        text: string;
        timestamp: Date;
        segmentId?: string;
    }

    const props = defineProps<{
        messages: TranscriptMessage[];
        isOpen: boolean;
    }>();

    const emit = defineEmits<{
        close: [];
    }>();

    const formattedMessages = computed(() => {
        return props.messages.map((msg) => ({
            ...msg,
            time: msg.timestamp.toLocaleTimeString('en-US', {
                hour: '2-digit',
                minute: '2-digit',
            }),
        }));
    });
</script>

<template>
    <Transition
        enter-active-class="transition-all duration-300"
        enter-from-class="translate-x-full"
        enter-to-class="translate-x-0"
        leave-active-class="transition-all duration-300"
        leave-from-class="translate-x-0"
        leave-to-class="translate-x-full"
    >
        <div
            v-if="isOpen"
            class="fixed top-0 right-0 z-50 flex h-full w-full flex-col border-l border-hairline bg-background shadow-2xl sm:w-96"
        >
            <!-- Header -->
            <div class="flex items-center justify-between border-b border-hairline px-4 py-3">
                <h3 class="text-sm font-semibold">Transcript</h3>
                <button
                    class="grid h-8 w-8 place-items-center rounded-lg text-muted-foreground transition hover:bg-surface hover:text-foreground"
                    aria-label="Close transcript"
                    @click="emit('close')"
                >
                    <X class="h-4 w-4" />
                </button>
            </div>

            <!-- Messages -->
            <div class="flex-1 space-y-4 overflow-y-auto p-4">
                <div
                    v-for="(msg, i) in formattedMessages"
                    :key="msg.segmentId ?? `${msg.speaker}-${i}-${msg.time}`"
                    :class="[
                        'flex flex-col gap-1.5',
                        msg.speaker === 'user' ? 'items-end' : 'items-start',
                    ]"
                >
                    <div class="flex items-center gap-2 text-xs text-muted-foreground">
                        <span class="font-medium">{{ msg.speaker === 'user' ? 'You' : 'Interviewer' }}</span>
                        <span>{{ msg.time }}</span>
                    </div>
                    <div
                        :class="[
                            'max-w-[85%] rounded-2xl px-4 py-2.5 text-sm leading-relaxed',
                            msg.speaker === 'user'
                                ? 'bg-foreground text-background'
                                : 'bg-surface border border-hairline',
                        ]"
                    >
                        {{ msg.text }}
                    </div>
                </div>

                <div
                    v-if="formattedMessages.length === 0"
                    class="flex min-h-[40%] items-center justify-center px-4 text-center text-sm text-muted-foreground"
                >
                    <p>Transcript will appear here as you and the interviewer speak.</p>
                </div>
            </div>
        </div>
    </Transition>
</template>
