<script setup lang="ts">
    import SiteLogo from "@/components/SiteLogo.vue";
    import UserAccountMenu from "@/components/UserAccountMenu.vue";
    import {useRoute} from "@/composables/useRoute";
    import SidebarNavMenuFlatItem from "@/components/SidebarNavMenuFlatItem.vue";
    import FlashMessages from "@/components/utilities/FlashMessages.vue";

    const route = useRoute();
</script>

<template>
    <div class="min-h-screen bg-surface-2/60 md:bg-[radial-gradient(circle_at_50%_-10%,oklch(0.97_0.01_265),transparent_60%),linear-gradient(to_bottom,oklch(0.96_0.005_265),oklch(0.94_0.005_265))] md:py-8">
        <div class="mx-auto w-full md:max-w-352 md:px-6">
            <div class="relative md:flex md:gap-0">
                <aside class="sticky hidden md:block md:w-64 md:shrink-0">
                    <div class="sticky top-0 flex min-h-[calc(100vh-4rem)] flex-col rounded-2xl p-4 shadow-sm">
                        <p class="mt-6 px-4 pb-3 text-xs font-medium uppercase tracking-wider text-muted-foreground">Overview</p>
                        <nav aria-label="Main navigation" class="flex flex-col gap-2">
                            <SidebarNavMenuFlatItem :href="route('user.dashboard')">
                                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" width="24" height="24">
                                    <path d="M22 10.9V4.1C22 2.6 21.36 2 19.77 2H15.73C14.14 2 13.5 2.6 13.5 4.1V10.9C13.5 12.4 14.14 13 15.73 13H19.77C21.36 13 22 12.4 22 10.9Z" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"></path>
                                    <path d="M22 19.9V18.1C22 16.6 21.36 16 19.77 16H15.73C14.14 16 13.5 16.6 13.5 18.1V19.9C13.5 21.4 14.14 22 15.73 22H19.77C21.36 22 22 21.4 22 19.9Z" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"></path>
                                    <path d="M10.5 13.1V19.9C10.5 21.4 9.86 22 8.27 22H4.23C2.64 22 2 21.4 2 19.9V13.1C2 11.6 2.64 11 4.23 11H8.27C9.86 11 10.5 11.6 10.5 13.1Z" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"></path>
                                    <path d="M10.5 4.1V5.9C10.5 7.4 9.86 8 8.27 8H4.23C2.64 8 2 7.4 2 5.9V4.1C2 2.6 2.64 2 4.23 2H8.27C9.86 2 10.5 2.6 10.5 4.1Z" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"></path>
                                </svg>
                                <span class="leading-tight">
                                    <span class="block">Dashboard</span>
                                    <span class="mt-1 block text-xs font-normal text-muted-foreground">Overview and recent activity</span>
                                </span>
                            </SidebarNavMenuFlatItem>
                        </nav>

                        <p class="mt-6 px-4 pb-3 text-xs font-medium uppercase tracking-wider text-muted-foreground">Workspace</p>
                        <nav aria-label="Main navigation" class="flex flex-col gap-2">
                            <SidebarNavMenuFlatItem :href="route('user.cv.index')">
                                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" width="24" height="24">
                                    <path d="M21 7V17C21 20 19.5 22 16 22H8C4.5 22 3 20 3 17V7C3 4 4.5 2 8 2H16C19.5 2 21 4 21 7Z" stroke="currentColor" stroke-width="1.75" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                    <path d="M14.5 4.5V6.5C14.5 7.6 15.4 8.5 16.5 8.5H18.5" stroke="currentColor" stroke-width="1.75" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                    <path d="M8 13H12" stroke="currentColor" stroke-width="1.75" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                    <path d="M8 17H16" stroke="currentColor" stroke-width="1.75" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                </svg>
                                <span class="leading-tight">
                                    <span class="block">CVs</span>
                                    <span class="mt-1 block text-xs font-normal text-muted-foreground">Manage resumes and versions</span>
                                </span>
                            </SidebarNavMenuFlatItem>

                            <a class="flex items-start gap-4 rounded-xl px-4 py-3.5 text-base transition bg-surface-2 font-medium text-foreground" href="/dashboard">
                                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" width="24" height="24">
                                    <path d="M22 4.67001V16.74C22 17.7 21.22 18.6 20.26 18.72L19.93 18.76C17.75 19.05 14.39 20.16 12.47 21.22C12.21 21.37 11.78 21.37 11.51 21.22L11.47 21.2C9.54997 20.15 6.20003 19.05 4.03003 18.76L3.73999 18.72C2.77999 18.6 2 17.7 2 16.74V4.66C2 3.47 2.96997 2.57001 4.15997 2.67001C6.25997 2.84001 9.43997 3.90003 11.22 5.01003L11.47 5.16C11.76 5.34 12.24 5.34 12.53 5.16L12.7 5.05001C13.33 4.66001 14.13 4.27001 15 3.92001V8.00002L17 6.67001L19 8.00002V2.78005C19.27 2.73005 19.53 2.70002 19.77 2.68002H19.83C21.02 2.58002 22 3.47001 22 4.67001Z" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"></path>
                                    <path d="M12 5.49023V20.4902" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"></path>
                                    <path d="M19 2.78027V8.00024L17 6.67023L15 8.00024V3.92023C16.31 3.40023 17.77 2.98027 19 2.78027Z" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"></path>
                                </svg>
                                <span class="leading-tight">
                                    <span class="block">Documents</span>
                                    <span class="mt-1 block text-xs font-normal text-muted-foreground">Reference files and notes</span>
                                </span>
                            </a>

                            <a class="flex items-start gap-4 rounded-xl px-4 py-3.5 text-base transition bg-surface-2 font-medium text-foreground" href="/dashboard">
                                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" width="24" height="24">
                                    <path d="M12 19C15.31 19 18 16.31 18 13V8C18 4.69 15.31 2 12 2C8.69 2 6 4.69 6 8V13C6 16.31 8.69 19 12 19Z" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"></path>
                                    <path d="M3 11V13C3 17.97 7.03 22 12 22C16.97 22 21 17.97 21 13V11" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"></path>
                                    <path d="M9.11011 7.47999C10.8901 6.82999 12.8301 6.82999 14.6101 7.47999" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"></path>
                                    <path d="M10.03 10.48C11.23 10.15 12.5 10.15 13.7 10.48" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"></path>
                                </svg>
                                <span class="leading-tight">
                                    <span class="block">Sessions</span>
                                    <span class="mt-1 block text-xs font-normal text-muted-foreground">Your interview practice history</span>
                                </span>
                            </a>

                            <SidebarNavMenuFlatItem :href="route('user.profile.settings')">
                                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" width="24" height="24">
                                    <path d="M9 22H15C20 22 22 20 22 15V9C22 4 20 2 15 2H9C4 2 2 4 2 9V15C2 20 4 22 9 22Z" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"></path>
                                    <path d="M15.5703 18.5001V14.6001" stroke="currentColor" stroke-width="1.75" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                    <path d="M15.5703 7.45V5.5" stroke="currentColor" stroke-width="1.75" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                    <path d="M15.5707 12.6502C17.0066 12.6502 18.1707 11.4861 18.1707 10.0502C18.1707 8.61426 17.0066 7.4502 15.5707 7.4502C14.1348 7.4502 12.9707 8.61426 12.9707 10.0502C12.9707 11.4861 14.1348 12.6502 15.5707 12.6502Z" stroke="currentColor" stroke-width="1.75" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                    <path d="M8.42969 18.4998V16.5498" stroke="currentColor" stroke-width="1.75" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                    <path d="M8.42969 9.4V5.5" stroke="currentColor" stroke-width="1.75" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                    <path d="M8.43008 16.55C9.86602 16.55 11.0301 15.3859 11.0301 13.95C11.0301 12.514 9.86602 11.35 8.43008 11.35C6.99414 11.35 5.83008 12.514 5.83008 13.95C5.83008 15.3859 6.99414 16.55 8.43008 16.55Z" stroke="currentColor" stroke-width="1.75" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                </svg>
                                <span class="leading-tight">
                                    <span class="block">Settings</span>
                                    <span class="mt-1 block text-xs font-normal text-muted-foreground">Security and app controls</span>
                                </span>
                            </SidebarNavMenuFlatItem>
                        </nav>

                        <div class="mt-auto">
                            <div class="mt-5 rounded-xl bg-surface-2 p-4 text-sm">
                                <p class="text-sm font-semibold tracking-tight text-foreground">Credits</p>
                                <p class="mt-1 text-xs text-muted-foreground">Track your credits and top up before your next session.</p>

                                <a class="text-foreground decoration-[hsl(var(--border))] underline-offset-4 duration-300 ease-out hover:decoration-current mt-3 block min-w-0 max-w-37.5 shrink rounded-lg p-2 text-left no-underline outline-none ring-primary transition-colors hover:bg-black/5 focus-visible:ring-2 dark:hover:bg-white/10 sm:max-w-50" aria-label="120 credits left. View credits and billing." href="/user/billing">
                                    <div class="heading-text text-xs md:text-sm">120 credits left</div>
                                    <div class="mt-2 h-2 w-full overflow-hidden rounded-full bg-border/60" aria-hidden="true">
                                        <div class="h-full rounded-full bg-primary transition-all duration-300" style="width: 72%;"></div>
                                    </div>
                                </a>

                                <a href="/user/billing" class="mt-3 inline-flex w-full items-center justify-center rounded-lg bg-foreground px-3 py-2 text-xs font-medium text-background transition hover:opacity-90">
                                    Buy more credits
                                </a>
                            </div>

                            <div class="mt-3 border-t border-hairline pt-4">
                                <UserAccountMenu variant="sidebar" />
                            </div>
                        </div>
                    </div>
                </aside>

                <div class="flex min-h-screen flex-col md:flex-1">
                    <header class="sticky top-0 z-20 mb-6 border-b border-hairline px-8 pb-3 pt-4 backdrop-blur-xl">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <SiteLogo />
                            </div>

                            <div class="flex items-center gap-1">
                                <button class="grid h-9 w-9 place-items-center rounded-full text-muted-foreground transition hover:bg-surface-2 hover:text-foreground">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-search h-4 w-4" aria-hidden="true">
                                        <path d="m21 21-4.34-4.34"></path>
                                        <circle cx="11" cy="11" r="8"></circle>
                                    </svg>
                                </button>

                                <button class="relative grid h-9 w-9 place-items-center rounded-full text-muted-foreground transition hover:bg-surface-2 hover:text-foreground">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-bell h-4 w-4" aria-hidden="true">
                                        <path d="M10.268 21a2 2 0 0 0 3.464 0"></path>
                                        <path d="M3.262 15.326A1 1 0 0 0 4 17h16a1 1 0 0 0 .74-1.673C19.41 13.956 18 12.499 18 8A6 6 0 0 0 6 8c0 4.499-1.411 5.956-2.738 7.326"></path>
                                    </svg>
                                    <span class="absolute right-2 top-2 h-1.5 w-1.5 rounded-full bg-brand"></span>
                                </button>

                                <UserAccountMenu variant="header" />
                            </div>
                        </div>
                    </header>

                    <slot/>
                </div>
            </div>
        </div>
    </div>

    <FlashMessages />
</template>
