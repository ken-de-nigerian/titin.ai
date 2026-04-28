<script setup lang="ts">
    import { Head, Link } from '@inertiajs/vue3';
    import { ArrowRight, ArrowLeft } from 'lucide-vue-next';
    import { ref } from 'vue';

    const form = ref({
        email: '',
    });

    const isSubmitting = ref(false);
    const emailSent = ref(false);

    function handleSubmit() {
        // TODO: Connect to backend
        console.log('Forgot password form submitted:', form.value);
        emailSent.value = true;
    }
</script>

<template>
    <Head title="Reset password — Lumen" />

    <div class="flex min-h-screen items-center justify-center px-6 py-10">
        <div class="w-full max-w-md">
            <Link
                href="/"
                class="mx-auto flex w-fit items-center gap-2.5"
            >
                <div class="grid h-7 w-7 place-items-center rounded-lg bg-foreground text-background">
                    <svg
                        viewBox="0 0 16 16"
                        class="h-3.5 w-3.5"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <circle
                            cx="8"
                            cy="8"
                            r="3"
                        />
                        <circle
                            cx="8"
                            cy="8"
                            r="6.5"
                            opacity="0.5"
                        />
                    </svg>
                </div>
                <span class="text-[15px] font-semibold tracking-tight">Lumen</span>
            </Link>

            <div class="mt-12">
                <h1 class="text-3xl font-semibold tracking-tight">Reset your password</h1>
                <p class="mt-2 text-sm text-muted-foreground">
                    Enter your email and we'll send you a link to reset your password.
                </p>

                <div
                    v-if="emailSent"
                    class="mt-8 rounded-lg border border-brand/20 bg-brand-soft p-4 text-sm"
                >
                    <p class="font-medium text-brand">Check your email</p>
                    <p class="mt-1 text-muted-foreground">
                        We've sent a password reset link to <strong>{{ form.email }}</strong>
                    </p>
                </div>

                <form
                    v-else
                    class="mt-8 space-y-4"
                    @submit.prevent="handleSubmit"
                >
                    <div>
                        <label class="text-xs font-medium text-foreground">Email</label>
                        <input
                            v-model="form.email"
                            type="email"
                            placeholder="you@company.com"
                            class="mt-1.5 w-full rounded-lg border border-hairline bg-surface px-3.5 py-2.5 text-sm shadow-xs outline-none placeholder:text-muted-foreground/60 focus:border-brand focus:ring-2 focus:ring-ring"
                            required
                        />
                    </div>

                    <button
                        type="submit"
                        :disabled="isSubmitting"
                        class="group mt-2 flex w-full items-center justify-center gap-2 rounded-lg bg-foreground px-4 py-2.5 text-sm font-medium text-background shadow-sm transition hover:bg-foreground/90 disabled:opacity-50"
                    >
                        Send reset link
                        <ArrowRight class="h-4 w-4 transition group-hover:translate-x-0.5" />
                    </button>
                </form>

                <Link
                    href="/login"
                    class="mt-6 inline-flex items-center gap-1.5 text-sm font-medium text-brand hover:underline"
                >
                    <ArrowLeft class="h-4 w-4" />
                    Back to sign in
                </Link>
            </div>
        </div>
    </div>
</template>
