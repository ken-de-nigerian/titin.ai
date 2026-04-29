<script setup lang="ts">
    import { usePage } from '@inertiajs/vue3';
    import { computed } from 'vue';
    import TextLink from "@/components/TextLink.vue";
    import {useRoute} from "@/composables/useRoute";

    const route = useRoute();

    const props = withDefaults(
        defineProps<{
            variant?: 'sidebar' | 'header';
        }>(),
        { variant: 'sidebar' },
    );

    const triggerAvatarPx = computed(() => (props.variant === 'sidebar' ? 32 : 28));
    const triggerInitialsClass = computed(() =>
        props.variant === 'sidebar'
            ? 'relative z-10 text-[10px] font-semibold leading-none text-white'
            : 'relative z-10 text-[10px] font-semibold leading-none text-white',
    );

    const page = usePage();
    type AuthUserLite = {
        name?: string | null;
        job_role?: string | null;
        interview_type?: string | null;
        profile_photo_url?: string | null;
    };

    const user = computed((): AuthUserLite | null | undefined => page.props.auth?.user as AuthUserLite | null | undefined);
    const displayName = computed(() => String(user.value?.name ?? 'Account'));
    const displayMeta = computed(() => String(user.value?.job_role ?? user.value?.interview_type ?? 'Profile'));
    const profilePhotoUrl = computed(() => String(user.value?.profile_photo_url ?? ''));

    const initials = computed(() => {
        const source = displayName.value.trim();

        if (!source) {
            return 'A';
        }

        const letters = source
            .split(/\s+/)
            .filter(Boolean)
            .slice(0, 2)
            .map((part) => part.charAt(0).toUpperCase())
            .join('');

        return letters || source.charAt(0).toUpperCase();
    });
</script>

<template>
    <TextLink v-if="variant === 'sidebar'" aria-label="Profile" :href="route('user.profile.settings')" class="flex w-full items-center rounded-xl border border-transparent px-2 py-2 text-left transition hover:bg-surface-2/80 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand/40">
        <div class="flex w-full items-center justify-between gap-2">
            <div class="flex items-center gap-2">
                <span class="relative flex shrink-0">
                    <span class="absolute -bottom-0.5 -right-0.5 h-2.5 w-2.5 rounded-full border-2 border-background bg-success"></span>
                    <span class="grid shrink-0 place-items-center rounded-full bg-foreground text-[11px] font-semibold text-background"
                        :style="{
                            width: `${triggerAvatarPx}px`,
                            height: `${triggerAvatarPx}px`,
                            minWidth: `${triggerAvatarPx}px`,
                        }">
                        <img
                            v-if="profilePhotoUrl"
                            :src="profilePhotoUrl"
                            alt="Profile photo"
                            class="h-full w-full rounded-full object-cover"
                        >
                        <span v-else :class="triggerInitialsClass">{{ initials }}</span>
                    </span>
                </span>

                <div class="min-w-0 text-left">
                    <p class="truncate text-sm font-semibold tracking-tight text-foreground">{{ displayName }}</p>
                    <p class="truncate text-xs text-muted-foreground">{{ displayMeta }}</p>
                </div>
            </div>
        </div>
    </TextLink>

    <TextLink v-else :href="route('user.profile.settings')" aria-label="Profile" class="rounded-full p-1 transition hover:bg-surface-2 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand/40">
        <span class="grid h-8 w-8 place-items-center overflow-hidden rounded-full bg-foreground text-[11px] font-semibold text-background">
            <img
                v-if="profilePhotoUrl"
                :src="profilePhotoUrl"
                alt="Profile photo"
                class="h-full w-full object-cover"
            >
            <span v-else>{{ initials }}</span>
        </span>
    </TextLink>
</template>
