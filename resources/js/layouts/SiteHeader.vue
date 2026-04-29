<script setup lang="ts">
    import { Link } from '@inertiajs/vue3';
    import { ArrowRight } from 'lucide-vue-next';
    import { useRoute } from '@/composables/useRoute';
    import SiteLogo from "@/components/SiteLogo.vue";

    const route = useRoute();
</script>

<template>
    <header class="sticky top-0 z-40 w-full border-b border-hairline bg-background/80 backdrop-blur-xl">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-6 py-3.5">
            <SiteLogo />

            <nav class="hidden items-center gap-8 text-sm text-muted-foreground md:flex">
                <Link :href="route('home')" class="transition hover:text-foreground">
                    Product
                </Link>

                <a href="#features" class="transition hover:text-foreground">
                    Features
                </a>

                <a href="#pricing" class="transition hover:text-foreground">
                    Pricing
                </a>

                <a href="/dashboard" class="transition hover:text-foreground">
                    Dashboard
                </a>
            </nav>

            <div class="flex items-center gap-2">
                <Link href="/register" class="group inline-flex items-center gap-1.5 rounded-lg bg-foreground px-3.5 py-2 text-sm font-medium text-background shadow-sm transition hover:bg-foreground/90">
                    Get Started
                    <ArrowRight class="h-3.5 w-3.5 transition group-hover:translate-x-0.5" />
                </Link>
            </div>
        </div>
    </header>
</template>
