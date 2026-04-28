<script setup lang="ts">
    import { Head, useForm } from '@inertiajs/vue3';
    import ActionButton from '@/components/ActionButton.vue';
    import FormInput from '@/components/FormInput.vue';
    import AuthLayout from '@/components/layouts/AuthLayout.vue';
    import AuthSidebar from '@/components/layouts/AuthSidebar.vue';
    import TextLink from '@/components/TextLink.vue';
    import { useRoute } from '@/composables/useRoute';

    const route = useRoute();

    const form = useForm({
        email: '',
        password: '',
        remember: false,
    });

    const submit = (): void => {
        form.post(route('login.store'), {
            preserveScroll: true,
            onFinish: () => {
                form.reset('password');
            },
        });
    };

    const clearError = (field: keyof typeof form.errors): void => {
        if (form.errors[field]) {
            form.clearErrors(field);
        }
    };
</script>

<template>
    <Head title="Login To Your Account" />

    <AuthLayout>
        <template #form>
            <div class="relative flex flex-col px-6 py-10 md:px-16 md:py-14">
                <div class="mx-auto flex w-full max-w-sm flex-1 flex-col justify-center py-12">
                    <h1 class="text-3xl font-semibold tracking-tight">Welcome back</h1>
                    <p class="mt-2 text-sm text-muted-foreground">Sign in to continue your practice.</p>

                    <button class="mt-8 flex w-full items-center justify-center gap-2 rounded-lg border border-hairline bg-surface px-4 py-2.5 text-sm font-medium shadow-xs transition hover:bg-surface-2" type="button">
                        <svg viewBox="0 0 24 24" class="h-4 w-4">
                            <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 0 1-2.2 3.32v2.76h3.56c2.08-1.92 3.28-4.74 3.28-8.09Z"/>
                            <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.56-2.76c-.98.66-2.24 1.06-3.72 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84A11 11 0 0 0 12 23Z"/>
                            <path fill="#FBBC05" d="M5.84 14.11A6.6 6.6 0 0 1 5.5 12c0-.73.13-1.44.34-2.11V7.05H2.18A11 11 0 0 0 1 12c0 1.78.43 3.46 1.18 4.95l3.66-2.84Z"/>
                            <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.05l3.66 2.84C6.71 7.31 9.14 5.38 12 5.38Z"/>
                        </svg>
                        Continue with Google
                    </button>

                    <div class="my-6 flex items-center gap-3 text-xs text-muted-foreground">
                        <div class="h-px flex-1 bg-hairline" />
                        or with email
                        <div class="h-px flex-1 bg-hairline" />
                    </div>

                    <form class="space-y-4" method="POST" @submit.prevent="submit">
                        <FormInput
                            id="email"
                            v-model="form.email"
                            label="Email"
                            type="email"
                            placeholder="you@email.com"
                            autocomplete="email"
                            :error="form.errors.email"
                            @focus="clearError('email')"
                        />

                        <FormInput
                            id="password"
                            v-model="form.password"
                            label="Password"
                            type="password"
                            placeholder="••••••••"
                            autocomplete="current-password"
                            :error="form.errors.password"
                            @focus="clearError('password')"
                        />

                        <div class="flex items-center justify-between">
                            <label class="group flex cursor-pointer items-center gap-2.5">
                                <input v-model="form.remember" type="checkbox" class="peer sr-only" />
                                <span class="relative flex h-4 w-4 shrink-0 items-center justify-center rounded-full border border-hairline bg-surface shadow-xs transition peer-checked:border-foreground peer-checked:bg-foreground peer-focus-visible:ring-2 peer-focus-visible:ring-ring">
                                    <span
                                        class="h-2 w-2 rounded-full bg-background transition-transform duration-150"
                                        :class="form.remember ? 'scale-100' : 'scale-0'"
                                    />
                                </span>
                                <span class="text-sm text-muted-foreground transition group-hover:text-foreground">Remember me</span>
                            </label>
                            <TextLink href="/forgot-password" class="text-sm text-muted-foreground">Forgot password?</TextLink>
                        </div>

                        <ActionButton :processing="form.processing">Sign in</ActionButton>
                    </form>

                    <p class="mt-8 text-center text-sm text-muted-foreground">
                        New to Lumen?
                        <TextLink :href="route('register')" class="font-medium text-brand hover:underline">
                            Create account
                        </TextLink>
                    </p>
                </div>
            </div>
        </template>

        <template #sidebar>
            <AuthSidebar />
        </template>
    </AuthLayout>
</template>
