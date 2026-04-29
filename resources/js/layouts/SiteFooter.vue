<script setup lang="ts">
    import SiteLogo from "@/components/SiteLogo.vue";
    const currentYear = new Date().getFullYear();

    const footerSections = [
        {
            title: 'Product',
            links: [
                { label: 'Features', href: '#features' },
                { label: 'Pricing', href: '#pricing' },
                { label: 'Dashboard', href: '/dashboard' },
                { label: 'Try a session', href: '/interview' },
            ],
        },
        {
            title: 'Company',
            links: [
                { label: 'About', href: '#' },
                { label: 'Customers', href: '#' },
                { label: 'Careers', href: '#' },
                { label: 'Contact', href: '#' },
            ],
        },
        {
            title: 'Resources',
            links: [
                { label: 'Interview guides', href: '#' },
                { label: 'Changelog', href: '#' },
                { label: 'Help center', href: '#' },
                { label: 'Status', href: '#' },
            ],
        },
    ];
</script>

<template>
    <footer class="border-t border-hairline bg-surface-2/40">
        <div class="mx-auto max-w-7xl px-6 py-16">
            <div class="grid gap-10 md:grid-cols-12">
                <div class="md:col-span-4">
                    <SiteLogo />

                    <p class="mt-4 max-w-xs text-sm text-muted-foreground">
                        Voice-first interview practice. Real conversations, honest feedback.
                    </p>
                </div>

                <div
                    v-for="section in footerSections"
                    :key="section.title"
                    class="md:col-span-2"
                >
                    <p class="text-xs font-semibold uppercase tracking-wider text-foreground">
                        {{ section.title }}
                    </p>
                    <ul class="mt-4 space-y-3 text-sm text-muted-foreground">
                        <li
                            v-for="link in section.links"
                            :key="link.label"
                        >
                            <a
                                :href="link.href"
                                class="transition hover:text-foreground"
                            >
                                {{ link.label }}
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <div
                class="mt-14 flex flex-wrap items-center justify-between gap-4 border-t border-hairline pt-6 text-xs text-muted-foreground"
            >
                <span>© {{ currentYear }} Lumen, Inc. All rights reserved.</span>
                <div class="flex gap-6">
                    <a
                        href="#"
                        class="hover:text-foreground"
                    >
                        Privacy
                    </a>
                    <a
                        href="#"
                        class="hover:text-foreground"
                    >
                        Terms
                    </a>
                    <a
                        href="#"
                        class="hover:text-foreground"
                    >
                        Security
                    </a>
                </div>
            </div>
        </div>
    </footer>
</template>
