<script setup lang="ts">
    import { Link } from '@inertiajs/vue3';
    import { ArrowRight } from 'lucide-vue-next';
    import { useRoute } from '@/composables/useRoute';

    const route = useRoute();
</script>

<template>
    <header class="sticky top-0 z-40 w-full border-b border-hairline bg-background/80 backdrop-blur-xl">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-6 py-3.5">
            <Link :href="route('home')" class="flex items-center gap-2.5">
                <div class="grid h-7 w-7 place-items-center rounded-lg bg-foreground text-background">
                    <svg viewBox="0 0 16 16" class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="8" cy="8" r="3"/>
                        <circle cx="8" cy="8" r="6.5" opacity="0.5"/>
                    </svg>
                </div>
                <span class="text-[15px] font-semibold tracking-tight">Lumen</span>
            </Link>

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
