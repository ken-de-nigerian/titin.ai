<script setup lang="ts">
    import { Head, useForm } from '@inertiajs/vue3';
    import ActionButton from '@/components/ActionButton.vue';
    import FormInput from '@/components/FormInput.vue';
    import AuthLayout from '@/layouts/AuthLayout.vue';
    import AuthSidebar from '@/layouts/AuthSidebar.vue';
    import TextLink from '@/components/TextLink.vue';
    import { useRoute } from '@/composables/useRoute';
    import SiteLogo from "@/components/SiteLogo.vue";

    const route = useRoute();

    const form = useForm({
        email: '',
    });

    const submit = (): void => {
        form.post(route('password.email'), {
            preserveScroll: true,
        });
    };

    const clearError = (field: keyof typeof form.errors): void => {
        if (form.errors[field]) {
            form.clearErrors(field);
        }
    };
</script>

<template>
    <Head title="Reset Your Password" />

    <AuthLayout>
        <template #form>
            <div class="relative flex flex-col px-6 py-10 md:px-16 md:py-14">
                <div class="mx-auto flex w-full max-w-sm flex-1 flex-col justify-center py-12">
                    <div class="mb-6 flex md:hidden">
                        <SiteLogo />
                    </div>

                    <h1 class="text-3xl font-semibold tracking-tight">Forgot your password?</h1>
                    <p class="mt-2 text-sm text-muted-foreground">
                        Enter your email and we'll send you a link to reset your password.
                    </p>

                    <form class="mt-8 space-y-4" method="POST" @submit.prevent="submit">
                        <FormInput
                            id="email"
                            v-model="form.email"
                            label="Email"
                            type="email"
                            placeholder="you@company.com"
                            autocomplete="email"
                            :error="form.errors.email"
                            @focus="clearError('email')"
                        />

                        <ActionButton :processing="form.processing">Send reset link</ActionButton>
                    </form>

                    <p class="mt-8 text-center text-sm text-muted-foreground">
                        Remembered your password?
                        <TextLink :href="route('login')">Back to sign in</TextLink>
                    </p>
                </div>
            </div>
        </template>

        <template #sidebar>
            <AuthSidebar />
        </template>
    </AuthLayout>
</template>
