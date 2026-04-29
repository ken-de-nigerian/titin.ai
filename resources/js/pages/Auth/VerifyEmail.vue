<script setup lang="ts">
    import { Head, router } from '@inertiajs/vue3';
    import ActionButton from '@/components/ActionButton.vue';
    import AuthLayout from '@/layouts/AuthLayout.vue';
    import AuthSidebar from '@/layouts/AuthSidebar.vue';
    import { useRoute } from '@/composables/useRoute';

    const route = useRoute();

    function resend() {
        router.post(route('verification.send'));
    }
</script>

<template>
    <Head title="Verify your email" />

    <AuthLayout>
        <template #form>
            <div class="relative flex flex-col px-6 py-10 md:px-16 md:py-14">
                <div class="mx-auto flex w-full max-w-sm flex-1 flex-col justify-center py-12">
                    <h1 class="text-3xl font-semibold tracking-tight">Check your inbox</h1>
                    <p class="mt-2 text-sm text-muted-foreground">
                        Before you can access your dashboard, please verify your email address by clicking the link we just emailed you.
                    </p>

                    <div class="mt-8 rounded-xl border border-hairline bg-surface p-5 shadow-xs">
                        <p class="text-sm text-muted-foreground">
                            Didn’t get the email? You can request another verification email.
                        </p>

                        <ActionButton type="button" class="mt-4" @click="resend">
                            Resend verification email
                        </ActionButton>
                    </div>
                </div>
            </div>
        </template>

        <template #sidebar>
            <AuthSidebar />
        </template>
    </AuthLayout>
</template>

