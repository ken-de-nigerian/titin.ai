<script setup lang="ts">
    import { Head, Link } from '@inertiajs/vue3';
    import { ArrowRight } from 'lucide-vue-next';
    import { ref } from 'vue';

    defineProps<{
        token: string;
        email: string;
    }>();

    const form = ref({
        password: '',
        password_confirmation: '',
    });

    const isSubmitting = ref(false);

    function handleSubmit() {
        // TODO: Connect to backend
        console.log('Reset password form submitted:', form.value);
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
                <h1 class="text-3xl font-semibold tracking-tight">Set new password</h1>
                <p class="mt-2 text-sm text-muted-foreground">
                    Enter a new password for <strong>{{ email }}</strong>
                </p>

                <form
                    class="mt-8 space-y-4"
                    @submit.prevent="handleSubmit"
                >
                    <div>
                        <label class="text-xs font-medium text-foreground">New Password</label>
                        <input
                            v-model="form.password"
                            type="password"
                            placeholder="••••••••"
                            class="mt-1.5 w-full rounded-lg border border-hairline bg-surface px-3.5 py-2.5 text-sm shadow-xs outline-none placeholder:text-muted-foreground/60 focus:border-brand focus:ring-2 focus:ring-ring"
                            required
                        />
                    </div>

                    <div>
                        <label class="text-xs font-medium text-foreground">Confirm Password</label>
                        <input
                            v-model="form.password_confirmation"
                            type="password"
                            placeholder="••••••••"
                            class="mt-1.5 w-full rounded-lg border border-hairline bg-surface px-3.5 py-2.5 text-sm shadow-xs outline-none placeholder:text-muted-foreground/60 focus:border-brand focus:ring-2 focus:ring-ring"
                            required
                        />
                    </div>

                    <button
                        type="submit"
                        :disabled="isSubmitting"
                        class="group mt-2 flex w-full items-center justify-center gap-2 rounded-lg bg-foreground px-4 py-2.5 text-sm font-medium text-background shadow-sm transition hover:bg-foreground/90 disabled:opacity-50"
                    >
                        Reset password
                        <ArrowRight class="h-4 w-4 transition group-hover:translate-x-0.5" />
                    </button>
                </form>
            </div>
        </div>
    </div>
</template>
