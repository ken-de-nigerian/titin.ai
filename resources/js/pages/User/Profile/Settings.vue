<script setup lang="ts">
    import { Head, router, useForm, usePage } from '@inertiajs/vue3';
    import { computed, onBeforeUnmount, ref, watch } from 'vue';

    import ActionButton from '@/components/ActionButton.vue';
    import AppLayout from '@/layouts/AppLayout.vue';
    import CustomSelectDropdown from '@/components/CustomSelectDropdown.vue';
    import FormInput from '@/components/FormInput.vue';
    import QuickActionModal from '@/components/QuickActionModal.vue';
    import { useRoute } from '@/composables/useRoute';

    type AuthUser = {
        id: number;
        name: string | null;
        email: string | null;
        email_verified?: boolean;
        job_role?: string | null;
        interview_type?: string | null;
        seniority_level?: string | null;
        prefers_concise_feedback?: boolean;
        profile_photo_url?: string | null;
    };

    const page = usePage();
    const route = useRoute();

    const user = computed(() => page.props.auth?.user as AuthUser | null | undefined);
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
    const senioritySettings = computed(() => ((page.props as any).settings?.seniority ?? {}) as {
        default_level?: string;
        levels?: Record<string, { label: string; description: string }>;
    });
    const seniorityOptions = computed(() =>
        Object.entries(senioritySettings.value.levels ?? {}).map(([value, option]) => ({
            value,
            label: option.label,
        })),
    );
    const fallbackSeniorityLevel = computed(
        () => senioritySettings.value.default_level ?? seniorityOptions.value[0]?.value ?? 'mid_level',
    );

    const displayName = computed(() => user.value?.name?.trim() || 'Your account');
    const displayEmail = computed(() => user.value?.email?.trim() || 'No email');

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
        const raw = user.value?.interview_type ?? fallbackInterviewType.value;
        const label = interviewSettings.value.types?.[raw];
        return label ?? raw.replace(/_/g, ' ');
    });

    const conciseFeedbackLabel = computed(() =>
        user.value?.prefers_concise_feedback ? 'Enabled' : 'Disabled',
    );
    const seniorityLabel = computed(() => {
        const raw = user.value?.seniority_level ?? fallbackSeniorityLevel.value;
        const label = senioritySettings.value.levels?.[raw]?.label;

        return label ?? raw.replace(/_/g, ' ');
    });

    const profileModalOpen = ref(false);
    const preferencesModalOpen = ref(false);
    const passwordModalOpen = ref(false);
    const deleteModalOpen = ref(false);
    const isLoggingOut = ref(false);

    const profileForm = useForm<{
        name: string;
        email: string;
        profile_photo: File | null;
    }>({
        name: user.value?.name ?? '',
        email: user.value?.email ?? '',
        profile_photo: null,
    });

    const preferencesForm = useForm<{
        job_role: string;
        interview_type: string;
        seniority_level: string;
        prefers_concise_feedback: boolean;
    }>({
        job_role: user.value?.job_role ?? '',
        interview_type: user.value?.interview_type ?? fallbackInterviewType.value,
        seniority_level: user.value?.seniority_level ?? fallbackSeniorityLevel.value,
        prefers_concise_feedback: Boolean(user.value?.prefers_concise_feedback),
    });

    const passwordForm = useForm<{
        current_password: string;
        password: string;
        password_confirmation: string;
    }>({
        current_password: '',
        password: '',
        password_confirmation: '',
    });

    const deleteAccountForm = useForm<{
        current_password: string;
        confirmation_text: string;
    }>({
        current_password: '',
        confirmation_text: '',
    });

    const selectedPhotoName = ref<string | null>(null);
    const selectedPhotoPreviewUrl = ref<string | null>(null);
    const photoInputRef = ref<HTMLInputElement | null>(null);
    const modalAvatarUrl = computed(
        () => selectedPhotoPreviewUrl.value ?? user.value?.profile_photo_url ?? null,
    );

    watch(
        () => user.value?.name,
        (v) => {
            profileForm.name = v ?? '';
            profileForm.defaults('name', v ?? '');
        },
    );

    watch(
        () => user.value?.email,
        (v) => {
            profileForm.email = v ?? '';
            profileForm.defaults('email', v ?? '');
        },
    );

    watch(
        () => user.value?.job_role,
        (v) => {
            if (v !== undefined && v !== null) {
                preferencesForm.job_role = v;
                preferencesForm.defaults('job_role', v);
            }
        },
    );

    watch(
        () => user.value?.interview_type,
        (v) => {
            if (v) {
                preferencesForm.interview_type = v;
                preferencesForm.defaults('interview_type', v);
            }
        },
    );

    watch(
        () => user.value?.prefers_concise_feedback,
        (v) => {
            const next = Boolean(v);
            preferencesForm.prefers_concise_feedback = next;
            preferencesForm.defaults('prefers_concise_feedback', next);
        },
    );

    watch(
        () => user.value?.seniority_level,
        (v) => {
            if (v) {
                preferencesForm.seniority_level = v;
                preferencesForm.defaults('seniority_level', v);
            }
        },
    );

    function openProfileModal(): void {
        profileModalOpen.value = true;
    }

    function closeProfileModal(): void {
        profileModalOpen.value = false;
        clearSelectedPhoto();
        profileForm.clearErrors();
    }

    function openPreferencesModal(): void {
        preferencesModalOpen.value = true;
    }

    function closePreferencesModal(): void {
        preferencesModalOpen.value = false;
        preferencesForm.clearErrors();
    }

    function openPasswordModal(): void {
        passwordModalOpen.value = true;
    }

    function closePasswordModal(): void {
        passwordModalOpen.value = false;
        passwordForm.clearErrors();
        passwordForm.reset();
    }

    function openDeleteModal(): void {
        deleteModalOpen.value = true;
    }

    function closeDeleteModal(): void {
        deleteModalOpen.value = false;
        deleteAccountForm.clearErrors();
        deleteAccountForm.reset();
    }

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
        profileForm.profile_photo = file;
        selectedPhotoPreviewUrl.value = file ? URL.createObjectURL(file) : null;
    }

    function clearSelectedPhoto(): void {
        clearPhotoPreview();
        selectedPhotoName.value = null;
        profileForm.profile_photo = null;
        if (photoInputRef.value) {
            photoInputRef.value.value = '';
        }
    }

    function saveProfileDetails(): void {
        profileForm.post(route('user.profile.details.update'), {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => {
                closeProfileModal();
            },
        });
    }

    function saveInterviewPreferences(): void {
        preferencesForm.post(route('user.profile.interview-preferences.update'), {
            preserveScroll: true,
            onSuccess: () => {
                closePreferencesModal();
            },
        });
    }

    function savePassword(): void {
        passwordForm.post(route('user.profile.password.update'), {
            preserveScroll: true,
            onSuccess: () => {
                closePasswordModal();
            },
        });
    }

    function deleteAccount(): void {
        deleteAccountForm.delete(route('user.profile.destroy'), {
            preserveScroll: true,
            onSuccess: () => {
                closeDeleteModal();
            },
        });
    }

    function signOut(): void {
        if (isLoggingOut.value) {
            return;
        }

        isLoggingOut.value = true;

        router.post(route('logout'), {}, {
            onFinish: () => {
                isLoggingOut.value = false;
            },
        });
    }

    onBeforeUnmount(() => {
        clearPhotoPreview();
    });
</script>

<template>
    <Head title="Profile & Settings" />

    <AppLayout>
        <main class="flex-1 overflow-y-auto px-5 pb-28 md:mx-auto md:w-full md:max-w-5xl md:px-0 md:pb-10">
            <!-- Identity -->
            <section class="surface rounded-2xl p-5 shadow-xs">
                <div class="flex flex-wrap items-start gap-4 sm:flex-nowrap sm:items-center">
                    <div class="grid h-14 w-14 shrink-0 place-items-center overflow-hidden rounded-full bg-foreground text-base font-semibold text-background">
                        <img v-if="user?.profile_photo_url" :src="user.profile_photo_url" alt="Profile photo" class="h-full w-full object-cover">
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
                    <p class="mt-0.5 text-xs text-muted-foreground">View your personal info and profile photo.</p>
                </div>

                <div class="surface divide-y divide-hairline overflow-hidden rounded-2xl shadow-xs">
                    <div class="px-4 py-4 sm:flex sm:items-start sm:justify-between sm:gap-6">
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-foreground">Full name</p>
                            <p class="mt-0.5 text-[11px] text-muted-foreground">Displayed across your account.</p>
                        </div>
                        <div class="mt-2 text-sm font-medium text-foreground sm:mt-0 sm:w-64 sm:text-right">
                            {{ displayName }}
                        </div>
                    </div>

                    <div class="px-4 py-4 sm:flex sm:items-start sm:justify-between sm:gap-6">
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-foreground">Email address</p>
                            <p class="mt-0.5 text-[11px] text-muted-foreground">Used for login and security notifications.</p>
                        </div>
                        <div class="mt-2 text-sm text-foreground sm:mt-0 sm:w-64 sm:text-right">
                            {{ displayEmail }}
                        </div>
                    </div>

                    <div class="px-4 py-4 sm:flex sm:items-center sm:justify-between sm:gap-6">
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-foreground">Profile photo</p>
                            <p class="mt-0.5 text-[11px] text-muted-foreground">Shown in your sidebar and account surfaces.</p>
                        </div>

                        <div class="mt-2 sm:mt-0">
                            <button type="button" class="inline-flex items-center justify-center rounded-lg border border-hairline bg-surface px-3 py-2 text-sm font-medium text-foreground transition hover:bg-surface-2" @click="openProfileModal">
                                Edit profile
                            </button>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Interview preferences -->
            <section class="mt-8">
                <div class="mb-3 px-1">
                    <h2 class="text-sm font-semibold tracking-tight text-foreground">Interview preferences</h2>
                    <p class="mt-0.5 text-xs text-muted-foreground">Defaults for new sessions used by the AI coach.</p>
                </div>

                <div class="surface divide-y divide-hairline overflow-hidden rounded-2xl shadow-xs">
                    <div class="px-4 py-4 sm:flex sm:items-start sm:justify-between sm:gap-6">
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-foreground">Default interview type</p>
                            <p class="mt-0.5 text-[11px] text-muted-foreground">What the AI coach optimizes for first.</p>
                        </div>
                        <div class="mt-2 text-sm text-foreground sm:mt-0 sm:w-72 sm:text-right">
                            {{ interviewLabel }}
                        </div>
                    </div>

                    <div class="px-4 py-4 sm:flex sm:items-start sm:justify-between sm:gap-6">
                        <div class="min-w-0">
                            <label for="pref-job-role" class="text-sm font-medium text-foreground">Target role</label>
                            <p class="mt-0.5 text-[11px] text-muted-foreground">Used to tailor prompts and framing.</p>
                        </div>
                        <div class="mt-2 text-sm text-foreground sm:mt-0 sm:w-72 sm:text-right">
                            {{ user?.job_role || 'Not set' }}
                        </div>
                    </div>

                    <div class="px-4 py-4 sm:flex sm:items-start sm:justify-between sm:gap-6">
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-foreground">Seniority level</p>
                            <p class="mt-0.5 text-[11px] text-muted-foreground">Used to calibrate depth and difficulty.</p>
                        </div>
                        <div class="mt-2 text-sm text-foreground sm:mt-0 sm:w-72 sm:text-right">
                            {{ seniorityLabel }}
                        </div>
                    </div>

                    <div class="flex items-center justify-between gap-3 px-4 py-3">
                        <p class="text-xs text-muted-foreground">Prefer concise AI feedback after each answer</p>
                        <span class="inline-flex items-center rounded-full border border-hairline bg-surface-2 px-2.5 py-0.5 text-[11px] font-medium text-foreground">
                            {{ conciseFeedbackLabel }}
                        </span>
                    </div>

                    <div class="flex items-center justify-end px-4 py-3">
                        <button type="button" class="inline-flex items-center justify-center rounded-lg border border-hairline bg-surface px-3 py-2 text-sm font-medium text-foreground transition hover:bg-surface-2" @click="openPreferencesModal">
                            Edit preferences
                        </button>
                    </div>
                </div>
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
                            <p class="mt-0.5 text-[11px] text-muted-foreground">Change your account password.</p>
                        </div>
                        <button type="button" class="shrink-0 rounded-lg border border-hairline bg-surface px-4 py-2 text-sm font-medium text-foreground transition hover:bg-surface-2" @click="openPasswordModal">
                            Change password
                        </button>
                    </div>
                    <div class="flex flex-col gap-3 px-4 py-4 sm:flex-row sm:items-start sm:justify-between">
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-medium text-foreground">Two-factor authentication</p>
                            <p class="mt-0.5 text-[11px] text-muted-foreground">
                                Adds a second step at sign-in.
                            </p>
                        </div>
                        <span class="inline-flex items-center rounded-full border border-hairline bg-surface-2 px-2.5 py-1 text-[11px] font-medium text-muted-foreground">
                            Coming soon
                        </span>
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
                    <a href="/user/billing" class="flex items-center gap-3 px-4 py-3.5 transition hover:bg-surface-2/80 active:bg-surface-2">
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
                        class="inline-flex items-center justify-center rounded-lg border border-destructive/40 bg-background px-4 py-2.5 text-sm font-medium text-destructive transition hover:bg-destructive/5"
                        @click="openDeleteModal"
                    >
                        Delete account
                    </button>
                </div>
            </section>

            <div class="mt-8 flex flex-col gap-3 sm:flex-row sm:justify-end">
                <button type="button" class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-hairline bg-surface px-4 py-3 text-sm font-medium text-destructive shadow-xs transition hover:bg-surface-2 active:bg-surface-2 sm:w-auto sm:max-w-xs sm:flex-initial" :disabled="isLoggingOut" @click="signOut">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4" aria-hidden="true">
                        <path d="m16 17 5-5-5-5"></path>
                        <path d="M21 12H9"></path>
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                    </svg>
                    {{ isLoggingOut ? 'Logging out...' : 'Sign out' }}
                </button>
            </div>
        </main>

        <QuickActionModal
            :is-open="profileModalOpen"
            title="Edit profile details"
            subtitle="Update your name, email, and profile image."
            class="rounded-2xl border border-hairline bg-background p-6 shadow-xl"
            @close="closeProfileModal">
            <div class="space-y-4">
                <div class="flex items-center gap-4">
                    <div class="grid h-14 w-14 shrink-0 place-items-center overflow-hidden rounded-full bg-foreground text-base font-semibold text-background">
                        <img v-if="modalAvatarUrl" :src="modalAvatarUrl" alt="Profile photo" class="h-full w-full object-cover">
                        <span v-else>{{ initials }}</span>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        <input ref="photoInputRef" type="file" accept="image/*" class="hidden" @change="handlePhotoChange">
                        <button type="button" class="inline-flex items-center justify-center rounded-lg border border-hairline bg-surface px-3 py-2 text-sm font-medium text-foreground transition hover:bg-surface-2" @click="openPhotoPicker">
                            Change photo
                        </button>

                        <button v-if="selectedPhotoName || profileForm.profile_photo" type="button" class="inline-flex items-center justify-center rounded-lg border border-hairline bg-surface px-3 py-2 text-sm font-medium text-muted-foreground transition hover:bg-surface-2" @click="clearSelectedPhoto">
                            Clear
                        </button>
                    </div>
                </div>

                <p v-if="selectedPhotoName" class="text-xs text-muted-foreground">{{ selectedPhotoName }}</p>
                <p v-if="profileForm.errors.profile_photo" class="text-xs text-destructive">{{ profileForm.errors.profile_photo }}</p>

                <FormInput
                    id="modal-profile-name"
                    v-model="profileForm.name"
                    label="Full name"
                    type="text"
                    placeholder="Alex Morgan"
                    autocomplete="name"
                    :error="profileForm.errors.name"
                    @focus="profileForm.clearErrors('name')"
                />

                <FormInput
                    id="modal-profile-email"
                    v-model="profileForm.email"
                    label="Email address"
                    type="email"
                    placeholder="you@email.com"
                    autocomplete="email"
                    :error="profileForm.errors.email"
                    @focus="profileForm.clearErrors('email')"
                />
            </div>

            <template #footer>
                <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                    <button type="button" class="inline-flex items-center justify-center rounded-lg border border-hairline bg-surface px-4 py-2 text-sm font-medium text-foreground transition hover:bg-surface-2" @click="closeProfileModal">
                        Cancel
                    </button>

                    <ActionButton type="button" :processing="profileForm.processing" class="mt-0! sm:w-auto!" @click="saveProfileDetails">
                        Save profile
                    </ActionButton>
                </div>
            </template>
        </QuickActionModal>

        <QuickActionModal
            :is-open="preferencesModalOpen"
            title="Edit interview preferences"
            subtitle="Control the session defaults used by your AI coach."
            class="rounded-2xl border border-hairline bg-background p-6 shadow-xl"
            @close="closePreferencesModal">
            <div class="space-y-4">
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-muted-foreground">Default interview type</label>
                    <CustomSelectDropdown
                        id="modal-interview-type"
                        v-model="preferencesForm.interview_type"
                        :options="interviewTypeOptions">
                        <template #default="{ selectedLabel }">{{ selectedLabel }}</template>
                    </CustomSelectDropdown>
                    <p v-if="preferencesForm.errors.interview_type" class="mt-1 text-xs text-destructive">
                        {{ preferencesForm.errors.interview_type }}
                    </p>
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-medium text-muted-foreground">Seniority level</label>
                    <CustomSelectDropdown
                        id="modal-seniority-level"
                        v-model="preferencesForm.seniority_level"
                        :options="seniorityOptions">
                        <template #default="{ selectedLabel }">{{ selectedLabel }}</template>
                    </CustomSelectDropdown>
                    <p v-if="preferencesForm.errors.seniority_level" class="mt-1 text-xs text-destructive">
                        {{ preferencesForm.errors.seniority_level }}
                    </p>
                </div>

                <FormInput
                    id="modal-job-role"
                    v-model="preferencesForm.job_role"
                    label="Target role"
                    type="text"
                    autocomplete="organization-title"
                    placeholder="e.g. Senior Product Manager"
                    :error="preferencesForm.errors.job_role"
                    @focus="preferencesForm.clearErrors('job_role')"
                />

                <div class="flex items-center justify-between gap-3 rounded-lg border border-hairline bg-surface p-3">
                    <p class="text-sm text-foreground">Prefer concise AI feedback after each answer</p>
                    <button
                        type="button"
                        role="switch"
                        :aria-checked="preferencesForm.prefers_concise_feedback"
                        class="relative inline-flex h-6 w-11 shrink-0 rounded-full transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand/40"
                        :class="preferencesForm.prefers_concise_feedback ? 'bg-foreground' : 'bg-muted'"
                        @click="preferencesForm.prefers_concise_feedback = !preferencesForm.prefers_concise_feedback">
                        <span
                            class="pointer-events-none inline-block h-5 w-5 translate-y-0.5 rounded-full bg-background shadow-sm transition-transform"
                            :class="preferencesForm.prefers_concise_feedback ? 'translate-x-5' : 'translate-x-0.5'"
                        ></span>
                    </button>
                </div>
            </div>

            <template #footer>
                <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                    <button type="button" class="inline-flex items-center justify-center rounded-lg border border-hairline bg-surface px-4 py-2 text-sm font-medium text-foreground transition hover:bg-surface-2" @click="closePreferencesModal">
                        Cancel
                    </button>

                    <ActionButton type="button" :processing="preferencesForm.processing" class="mt-0! sm:w-auto!" @click="saveInterviewPreferences">
                        Save preferences
                    </ActionButton>
                </div>
            </template>
        </QuickActionModal>

        <QuickActionModal
            :is-open="passwordModalOpen"
            title="Change password"
            subtitle="Update your password to keep your account secure."
            class="rounded-2xl border border-hairline bg-background p-6 shadow-xl"
            @close="closePasswordModal">
            <div class="space-y-4">
                <FormInput
                    id="modal-current-password"
                    v-model="passwordForm.current_password"
                    label="Current password"
                    type="password"
                    autocomplete="current-password"
                    placeholder="Enter your current password"
                    :error="passwordForm.errors.current_password"
                    @focus="passwordForm.clearErrors('current_password')"
                />

                <FormInput
                    id="modal-new-password"
                    v-model="passwordForm.password"
                    label="New password"
                    type="password"
                    autocomplete="new-password"
                    placeholder="Enter a new password"
                    :error="passwordForm.errors.password"
                    @focus="passwordForm.clearErrors('password')"
                />

                <FormInput
                    id="modal-password-confirmation"
                    v-model="passwordForm.password_confirmation"
                    label="Confirm new password"
                    type="password"
                    autocomplete="new-password"
                    placeholder="Confirm new password"
                    :error="passwordForm.errors.password_confirmation"
                    @focus="passwordForm.clearErrors('password_confirmation')"
                />
            </div>

            <template #footer>
                <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                    <button type="button" class="inline-flex items-center justify-center rounded-lg border border-hairline bg-surface px-4 py-2 text-sm font-medium text-foreground transition hover:bg-surface-2" @click="closePasswordModal">
                        Cancel
                    </button>

                    <ActionButton type="button" :processing="passwordForm.processing" class="mt-0! sm:w-auto!" @click="savePassword">
                        Update password
                    </ActionButton>
                </div>
            </template>
        </QuickActionModal>

        <QuickActionModal
            :is-open="deleteModalOpen"
            title="Delete account"
            subtitle="This action is permanent and cannot be undone."
            class="rounded-2xl border border-destructive/30 bg-background p-6 shadow-xl"
            @close="closeDeleteModal">
            <div class="space-y-4">
                <div class="rounded-lg border border-destructive/20 bg-destructive/5 px-3 py-2 text-xs text-destructive">
                    Deleting your account will remove your profile data and cannot be reversed.
                </div>

                <FormInput
                    id="modal-delete-current-password"
                    v-model="deleteAccountForm.current_password"
                    label="Current password"
                    type="password"
                    autocomplete="current-password"
                    placeholder="Enter your current password"
                    :error="deleteAccountForm.errors.current_password"
                    @focus="deleteAccountForm.clearErrors('current_password')"
                />

                <FormInput
                    id="modal-delete-confirmation-text"
                    v-model="deleteAccountForm.confirmation_text"
                    label="Type DELETE to confirm"
                    type="text"
                    autocomplete="off"
                    placeholder="DELETE"
                    :error="deleteAccountForm.errors.confirmation_text"
                    @focus="deleteAccountForm.clearErrors('confirmation_text')"
                />
            </div>

            <template #footer>
                <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                    <button type="button" class="inline-flex items-center justify-center rounded-lg border border-hairline bg-surface px-4 py-2 text-sm font-medium text-foreground transition hover:bg-surface-2" @click="closeDeleteModal">
                        Cancel
                    </button>

                    <ActionButton type="button" :processing="deleteAccountForm.processing" class="mt-0! sm:w-auto! bg-destructive! text-destructive-foreground! hover:bg-destructive/90!" @click="deleteAccount">
                        Permanently delete account
                    </ActionButton>
                </div>
            </template>
        </QuickActionModal>
    </AppLayout>
</template>
