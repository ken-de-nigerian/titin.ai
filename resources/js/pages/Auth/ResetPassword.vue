<script setup lang="ts">
    import { Head, useForm } from '@inertiajs/vue3';
    import ActionButton from '@/components/ActionButton.vue';
    import FormInput from '@/components/FormInput.vue';
    import AuthLayout from '@/layouts/AuthLayout.vue';
    import AuthSidebar from '@/layouts/AuthSidebar.vue';
    import TextLink from '@/components/TextLink.vue';
    import { useRoute } from '@/composables/useRoute';

    const route = useRoute();

    defineProps<{
        token: string;
        email: string;
    }>();

    const form = useForm({
        password: '',
        password_confirmation: '',
    });

    const submit = (): void => {
        if (!route().has('password.store')) {

            console.warn('Missing route password.store; wire Laravel password reset routes to enable this form.');

            return;
        }

        form.post(route('password.store'), {
            preserveScroll: true,
        });
    };
</script>

<template>
    <Head title="Reset password — Lumen" />

    <AuthLayout>
        <template #form>
            <div class="relative flex flex-col px-6 py-10 md:px-16 md:py-14">
                <div class="mx-auto flex w-full max-w-sm flex-1 flex-col justify-center py-12">
                    <h1 class="text-3xl font-semibold tracking-tight">Set new password</h1>
                    <p class="mt-2 text-sm text-muted-foreground">
                        Enter a new password for <strong>{{ email }}</strong>
                    </p>

                    <form class="mt-8 space-y-4" method="POST" @submit.prevent="submit">
                        <FormInput
                            id="password"
                            v-model="form.password"
                            label="New password"
                            type="password"
                            placeholder="••••••••"
                            autocomplete="new-password"
                            :error="form.errors.password"
                        />

                        <FormInput
                            id="password_confirmation"
                            v-model="form.password_confirmation"
                            label="Confirm password"
                            type="password"
                            placeholder="••••••••"
                            autocomplete="new-password"
                            :error="form.errors.password_confirmation"
                        />

                        <ActionButton :processing="form.processing">Reset password</ActionButton>
                    </form>

                    <p class="mt-8 text-center text-sm text-muted-foreground">
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
