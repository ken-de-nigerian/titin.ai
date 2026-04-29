<script setup lang="ts">
    import {Head, useForm} from '@inertiajs/vue3';
    import ActionButton from '@/components/ActionButton.vue';
import CustomSelectDropdown from '@/components/CustomSelectDropdown.vue';
    import FormInput from '@/components/FormInput.vue';
    import {useRoute} from '@/composables/useRoute';

    const route = useRoute();
const interviewTypeOptions = [
    { value: 'behavioral', label: 'Behavioral' },
    { value: 'technical', label: 'Technical' },
    { value: 'role_specific', label: 'Role-specific' },
] as const;

    const props = defineProps<{
        prefill: {
            name: string;
            job_role: string;
            interview_type: 'behavioral' | 'technical' | 'role_specific' | string;
        };
    }>();

    const form = useForm<{
        job_role: string;
        interview_type: 'behavioral' | 'technical' | 'role_specific';
        resume: File | null;
    }>({
        job_role: props.prefill.job_role ?? '',
        interview_type: (['behavioral', 'technical', 'role_specific'].includes(props.prefill.interview_type)
            ? (props.prefill.interview_type as any)
            : 'behavioral'),
        resume: null,
    });

    const submit = (): void => {
        form.post(route('user.onboarding.update'), {
            preserveScroll: true,
            forceFormData: true,
        });
    };
</script>

<template>
    <Head title="Onboarding — Lumen" />

    <div class="onboarding">
        <main class="onboarding-main">
            <div class="surface rounded-2xl p-6 shadow-xs md:p-8">
                <p class="text-xs font-medium uppercase tracking-wider text-brand">Welcome</p>
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
                    />

                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-muted-foreground">Interview focus</label>
                        <CustomSelectDropdown
                            id="interview_type"
                            v-model="form.interview_type"
                            :options="interviewTypeOptions"
                            :error="form.errors.interview_type"
                        >
                            <template #default="{ selectedLabel }">{{ selectedLabel }}</template>
                        </CustomSelectDropdown>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-muted-foreground">CV / Resume (optional)</label>
                        <input
                            type="file"
                            accept=".pdf,.doc,.docx"
                            class="w-full rounded-lg border border-input bg-background px-4 py-3 text-sm outline-none transition focus:border-ring focus:ring-2 focus:ring-ring/20"
                            @change="(e: Event) => {  form.resume = (e.target as HTMLInputElement).files?.[0] ?? null; }"
                        />
                        <p class="mt-2 text-xs text-muted-foreground">
                            PDF, DOC, or DOCX up to 5MB.
                        </p>
                        <p v-if="form.errors.resume" class="mt-2 text-xs text-destructive">
                            {{ form.errors.resume }}
                        </p>
                    </div>

                    <div class="flex flex-col gap-3 pt-2 sm:flex-row sm:items-center sm:justify-between">
                        <ActionButton :processing="form.processing">
                            Continue to dashboard
                        </ActionButton>

                        <button
                            type="button"
                            class="text-sm font-medium text-muted-foreground transition hover:text-foreground"
                            @click="form.resume = null; submit();"
                        >
                            Skip resume for now
                        </button>
                    </div>
                </form>
            </div>
        </main>
    </div>
</template>

<style scoped>
.onboarding {
    padding: 1.75rem 0 2rem;
}

.onboarding-main {
    margin: 0 auto;
}

@media (min-width: 768px) {
    .onboarding {
        padding: 2.5rem 0 2.5rem;
    }
}
</style>

