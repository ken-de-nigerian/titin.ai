<script setup lang="ts">
    import { Head, router, useForm, usePage } from '@inertiajs/vue3';
    import { computed, onBeforeUnmount, ref, watch } from 'vue';

    import ActionButton from '@/components/ActionButton.vue';
    import AppLayout from '@/layouts/AppLayout.vue';
    import CustomSelectDropdown from '@/components/CustomSelectDropdown.vue';
    import FormInput from '@/components/FormInput.vue';
    import { useRoute } from '@/composables/useRoute';

    type AuthUser = {
        id: number;
        name: string | null;
        email: string | null;
        email_verified?: boolean;
        job_role?: string | null;
        interview_type?: string | null;
        profile_photo_url?: string | null;
    };

    const page = usePage();
    const route = useRoute();

    const user = computed(() => page.props.auth?.user as AuthUser | null | undefined);

    const displayName = computed(() => user.value?.name?.trim() || 'Your account');

    const initials = computed(() => {
        const n = displayName.value.trim();
        if (!n || n === 'Your account') {
            return '?';
        }
        const parts = n.split(/\s+/).filter(Boolean);
        if (parts.length >= 2) {
            return `${parts[0]?.charAt(0) ?? ''}${parts[1]?.charAt(0) ?? ''}`.toUpperCase();
        }

        return n.slice(0, 2).toUpperCase();
    });

    const interviewLabel = computed(() => {
        const raw = user.value?.interview_type ?? 'behavioral';
        const labels: Record<string, string> = {
            behavioral: 'Behavioral',
            technical: 'Technical',
            role_specific: 'Role-specific',
        };

        return labels[raw] ?? raw.replace(/_/g, ' ');
    });

    const form = useForm<{
        name: string;
        email: string;
        profile_photo: File | null;
    }>({
        name: user.value?.name ?? '',
        email: user.value?.email ?? '',
        profile_photo: null,
    });

    const preferenceInterviewType = ref(user.value?.interview_type ?? 'behavioral');
    const preferenceJobRole = ref(user.value?.job_role ?? '');
    const selectedPhotoName = ref<string | null>(null);
    const selectedPhotoPreviewUrl = ref<string | null>(null);
    const photoInputRef = ref<HTMLInputElement | null>(null);
    const interviewTypeOptions = [
        { value: 'behavioral', label: 'Behavioral' },
        { value: 'technical', label: 'Technical' },
        { value: 'role_specific', label: 'Role-specific' },
    ] as const;

    watch(
        () => user.value?.interview_type,
        (v) => {
            if (v) {
                preferenceInterviewType.value = v;
            }
        },
    );

    watch(
        () => user.value?.job_role,
        (v) => {
            if (v !== undefined && v !== null) {
                preferenceJobRole.value = v;
            }
        },
    );

    watch(
        () => user.value?.name,
        (v) => {
            form.name = v ?? '';
            form.defaults('name', v ?? '');
        },
    );

    watch(
        () => user.value?.email,
        (v) => {
            form.email = v ?? '';
            form.defaults('email', v ?? '');
        },
    );

    function openPhotoPicker(): void {
        photoInputRef.value?.click();
    }

    function clearPhotoPreview(): void {
        if (selectedPhotoPreviewUrl.value !== null) {
            URL.revokeObjectURL(selectedPhotoPreviewUrl.value);
            selectedPhotoPreviewUrl.value = null;
        }
    }

    function handlePhotoChange(event: Event): void {
        const file = (event.target as HTMLInputElement).files?.[0] ?? null;
        clearPhotoPreview();
        selectedPhotoName.value = file?.name ?? null;
        form.profile_photo = file;
        selectedPhotoPreviewUrl.value = file ? URL.createObjectURL(file) : null;
    }

    function clearSelectedPhoto(): void {
        clearPhotoPreview();
        selectedPhotoName.value = null;
        form.profile_photo = null;

        if (photoInputRef.value) {
            photoInputRef.value.value = '';
        }
    }

    function saveProfile(): void {
        form.post(route('user.profile.update'), {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => {
                clearSelectedPhoto();
            },
        });
    }

    function signOut(): void {
        router.post(route('logout'));
    }

    onBeforeUnmount(() => {
        clearPhotoPreview();
    });

    const clearError = (field: keyof typeof form.errors): void => {
        if (form.errors[field]) {
            form.clearErrors(field);
        }
    };

    const displayedAvatarUrl = computed(() => selectedPhotoPreviewUrl.value ?? user.value?.profile_photo_url ?? null);
</script>

<template>
    <Head title="Profile & Settings" />

    <AppLayout>
        <main class="flex-1 overflow-y-auto px-5 pb-28 md:mx-auto md:w-full md:max-w-5xl md:px-0 md:pb-10">
            <!-- Identity -->
            <section class="surface rounded-2xl p-5 shadow-xs">
                <div class="flex flex-wrap items-start gap-4 sm:flex-nowrap sm:items-center">
                    <div class="grid h-14 w-14 shrink-0 place-items-center overflow-hidden rounded-full bg-foreground text-base font-semibold text-background">
                        <img
                            v-if="displayedAvatarUrl"
                            :src="displayedAvatarUrl"
                            alt="Profile photo"
                            class="h-full w-full object-cover"
                        >
                        <span v-else>{{ initials }}</span>
                    </div>

                    <div class="min-w-0 flex-1">
                        <p class="truncate text-base font-semibold tracking-tight text-foreground">{{ displayName }}</p>
                        <p v-if="user?.email" class="mt-0.5 truncate text-sm text-muted-foreground">{{ user.email }}</p>
                        <div class="mt-2 flex flex-wrap items-center gap-2">
                            <span v-if="user?.job_role" class="inline-flex items-center rounded-full border border-hairline bg-surface-2 px-2.5 py-0.5 text-[11px] font-medium text-foreground">
                                {{ user.job_role }}
                            </span>

                            <span class="inline-flex items-center rounded-full border border-hairline bg-brand-soft px-2.5 py-0.5 text-[11px] font-medium text-brand">
                                {{ interviewLabel }}
                            </span>

                            <span v-if="user?.email_verified === false" class="inline-flex items-center rounded-full border border-warning/40 bg-warning/10 px-2.5 py-0.5 text-[11px] font-medium text-warning">
                                Email unverified
                            </span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Profile details -->
            <section id="profile-details" class="mt-8">
                <div class="mb-3 px-1">
                    <h2 class="text-sm font-semibold tracking-tight text-foreground">Profile details</h2>
                    <p class="mt-0.5 text-xs text-muted-foreground">Update your personal info and profile photo.</p>
                </div>
                <div class="surface divide-y divide-hairline overflow-hidden rounded-2xl shadow-xs">
                    <div class="px-4 py-4 sm:flex sm:items-start sm:justify-between sm:gap-6">
                        <div class="min-w-0">
                            <label for="profile-name" class="text-sm font-medium text-foreground">Full name</label>
                            <p class="mt-0.5 text-[11px] text-muted-foreground">Displayed across your account.</p>
                        </div>
                        <div class="mt-3 sm:mt-0 sm:w-64 sm:shrink-0">
                            <FormInput
                                id="profile-name"
                                v-model="form.name"
                                label="Full name"
                                placeholder="Alex Morgan"
                                :show-field-label="false"
                                type="text"
                                autocomplete="name"
                                :error="form.errors.name"
                                @focus="clearError('name')"
                            />
                        </div>
                    </div>

                    <div class="px-4 py-4 sm:flex sm:items-start sm:justify-between sm:gap-6">
                        <div class="min-w-0">
                            <label for="profile-email" class="text-sm font-medium text-foreground">Email address</label>
                            <p class="mt-0.5 text-[11px] text-muted-foreground">Used for login and security notifications.</p>
                        </div>
                        <div class="mt-3 sm:mt-0 sm:w-64 sm:shrink-0">
                            <FormInput
                                id="profile-email"
                                v-model="form.email"
                                label="Email address"
                                placeholder="you@email.com"
                                :show-field-label="false"
                                type="email"
                                autocomplete="email"
                                :error="form.errors.email"
                                @focus="clearError('email')"
                            />
                        </div>
                    </div>

                    <div id="profile-photo" class="px-4 py-4 sm:flex sm:items-start sm:justify-between sm:gap-6">
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-foreground">Profile photo</p>
                            <p class="mt-0.5 text-[11px] text-muted-foreground">Shown in your sidebar and account surfaces.</p>
                        </div>

                        <div class="mt-3 flex flex-wrap items-center gap-2 sm:mt-0 sm:shrink-0">
                            <input ref="photoInputRef" type="file" accept="image/*" class="hidden" @change="handlePhotoChange">
                            <button type="button" class="inline-flex items-center justify-center rounded-lg border border-hairline bg-surface px-3 py-2 text-sm font-medium text-foreground transition hover:bg-surface-2" @click="openPhotoPicker">
                                Change photo
                            </button>

                            <button v-if="selectedPhotoName" type="button" class="inline-flex items-center justify-center rounded-lg border border-hairline bg-surface px-3 py-2 text-sm font-medium text-muted-foreground transition hover:bg-surface-2" @click="clearSelectedPhoto">
                                Clear
                            </button>
                        </div>
                    </div>

                    <p v-if="form.errors.profile_photo" class="px-4 pb-2 text-xs text-destructive">
                        {{ form.errors.profile_photo }}
                    </p>

                    <div class="flex items-center justify-end px-4 py-3">
                        <ActionButton
                            type="button"
                            :processing="form.processing"
                            class="mt-0! w-auto! px-4 py-2"
                            @click="saveProfile">
                            Save changes
                        </ActionButton>
                    </div>
                </div>
            </section>

            <!-- Interview preferences (UI only — persist later) -->
            <section class="mt-8">
                <div class="mb-3 px-1">
                    <h2 class="text-sm font-semibold tracking-tight text-foreground">Interview preferences</h2>
                    <p class="mt-0.5 text-xs text-muted-foreground">Defaults for new sessions. Saving will connect to your account later.</p>
                </div>
                <div class="surface divide-y divide-hairline overflow-hidden rounded-2xl shadow-xs">
                    <div class="px-4 py-4 sm:flex sm:items-start sm:justify-between sm:gap-6">
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-foreground">Default interview type</p>
                            <p class="mt-0.5 text-[11px] text-muted-foreground">What the AI coach optimizes for first.</p>
                        </div>
                        <div class="mt-3 sm:mt-0 sm:w-56 sm:shrink-0">
                            <label class="sr-only" for="pref-interview-type">Default interview type</label>
                            <CustomSelectDropdown
                                id="pref-interview-type"
                                v-model="preferenceInterviewType"
                                :options="interviewTypeOptions">
                                <template #default="{ selectedLabel }">{{ selectedLabel }}</template>
                            </CustomSelectDropdown>
                        </div>
                    </div>

                    <div class="px-4 py-4 sm:flex sm:items-start sm:justify-between sm:gap-6">
                        <div class="min-w-0">
                            <label for="pref-job-role" class="text-sm font-medium text-foreground">Target role</label>
                            <p class="mt-0.5 text-[11px] text-muted-foreground">Used to tailor prompts and framing.</p>
                        </div>
                        <div class="mt-3 sm:mt-0 sm:w-56 sm:shrink-0">
                            <FormInput
                                id="pref-job-role"
                                v-model="preferenceJobRole"
                                label="Target role"
                                :show-field-label="false"
                                type="text"
                                autocomplete="organization-title"
                                placeholder="e.g. Senior Product Manager"
                            />
                        </div>
                    </div>

                    <div class="flex items-center justify-between gap-3 px-4 py-3">
                        <p class="text-xs text-muted-foreground">Prefer concise AI feedback after each answer</p>
                        <button
                            type="button"
                            disabled
                            class="relative inline-flex h-6 w-11 shrink-0 cursor-not-allowed rounded-full bg-muted opacity-70"
                            title="Coming soon"
                            aria-disabled="true"
                        >
                            <span class="pointer-events-none inline-block h-5 w-5 translate-x-0.5 translate-y-0.5 rounded-full bg-background shadow-sm"></span>
                        </button>
                    </div>
                </div>
                <p class="mt-3 px-1 text-xs text-muted-foreground">Preference changes here are preview-only until backed by your account settings.</p>
            </section>

            <!-- Security -->
            <section class="mt-8">
                <div class="mb-3 px-1">
                    <h2 class="text-sm font-semibold tracking-tight text-foreground">Security</h2>
                    <p class="mt-0.5 text-xs text-muted-foreground">Password and sign-in protection.</p>
                </div>
                <div class="surface divide-y divide-hairline overflow-hidden rounded-2xl shadow-xs">
                    <div class="flex flex-col gap-3 px-4 py-4 sm:flex-row sm:items-center sm:justify-between">
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-foreground">Password</p>
                            <p class="mt-0.5 text-[11px] text-muted-foreground">Change your password or send a reset link to your email.</p>
                        </div>
                        <button
                            type="button"
                            disabled
                            class="shrink-0 rounded-lg border border-hairline bg-surface px-4 py-2 text-sm font-medium text-muted-foreground opacity-70"
                            aria-disabled="true"
                        >
                            Change password
                        </button>
                    </div>
                    <div class="flex flex-col gap-3 px-4 py-4 sm:flex-row sm:items-start sm:justify-between">
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-medium text-foreground">Two-factor authentication</p>
                            <p class="mt-0.5 text-[11px] text-muted-foreground">
                                Adds a second step at sign-in. Enrollment will be enabled when wired to your backend.
                            </p>
                        </div>
                        <button
                            type="button"
                            role="switch"
                            aria-checked="false"
                            class="relative inline-flex h-6 w-11 shrink-0 rounded-full bg-muted transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand/40 disabled:cursor-not-allowed disabled:opacity-50"
                            disabled
                            aria-disabled="true"
                            aria-label="Two-factor authentication (coming soon)"
                        >
                            <span
                                class="pointer-events-none inline-block h-5 w-5 translate-x-0.5 translate-y-0.5 rounded-full bg-background shadow-sm transition-transform"
                            ></span>
                        </button>
                    </div>
                </div>
            </section>

            <!-- Account quick links -->
            <section class="mt-8">
                <div class="mb-3 px-1">
                    <h2 class="text-sm font-semibold tracking-tight text-foreground">Account</h2>
                    <p class="mt-0.5 text-xs text-muted-foreground">Billing and notifications.</p>
                </div>
                <div class="surface divide-y divide-hairline overflow-hidden rounded-2xl shadow-xs">
                    <a
                        href="#profile-details"
                        class="flex items-center gap-3 px-4 py-3.5 transition hover:bg-surface-2/80 active:bg-surface-2"
                    >
                        <div class="grid h-8 w-8 place-items-center rounded-lg bg-surface-2 text-foreground">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4" aria-hidden="true">
                                <path d="M12 20h9"></path>
                                <path d="M16.5 3.5a2.121 2.121 0 1 1 3 3L7 19l-4 1 1-4Z"></path>
                            </svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-medium">Edit profile details</p>
                            <p class="text-[11px] text-muted-foreground">Name, email, and profile image</p>
                        </div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4 shrink-0 text-muted-foreground" aria-hidden="true">
                            <path d="m9 18 6-6-6-6"></path>
                        </svg>
                    </a>
                    <a
                        href="/user/billing"
                        class="flex items-center gap-3 px-4 py-3.5 transition hover:bg-surface-2/80 active:bg-surface-2"
                    >
                        <div class="grid h-8 w-8 place-items-center rounded-lg bg-surface-2 text-foreground">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                width="24"
                                height="24"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                class="h-4 w-4"
                                aria-hidden="true"
                            >
                                <rect width="20" height="14" x="2" y="5" rx="2"></rect>
                                <line x1="2" x2="22" y1="10" y2="10"></line>
                            </svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-medium">Billing &amp; credits</p>
                            <p class="text-[11px] text-muted-foreground">Plans, invoices, and credit balance</p>
                        </div>
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            width="24"
                            height="24"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            class="h-4 w-4 shrink-0 text-muted-foreground"
                            aria-hidden="true"
                        >
                            <path d="m9 18 6-6-6-6"></path>
                        </svg>
                    </a>
                    <button type="button" disabled class="flex w-full cursor-not-allowed items-center gap-3 px-4 py-3.5 text-left opacity-60" aria-disabled="true">
                        <div class="grid h-8 w-8 place-items-center rounded-lg bg-surface-2 text-foreground">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                width="24"
                                height="24"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                class="h-4 w-4"
                                aria-hidden="true"
                            >
                                <path d="M10.268 21a2 2 0 0 0 3.464 0"></path>
                                <path
                                    d="M3.262 15.326A1 1 0 0 0 4 17h16a1 1 0 0 0 .74-1.673C19.41 13.956 18 12.499 18 8A6 6 0 0 0 6 8c0 4.499-1.411 5.956-2.738 7.326"
                                ></path>
                            </svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-medium">Notifications</p>
                            <p class="text-[11px] text-muted-foreground">Email &amp; reminders (coming soon)</p>
                        </div>
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            width="24"
                            height="24"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            class="h-4 w-4 shrink-0 text-muted-foreground"
                            aria-hidden="true"
                        >
                            <path d="m9 18 6-6-6-6"></path>
                        </svg>
                    </button>
                </div>
            </section>

            <!-- Danger zone -->
            <section class="mt-8 rounded-2xl border border-destructive/25 bg-destructive/5 p-5">
                <h2 class="text-sm font-semibold tracking-tight text-destructive">Danger zone</h2>
                <p class="mt-1 text-xs text-muted-foreground">
                    Irreversible actions for your account and practice history. Confirmation will be added with backend support.
                </p>
                <div class="mt-4 flex flex-col gap-3 sm:flex-row sm:flex-wrap">
                    <button
                        type="button"
                        disabled
                        class="inline-flex cursor-not-allowed items-center justify-center rounded-lg border border-hairline bg-background px-4 py-2.5 text-sm font-medium text-muted-foreground opacity-60"
                        aria-disabled="true"
                    >
                        Export my data
                    </button>
                    <button
                        type="button"
                        disabled
                        class="inline-flex cursor-not-allowed items-center justify-center rounded-lg border border-destructive/40 bg-background px-4 py-2.5 text-sm font-medium text-destructive opacity-70"
                        aria-disabled="true"
                    >
                        Delete account
                    </button>
                </div>
            </section>

            <div class="mt-8 flex flex-col gap-3 sm:flex-row sm:justify-end">
                <button
                    type="button"
                    class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-hairline bg-surface px-4 py-3 text-sm font-medium text-destructive shadow-xs transition hover:bg-surface-2 active:bg-surface-2 sm:w-auto sm:max-w-xs sm:flex-initial"
                    @click="signOut"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4" aria-hidden="true">
                        <path d="m16 17 5-5-5-5"></path>
                        <path d="M21 12H9"></path>
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                    </svg>
                    Sign out
                </button>
            </div>
        </main>
    </AppLayout>
</template>
