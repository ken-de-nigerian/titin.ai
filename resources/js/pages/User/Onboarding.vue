<script setup lang="ts">
import {Head, router, useForm, usePage} from '@inertiajs/vue3';
    import {computed, ref} from 'vue';
    import ActionButton from '@/components/ActionButton.vue';
    import FormInput from '@/components/FormInput.vue';
    import { useRoute } from '@/composables/useRoute';
    import SiteLogo from "@/components/SiteLogo.vue";

    const route = useRoute();
    const page = usePage();
    const isLoggingOut = ref(false);
    const isResumeAnalyzing = ref(false);
    let resumeAnalyzeTimer: ReturnType<typeof setTimeout> | null = null;
    const authUser = computed(() => (page.props as { auth?: { user?: { name?: string } } }).auth?.user ?? null);

    type InterviewSettings = {
        default_type?: string;
        types?: Record<string, string>;
    };
    type SeniorityLevelSettings = {
        label: string;
        description: string;
    };
    type SenioritySettings = {
        default_level?: string;
        levels?: Record<string, SeniorityLevelSettings>;
    };

    const interviewSettings = computed(
        () => (page.props.settings as { interview?: InterviewSettings } | undefined)?.interview,
    );

    const interviewTypeOptions = computed(() =>
        Object.entries(interviewSettings.value?.types ?? {}).map(([value, label]) => ({ value, label })),
    );

    const defaultInterviewType = computed(() =>
        interviewSettings.value?.default_type ?? interviewTypeOptions.value[0]?.value ?? 'mixed',
    );
    const senioritySettings = computed(
        () => (page.props.settings as { seniority?: SenioritySettings } | undefined)?.seniority,
    );
    const seniorityOptions = computed(() =>
        Object.entries(senioritySettings.value?.levels ?? {}).map(([value, option]) => ({
            value,
            label: option.label,
            description: option.description,
        })),
    );
    const defaultSeniorityLevel = computed(
        () => senioritySettings.value?.default_level ?? seniorityOptions.value[0]?.value ?? 'mid_level',
    );

    const props = defineProps<{
        prefill: {
            name: string;
            job_role: string;
            interview_type: string;
            seniority_level: string;
        };
    }>();

    const form = useForm<{
        job_role: string;
        interview_type: string;
        seniority_level: string;
        resume: File | null;
    }>({
        job_role: props.prefill.job_role ?? '',
        interview_type: (
            interviewTypeOptions.value.some((option) => option.value === props.prefill.interview_type)
                ? props.prefill.interview_type
                : defaultInterviewType.value
        ),
        seniority_level: (
            seniorityOptions.value.some((option) => option.value === props.prefill.seniority_level)
                ? props.prefill.seniority_level
                : defaultSeniorityLevel.value
        ),
        resume: null,
    });

    const handleResumeChange = (event: Event): void => {
        const input = event.target as HTMLInputElement;
        const selectedFile = input.files?.[0] ?? null;
        form.resume = selectedFile;

        if (resumeAnalyzeTimer) {
            clearTimeout(resumeAnalyzeTimer);
            resumeAnalyzeTimer = null;
        }

        if (!selectedFile) {
            isResumeAnalyzing.value = false;

            return;
        }

        isResumeAnalyzing.value = true;
        resumeAnalyzeTimer = setTimeout(() => {
            isResumeAnalyzing.value = false;
            resumeAnalyzeTimer = null;
        }, 1400);
    };

    const clearResume = (): void => {
        form.resume = null;
        isResumeAnalyzing.value = false;
        if (resumeAnalyzeTimer) {
            clearTimeout(resumeAnalyzeTimer);
            resumeAnalyzeTimer = null;
        }
    };

    const submit = (): void => {
        form.post(route('user.onboarding.update'), {
            preserveScroll: true,
            forceFormData: true,
        });
    };

    function logout() {
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

    const clearError = (field: keyof typeof form.errors): void => {
        if (form.errors[field]) {
            form.clearErrors(field);
        }
    };
</script>

<template>
    <Head title="Onboarding" />

    <div class="min-h-screen bg-background">
        <header class="sticky top-0 z-40 border-b border-hairline bg-background/95 backdrop-blur">
            <div class="container mx-auto px-6 py-4 flex items-center justify-between">
                <SiteLogo />

                <button
                    type="button"
                    class="inline-flex h-10 items-center justify-center gap-2 whitespace-nowrap rounded-lg px-5 py-2 text-sm font-semibold text-muted-foreground ring-offset-background transition-all duration-200 bg-surface-2 hover:text-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&_svg]:pointer-events-none [&_svg]:size-4 [&_svg]:shrink-0"
                    :disabled="isLoggingOut"
                    @click="logout">
                    <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em">
                        <path d="M8.8999 7.56C9.2099 3.96 11.0599 2.49 15.1099 2.49H15.2399C19.7099 2.49 21.4999 4.28 21.4999 8.75V15.27C21.4999 19.74 19.7099 21.53 15.2399 21.53H15.1099C11.0899 21.53 9.2399 20.08 8.9099 16.54" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"></path>
                        <path d="M15.0001 12H3.62012" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"></path>
                        <path d="M5.85 8.65L2.5 12L5.85 15.35" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"></path>
                    </svg>
                    {{ isLoggingOut ? 'Logging out...' : 'Logout' }}
                </button>
            </div>
        </header>

        <main class="container mx-auto max-w-3xl px-6 py-8">
            <div>
                <p class="text-xs font-medium uppercase tracking-wider text-brand">Welcome, {{ authUser?.name}}</p>
                <h1 class="mt-3 text-2xl font-semibold tracking-tight md:text-3xl">Let’s tailor your practice sessions</h1>
                <p class="mt-2 text-sm text-muted-foreground">
                    Tell us what you’re preparing for. You can update this later.
                </p>

                <form class="mt-8 space-y-5" @submit.prevent="submit">
                    <FormInput
                        id="job_role"
                        v-model="form.job_role"
                        label="Role you’re preparing for"
                        type="text"
                        placeholder="Software Engineer"
                        autocomplete="organization-title"
                        :error="form.errors.job_role"
                        @focus="clearError('job_role')"
                    />

                    <div class="mb-6 rounded-lg border border-hairline bg-surface text-foreground shadow-sm">
                        <div class="flex flex-col space-y-1.5 p-6">
                            <h3 class="font-semibold tracking-tight text-lg flex items-center gap-2">
                                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" width="24" height="24">
                                    <path d="M7.99983 22H15.9998C20.0198 22 20.7398 20.39 20.9498 18.43L21.6998 10.43C21.9698 7.99 21.2698 6 16.9998 6H6.99983C2.72983 6 2.02983 7.99 2.29983 10.43L3.04983 18.43C3.25983 20.39 3.97983 22 7.99983 22Z" stroke="currentColor" stroke-width="1.75" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                    <path d="M8 6V5.2C8 3.43 8 2 11.2 2H12.8C16 2 16 3.43 16 5.2V6" stroke="currentColor" stroke-width="1.75" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                    <path d="M14 13V14C14 14.01 14 14.01 14 14.02C14 15.11 13.99 16 12 16C10.02 16 10 15.12 10 14.03V13C10 12 10 12 11 12H13C14 12 14 12 14 13Z" stroke="currentColor" stroke-width="1.75" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                    <path d="M21.65 11C19.34 12.68 16.7 13.68 14 14.02" stroke="currentColor" stroke-width="1.75" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                    <path d="M2.62012 11.27C4.87012 12.81 7.41012 13.74 10.0001 14.03" stroke="currentColor" stroke-width="1.75" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                </svg>
                                Interview Type
                            </h3>
                            <p class="text-sm text-muted-foreground">Select the interview format you want to practice.</p>
                        </div>

                        <div class="p-6 pt-0">
                            <fieldset>
                                <legend class="sr-only">Interview type</legend>
                                <div class="grid grid-cols-2 gap-3 md:grid-cols-4">
                                    <label v-for="option in interviewTypeOptions" :key="option.value" class="flex cursor-pointer flex-col items-center gap-2 rounded-xl border-2 p-4 transition-all hover:border-accent/50" :class="form.interview_type === option.value ? 'border-brand bg-brand-soft' : 'border-hairline bg-surface'">
                                        <input v-model="form.interview_type" type="radio" name="interview_type" class="sr-only" :value="option.value">
                                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" width="24" height="24">
                                            <path d="M7.99983 22H15.9998C20.0198 22 20.7398 20.39 20.9498 18.43L21.6998 10.43C21.9698 7.99 21.2698 6 16.9998 6H6.99983C2.72983 6 2.02983 7.99 2.29983 10.43L3.04983 18.43C3.25983 20.39 3.97983 22 7.99983 22Z" stroke="currentColor" stroke-width="1.75" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                            <path d="M8 6V5.2C8 3.43 8 2 11.2 2H12.8C16 2 16 3.43 16 5.2V6" stroke="currentColor" stroke-width="1.75" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                            <path d="M14 13V14C14 14.01 14 14.01 14 14.02C14 15.11 13.99 16 12 16C10.02 16 10 15.12 10 14.03V13C10 12 10 12 11 12H13C14 12 14 12 14 13Z" stroke="currentColor" stroke-width="1.75" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                            <path d="M21.65 11C19.34 12.68 16.7 13.68 14 14.02" stroke="currentColor" stroke-width="1.75" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                            <path d="M2.62012 11.27C4.87012 12.81 7.41012 13.74 10.0001 14.03" stroke="currentColor" stroke-width="1.75" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                        </svg>
                                        <span class="text-sm font-medium text-center text-muted-foreground">
                                            {{ option.label }}
                                        </span>
                                    </label>
                                </div>
                            </fieldset>

                            <p v-if="form.errors.interview_type" class="mt-2 text-xs text-destructive">
                                {{ form.errors.interview_type }}
                            </p>
                        </div>
                    </div>

                    <div class="mb-8 rounded-lg border border-hairline bg-surface text-foreground shadow-sm">
                        <div class="flex flex-col space-y-1.5 p-6">
                            <h3 class="font-semibold tracking-tight text-lg flex items-center gap-2">
                                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" width="24" height="24">
                                    <path d="M10.05 2.53004L4.03002 6.46004C2.10002 7.72004 2.10002 10.54 4.03002 11.8L10.05 15.73C11.13 16.44 12.91 16.44 13.99 15.73L19.98 11.8C21.9 10.54 21.9 7.73004 19.98 6.47004L13.99 2.54004C12.91 1.82004 11.13 1.82004 10.05 2.53004Z" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"></path>
                                    <path d="M5.63012 13.08L5.62012 17.77C5.62012 19.04 6.60012 20.4 7.80012 20.8L10.9901 21.86C11.5401 22.04 12.4501 22.04 13.0101 21.86L16.2001 20.8C17.4001 20.4 18.3801 19.04 18.3801 17.77V13.13" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"></path>
                                    <path d="M21.3999 15V9" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"></path>
                                </svg>
                                Seniority Level
                            </h3>
                            <p class="text-sm text-muted-foreground">Select your experience level for tailored questions</p>
                        </div>

                        <div class="p-6 pt-0">
                            <fieldset>
                                <legend class="sr-only">Seniority level</legend>
                                <div class="grid grid-cols-2 gap-3 md:grid-cols-4">
                                    <label v-for="option in seniorityOptions" :key="option.value" class="flex cursor-pointer flex-col items-start rounded-xl border-2 p-4 transition-all hover:border-accent/50" :class="form.seniority_level === option.value ? 'border-brand bg-brand-soft' : 'border-hairline bg-surface'">
                                        <input v-model="form.seniority_level" type="radio" name="seniority_level" class="sr-only" :value="option.value">
                                        <span class="text-sm font-semibold text-muted-foreground">{{ option.label }}</span>
                                        <span class="text-xs text-muted-foreground mt-1">{{ option.description }}</span>
                                    </label>
                                </div>
                            </fieldset>

                            <p v-if="form.errors.seniority_level" class="mt-2 text-xs text-destructive">
                                {{ form.errors.seniority_level }}
                            </p>
                        </div>
                    </div>

                    <div>
                        <div class="overflow-hidden rounded-lg border border-hairline bg-surface text-foreground shadow-sm">
                            <div class="flex flex-col space-y-1.5 p-6 pb-4">
                                <h3 class="font-semibold tracking-tight flex items-center gap-2 text-lg">
                                    <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" width="24" height="24">
                                        <path d="M21 7V17C21 20 19.5 22 16 22H8C4.5 22 3 20 3 17V7C3 4 4.5 2 8 2H16C19.5 2 21 4 21 7Z" stroke="currentColor" stroke-width="1.75" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                        <path d="M14.5 4.5V6.5C14.5 7.6 15.4 8.5 16.5 8.5H18.5" stroke="currentColor" stroke-width="1.75" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                        <path d="M8 13H12" stroke="currentColor" stroke-width="1.75" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                        <path d="M8 17H16" stroke="currentColor" stroke-width="1.75" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                    </svg>
                                    CV / Resume (optional)
                                </h3>
                                <p class="text-sm text-muted-foreground">Upload your CV for personalized interview questions</p>
                            </div>

                            <div class="p-6 pt-0">
                                <label v-if="!form.resume" class="flex cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed border-hairline bg-surface p-8 transition-all hover:border-brand/40 hover:bg-brand-soft/40">
                                    <input type="file" class="hidden" accept=".pdf,.docx" @change="handleResumeChange" />
                                    <div class="h-12 w-12 rounded-full bg-accent border flex items-center justify-center mb-4">
                                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" width="24" height="24">
                                            <path d="M21 7V17C21 20 19.5 22 16 22H8C4.5 22 3 20 3 17V7C3 4 4.5 2 8 2H16C19.5 2 21 4 21 7Z" stroke="currentColor" stroke-width="1.75" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                            <path d="M14.5 4.5V6.5C14.5 7.6 15.4 8.5 16.5 8.5H18.5" stroke="currentColor" stroke-width="1.75" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                            <path d="M8 13H12" stroke="currentColor" stroke-width="1.75" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                            <path d="M8 17H16" stroke="currentColor" stroke-width="1.75" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                        </svg>
                                    </div>
                                    <p class="font-medium text-foreground mb-1">Drop your CV here or click to browse</p>
                                    <p class="text-sm text-muted-foreground">PDF or DOCX, max 10MB</p>
                                </label>

                                <div v-else class="flex flex-col gap-3 rounded-xl border bg-surface p-4 sm:flex-row sm:items-center sm:justify-between">
                                    <div class="flex w-full min-w-0 items-center gap-3">
                                        <div class="h-10 w-10 rounded-lg bg-accent flex items-center justify-center">
                                            <svg v-if="isResumeAnalyzing" stroke="currentColor" fill="currentColor" stroke-width="0" version="1.1" viewBox="0 0 16 16" class="spinner animate-spin" height="1em" width="1em" xmlns="http://www.w3.org/2000/svg" style="height: 20px; width: 20px;">
                                                <path d="M8 0c-4.355 0-7.898 3.481-7.998 7.812 0.092-3.779 2.966-6.812 6.498-6.812 3.59 0 6.5 3.134 6.5 7 0 0.828 0.672 1.5 1.5 1.5s1.5-0.672 1.5-1.5c0-4.418-3.582-8-8-8zM8 16c4.355 0 7.898-3.481 7.998-7.812-0.092 3.779-2.966 6.812-6.498 6.812-3.59 0-6.5-3.134-6.5-7 0-0.828-0.672-1.5-1.5-1.5s-1.5 0.672-1.5 1.5c0 4.418 3.582 8 8 8z"></path>
                                            </svg>
                                            <svg v-else viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" width="24" height="24">
                                                <path d="M21 7V17C21 20 19.5 22 16 22H8C4.5 22 3 20 3 17V7C3 4 4.5 2 8 2H16C19.5 2 21 4 21 7Z" stroke="currentColor" stroke-width="1.75" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                                <path d="M14.5 4.5V6.5C14.5 7.6 15.4 8.5 16.5 8.5H18.5" stroke="currentColor" stroke-width="1.75" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                                <path d="M8 13H12" stroke="currentColor" stroke-width="1.75" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                                <path d="M8 17H16" stroke="currentColor" stroke-width="1.75" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                            </svg>
                                        </div>

                                        <div class="min-w-0 flex-1">
                                            <p
                                                class="break-all sm:truncate font-medium text-foreground"
                                                :title="`${(authUser?.name ?? 'user').toLowerCase().replaceAll(' ', '_')}_${form.resume.name}`"
                                            >
                                                {{ `${(authUser?.name ?? 'user').toLowerCase().replaceAll(' ', '_')}_${form.resume.name}` }}
                                            </p>
                                            <p class="truncate text-sm text-muted-foreground">
                                                {{ isResumeAnalyzing ? 'Uploading and analyzing...' : 'Uploaded successfully • AI analyzed' }}
                                            </p>
                                        </div>
                                    </div>

                                    <div class="flex w-full shrink-0 items-center gap-2 sm:w-auto">
                                        <label class="inline-flex h-9 flex-1 cursor-pointer items-center justify-center gap-2 whitespace-nowrap rounded-md border border-hairline bg-surface px-4 text-xs font-semibold text-foreground ring-offset-background transition-all duration-200 hover:bg-surface-2 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 sm:flex-none [&_svg]:pointer-events-none [&_svg]:size-4 [&_svg]:shrink-0">
                                            Replace
                                            <input type="file" class="hidden" accept=".pdf,.docx" @change="handleResumeChange" />
                                        </label>

                                        <button type="button" class="inline-flex h-9 flex-1 items-center justify-center gap-2 whitespace-nowrap rounded-md border border-destructive/30 bg-surface px-4 text-xs font-semibold text-destructive ring-offset-background transition-all duration-200 hover:bg-destructive/10 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 sm:flex-none" @click="clearResume">
                                            Remove
                                        </button>
                                    </div>
                                </div>

                                <p v-if="form.errors.resume" class="mt-2 text-xs text-destructive">
                                    {{ form.errors.resume }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <ActionButton :processing="form.processing">
                            Continue to dashboard
                        </ActionButton>
                    </div>
                </form>
            </div>
        </main>
    </div>
</template>
