<script setup lang="ts">
    import { Head, router } from '@inertiajs/vue3';
    import { ref } from 'vue';
    import ActionButton from '@/components/ActionButton.vue';
    import AuthLayout from '@/layouts/AuthLayout.vue';
    import AuthSidebar from '@/layouts/AuthSidebar.vue';
    import { useRoute } from '@/composables/useRoute';
    import SiteLogo from "@/components/SiteLogo.vue";

    const route = useRoute();
    const isResending = ref(false);
    const isLoggingOut = ref(false);

    function resend() {
        if (isResending.value) {
            return;
        }

        isResending.value = true;

        router.post(route('verification.send'), {}, {
            preserveScroll: true,
            onFinish: () => {
                isResending.value = false;
            },
        });
    }

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
</script>

<template>
    <Head title="Verify Your Email" />

    <AuthLayout>
        <template #form>
            <div class="relative flex flex-col px-6 py-10 md:px-16 md:py-14">
                <div class="mx-auto flex w-full max-w-sm flex-1 flex-col justify-center py-12">
                    <div class="mb-6 flex md:hidden">
                        <SiteLogo />
                    </div>

                    <h1 class="text-3xl font-semibold tracking-tight">Check your inbox</h1>
                    <p class="mt-2 text-sm text-muted-foreground">
                        We’ve sent you a link to verify your email and get started with your interview practice.
                    </p>

                    <div class="mt-8 rounded-xl border border-hairline bg-surface p-5 shadow-xs">
                        <p class="text-sm text-muted-foreground">
                            Didn’t get the email? You can request another verification email.
                        </p>

                        <ActionButton type="button" class="mt-4" :processing="isResending" @click="resend">
                            Resend verification email
                        </ActionButton>

                        <button
                            type="button"
                            class="mt-3 w-full rounded-lg border border-hairline bg-background px-4 py-2.5 text-sm font-medium text-foreground transition hover:bg-surface-2 disabled:cursor-not-allowed disabled:opacity-60"
                            :disabled="isLoggingOut"
                            @click="logout">
                            {{ isLoggingOut ? 'Logging out...' : 'Log out' }}
                        </button>
                    </div>
                </div>
            </div>
        </template>

        <template #sidebar>
            <AuthSidebar />
        </template>
    </AuthLayout>
</template>

