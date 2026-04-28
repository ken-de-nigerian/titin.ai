<script setup lang="ts">
    import { Head, Link } from '@inertiajs/vue3';
    import { ArrowRight, Check } from 'lucide-vue-next';
    import { ref } from 'vue';

    const form = ref({
        email: '',
        password: '',
        remember: false,
    });

    const isSubmitting = ref(false);

    function handleSubmit() {
        // TODO: Connect to backend
        console.log('Login form submitted:', form.value);
    }
</script>

<template>
    <Head title="Sign in — Lumen" />

    <div class="grid min-h-screen md:grid-cols-2">
        <!-- Left — form -->
        <div class="relative flex flex-col px-6 py-10 md:px-16 md:py-14">
            <Link
                href="/"
                class="flex items-center gap-2.5"
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

            <div class="mx-auto flex w-full max-w-sm flex-1 flex-col justify-center py-12">
                <h1 class="text-3xl font-semibold tracking-tight">Welcome back</h1>
                <p class="mt-2 text-sm text-muted-foreground">Sign in to continue your practice.</p>

                <button
                    class="mt-8 flex w-full items-center justify-center gap-2 rounded-lg border border-hairline bg-surface px-4 py-2.5 text-sm font-medium shadow-xs transition hover:bg-surface-2"
                    type="button"
                >
                    <svg
                        viewBox="0 0 24 24"
                        class="h-4 w-4"
                    >
                        <path
                            fill="#4285F4"
                            d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 0 1-2.2 3.32v2.76h3.56c2.08-1.92 3.28-4.74 3.28-8.09Z"
                        />
                        <path
                            fill="#34A853"
                            d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.56-2.76c-.98.66-2.24 1.06-3.72 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84A11 11 0 0 0 12 23Z"
                        />
                        <path
                            fill="#FBBC05"
                            d="M5.84 14.11A6.6 6.6 0 0 1 5.5 12c0-.73.13-1.44.34-2.11V7.05H2.18A11 11 0 0 0 1 12c0 1.78.43 3.46 1.18 4.95l3.66-2.84Z"
                        />
                        <path
                            fill="#EA4335"
                            d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.05l3.66 2.84C6.71 7.31 9.14 5.38 12 5.38Z"
                        />
                    </svg>
                    Continue with Google
                </button>

                <div class="my-6 flex items-center gap-3 text-xs text-muted-foreground">
                    <div class="h-px flex-1 bg-hairline" />
                    or with email
                    <div class="h-px flex-1 bg-hairline" />
                </div>

                <form
                    class="space-y-4"
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

                    <div>
                        <label class="text-xs font-medium text-foreground">Password</label>
                        <input
                            v-model="form.password"
                            type="password"
                            placeholder="••••••••"
                            class="mt-1.5 w-full rounded-lg border border-hairline bg-surface px-3.5 py-2.5 text-sm shadow-xs outline-none placeholder:text-muted-foreground/60 focus:border-brand focus:ring-2 focus:ring-ring"
                            required
                        />
                    </div>

                    <div class="flex items-center justify-between">
                        <label class="flex items-center gap-2 text-sm">
                            <input
                                v-model="form.remember"
                                type="checkbox"
                                class="h-4 w-4 rounded border-hairline text-brand focus:ring-2 focus:ring-ring"
                            />
                            <span class="text-muted-foreground">Remember me</span>
                        </label>
                        <Link
                            href="/forgot-password"
                            class="text-sm font-medium text-brand hover:underline"
                        >
                            Forgot password?
                        </Link>
                    </div>

                    <button
                        type="submit"
                        :disabled="isSubmitting"
                        class="group mt-2 flex w-full items-center justify-center gap-2 rounded-lg bg-foreground px-4 py-2.5 text-sm font-medium text-background shadow-sm transition hover:bg-foreground/90 disabled:opacity-50"
                    >
                        Sign in
                        <ArrowRight class="h-4 w-4 transition group-hover:translate-x-0.5" />
                    </button>
                </form>

                <p class="mt-8 text-center text-sm text-muted-foreground">
                    New to Lumen?
                    <Link
                        href="/register"
                        class="font-medium text-brand hover:underline"
                    >
                        Create account
                    </Link>
                </p>
            </div>

            <p class="text-xs text-muted-foreground">
                By continuing you agree to our
                <a
                    href="#"
                    class="underline"
                >Terms</a>
                and
                <a
                    href="#"
                    class="underline"
                >Privacy Policy</a>.
            </p>
        </div>

        <!-- Right — marketing aside -->
        <div class="relative hidden overflow-hidden border-l border-hairline bg-surface-2/40 md:block">
            <div class="absolute inset-0 hero-wash" />
            <div
                class="absolute inset-0 bg-dots opacity-50"
                style="mask-image: radial-gradient(ellipse at center, black 40%, transparent 75%)"
            />

            <div class="relative flex h-full flex-col justify-between p-14">
                <div>
                    <p class="text-sm font-medium text-brand">Lumen Pro</p>
                    <h2 class="mt-3 text-3xl font-semibold leading-tight tracking-tight">
                        The interview prep tool I wish I'd had.
                    </h2>
                    <ul class="mt-8 space-y-3 text-sm">
                        <li
                            v-for="feature in [
                                'Real voice conversations, not chat',
                                'Honest, structured feedback in seconds',
                                'Rewrites in your voice, ready to rehearse',
                                'Private by default — never used to train models',
                            ]"
                            :key="feature"
                            class="flex items-start gap-2.5"
                        >
                            <div class="mt-0.5 grid h-4 w-4 shrink-0 place-items-center rounded-full bg-brand text-background">
                                <Check class="h-2.5 w-2.5" />
                            </div>
                            <span>{{ feature }}</span>
                        </li>
                    </ul>
                </div>

                <figure class="surface rounded-2xl p-6 shadow-md">
                    <blockquote class="text-base leading-relaxed">
                        "I did six sessions before my Stripe loop. Got the offer. Closest thing to a real
                        interview I've used."
                    </blockquote>
                    <figcaption class="mt-5 flex items-center gap-3 border-t border-hairline pt-4">
                        <div class="grid h-8 w-8 place-items-center rounded-full bg-surface-2 text-xs font-medium">
                            M
                        </div>
                        <div class="text-xs">
                            <p class="font-medium">Maya R.</p>
                            <p class="text-muted-foreground">Senior PM, fintech</p>
                        </div>
                    </figcaption>
                </figure>
            </div>
        </div>
    </div>
</template>
