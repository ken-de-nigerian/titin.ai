<script setup lang="ts">
    import {Head, usePage} from "@inertiajs/vue3";
    import {CircleCheck, Download, Ellipsis, Star, Trash2} from "lucide-vue-next";
    import {computed, onMounted, onUnmounted, ref} from "vue";
    import AppLayout from "@/layouts/AppLayout.vue";

    const page = usePage();
    const authUser = computed(() => (page.props as { auth?: { user?: { name?: string } } }).auth?.user ?? null);

    const resume = ref<File | null>(null);
    const isResumeAnalyzing = ref(false);
    let resumeAnalyzeTimer: ReturnType<typeof setTimeout> | null = null;

    const resumeDisplayName = computed(() => {
        const file = resume.value;

        if (!file) {
            return "";
        }

        const slug = (authUser.value?.name ?? "user").toLowerCase().replaceAll(" ", "_");

        return `${slug}_${file.name}`;
    });

    const handleResumeChange = (event: Event): void => {
        const input = event.target as HTMLInputElement;
        const selectedFile = input.files?.[0] ?? null;
        resume.value = selectedFile;

        if (resumeAnalyzeTimer) {
            clearTimeout(resumeAnalyzeTimer);
            resumeAnalyzeTimer = null;
        }

        if (!selectedFile) {
            isResumeAnalyzing.value = false;

            return;
        }

        isResumeAnalyzing.value = true;
        resumeAnalyzeTimer = setTimeout(() => {
            isResumeAnalyzing.value = false;
            resumeAnalyzeTimer = null;
        }, 1400);
    };

    const clearResume = (): void => {
        resume.value = null;
        isResumeAnalyzing.value = false;

        if (resumeAnalyzeTimer) {
            clearTimeout(resumeAnalyzeTimer);
            resumeAnalyzeTimer = null;
        }
    };

    type CvListItem = {
        id: string;
        name: string;
        meta: string;
        isActive: boolean;
    };

    const uploadedCvs = ref<CvListItem[]>([
        {id: "1", name: "Alex_Morgan_Product_Manager_2026.pdf", meta: "412 KB · Today · 14:22", isActive: true},
        {id: "2", name: "Alex_Morgan_Resume_v3.pdf", meta: "287 KB · Mar 18 · 09:10", isActive: false},
        {id: "3", name: "Portfolio_one_pager.pdf", meta: "1.1 MB · Feb 02 · 18:44", isActive: false},
    ]);

    const CV_MENU_WIDTH_PX = 176;
    const CV_MENU_EDGE_PAD_PX = 8;

    const openMenuId = ref<string | null>(null);
    const openMenuHorizontal = ref<"left" | "right">("right");

    function horizontalPlacementForTrigger(trigger: HTMLElement): "left" | "right" {
        const br = trigger.getBoundingClientRect();
        const vw = window.innerWidth;
        const mw = CV_MENU_WIDTH_PX;
        const pad = CV_MENU_EDGE_PAD_PX;

        const leftEdgeIfRightAnchored = br.right - mw;
        const rightEdgeIfLeftAnchored = br.left + mw;

        const rightAnchorFits = leftEdgeIfRightAnchored >= pad;
        const leftAnchorFits = rightEdgeIfLeftAnchored <= vw - pad;

        if (rightAnchorFits && leftAnchorFits) {
            return "right";
        }

        if (rightAnchorFits) {
            return "right";
        }

        if (leftAnchorFits) {
            return "left";
        }

        return vw - br.right > br.left ? "right" : "left";
    }

    const toggleCvMenu = (id: string, event: MouseEvent): void => {
        if (openMenuId.value === id) {
            openMenuId.value = null;

            return;
        }

        const trigger = event.currentTarget;

        if (trigger instanceof HTMLElement) {
            openMenuHorizontal.value = horizontalPlacementForTrigger(trigger);
        }

        openMenuId.value = id;
    };

    const closeCvMenu = (): void => {
        openMenuId.value = null;
    };

    const setActiveCv = (id: string): void => {
        uploadedCvs.value = uploadedCvs.value.map((row) => ({
            ...row,
            isActive: row.id === id,
        }));
        closeCvMenu();
    };

    function onDocumentPointerCloseMenus(event: MouseEvent): void {
        const target = event.target;

        if (target instanceof Element && !target.closest("[data-cv-menu]")) {
            closeCvMenu();
        }
    }

    onMounted(() => {
        document.addEventListener("click", onDocumentPointerCloseMenus);
    });

    onUnmounted(() => {
        document.removeEventListener("click", onDocumentPointerCloseMenus);
    });
</script>

<template>
    <Head title="CV and Resume" />

    <AppLayout>
        <main class="flex-1 overflow-y-auto px-5 pb-28 md:mx-auto md:w-full md:max-w-5xl md:px-0 md:pb-10">
            <div class="overflow-hidden rounded-2xl border border-hairline bg-surface text-foreground shadow-sm">
                <div class="flex flex-col space-y-1.5 p-6 pb-4">
                    <h3 class="flex items-center gap-2 text-lg font-semibold tracking-tight">
                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" width="24" height="24">
                            <path d="M21 7V17C21 20 19.5 22 16 22H8C4.5 22 3 20 3 17V7C3 4 4.5 2 8 2H16C19.5 2 21 4 21 7Z" stroke="currentColor" stroke-width="1.75" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                            <path d="M14.5 4.5V6.5C14.5 7.6 15.4 8.5 16.5 8.5H18.5" stroke="currentColor" stroke-width="1.75" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                            <path d="M8 13H12" stroke="currentColor" stroke-width="1.75" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                            <path d="M8 17H16" stroke="currentColor" stroke-width="1.75" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                        </svg>
                        CV / Resume (optional)
                    </h3>
                    <p class="text-sm text-muted-foreground">Upload your CV for personalized interview questions</p>
                </div>

                <div class="p-6 pt-0">
                    <label v-if="!resume" class="flex cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed border-hairline bg-surface p-8 transition-all hover:border-brand/40 hover:bg-brand-soft/40">
                        <input type="file" class="hidden" accept=".pdf,.docx" @change="handleResumeChange" />
                        <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-full border bg-accent">
                            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" width="24" height="24">
                                <path d="M21 7V17C21 20 19.5 22 16 22H8C4.5 22 3 20 3 17V7C3 4 4.5 2 8 2H16C19.5 2 21 4 21 7Z" stroke="currentColor" stroke-width="1.75" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                <path d="M14.5 4.5V6.5C14.5 7.6 15.4 8.5 16.5 8.5H18.5" stroke="currentColor" stroke-width="1.75" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                <path d="M8 13H12" stroke="currentColor" stroke-width="1.75" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                <path d="M8 17H16" stroke="currentColor" stroke-width="1.75" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                            </svg>
                        </div>
                        <p class="mb-1 font-medium text-foreground">Drop your CV here or click to browse</p>
                        <p class="text-sm text-muted-foreground">PDF or DOCX, max 10MB</p>
                    </label>

                    <div v-else class="flex flex-col gap-3 rounded-xl border bg-surface p-4 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex w-full min-w-0 items-center gap-3">
                            <div class="h-10 w-10 rounded-lg bg-accent flex items-center justify-center">
                                <svg v-if="isResumeAnalyzing" stroke="currentColor" fill="currentColor" stroke-width="0" version="1.1" viewBox="0 0 16 16" class="spinner animate-spin" height="1em" width="1em" xmlns="http://www.w3.org/2000/svg" style="height: 20px; width: 20px;">
                                    <path d="M8 0c-4.355 0-7.898 3.481-7.998 7.812 0.092-3.779 2.966-6.812 6.498-6.812 3.59 0 6.5 3.134 6.5 7 0 0.828 0.672 1.5 1.5 1.5s1.5-0.672 1.5-1.5c0-4.418-3.582-8-8-8zM8 16c4.355 0 7.898-3.481 7.998-7.812-0.092 3.779-2.966 6.812-6.498 6.812-3.59 0-6.5-3.134-6.5-7 0-0.828-0.672-1.5-1.5-1.5s-1.5 0.672-1.5 1.5c0 4.418 3.582 8 8 8z"></path>
                                </svg>
                                <svg v-else viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" width="24" height="24">
                                    <path d="M21 7V17C21 20 19.5 22 16 22H8C4.5 22 3 20 3 17V7C3 4 4.5 2 8 2H16C19.5 2 21 4 21 7Z" stroke="currentColor" stroke-width="1.75" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                    <path d="M14.5 4.5V6.5C14.5 7.6 15.4 8.5 16.5 8.5H18.5" stroke="currentColor" stroke-width="1.75" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                    <path d="M8 13H12" stroke="currentColor" stroke-width="1.75" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                    <path d="M8 17H16" stroke="currentColor" stroke-width="1.75" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                </svg>
                            </div>

                            <div class="min-w-0 flex-1">
                                <p class="break-all font-medium text-foreground sm:truncate" :title="resumeDisplayName">
                                    {{ resumeDisplayName }}
                                </p>

                                <p class="truncate text-sm text-muted-foreground">
                                    {{ isResumeAnalyzing ? 'Uploading and analyzing...' : 'Uploaded successfully • AI analyzed' }}
                                </p>
                            </div>
                        </div>

                        <div class="flex w-full shrink-0 items-center gap-2 sm:w-auto">
                            <label class="inline-flex h-9 flex-1 cursor-pointer items-center justify-center gap-2 whitespace-nowrap rounded-md border border-hairline bg-surface px-4 text-xs font-semibold text-foreground ring-offset-background transition-all duration-200 hover:bg-surface-2 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 sm:flex-none [&_svg]:pointer-events-none [&_svg]:size-4 [&_svg]:shrink-0">
                                Replace
                                <input type="file" class="hidden" accept=".pdf,.docx" @change="handleResumeChange" />
                            </label>

                            <button type="button" class="inline-flex h-9 flex-1 items-center justify-center gap-2 whitespace-nowrap rounded-md border border-destructive/30 bg-surface px-4 text-xs font-semibold text-destructive ring-offset-background transition-all duration-200 hover:bg-destructive/10 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 sm:flex-none" @click="clearResume">
                                Remove
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <section class="mt-6">
                <div class="mb-3 px-1">
                    <h2 class="text-sm font-semibold tracking-tight text-foreground">
                        Uploaded ({{ uploadedCvs.length }})
                    </h2>
                    <p class="mt-0.5 text-xs text-muted-foreground">Active marked with ★</p>
                </div>

                <ul class="surface divide-y divide-hairline overflow-visible rounded-2xl shadow-xs">
                    <li v-for="item in uploadedCvs" :key="item.id" class="px-4 py-3.5 transition">
                        <div class="flex items-center gap-3">
                            <div class="grid h-10 w-10 shrink-0 place-items-center rounded-lg bg-surface-2 text-foreground">
                                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" width="24" height="24">
                                    <path d="M21 7V17C21 20 19.5 22 16 22H8C4.5 22 3 20 3 17V7C3 4 4.5 2 8 2H16C19.5 2 21 4 21 7Z" stroke="currentColor" stroke-width="1.75" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                    <path d="M14.5 4.5V6.5C14.5 7.6 15.4 8.5 16.5 8.5H18.5" stroke="currentColor" stroke-width="1.75" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                    <path d="M8 13H12" stroke="currentColor" stroke-width="1.75" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                    <path d="M8 17H16" stroke="currentColor" stroke-width="1.75" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                </svg>
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-1.5">
                                    <p class="truncate text-sm font-medium">{{ item.name }}</p>
                                    <Star v-if="item.isActive" class="h-3 w-3 shrink-0 fill-brand text-brand" aria-hidden="true"/>
                                </div>

                                <p class="mt-0.5 text-[11px] text-muted-foreground">{{ item.meta }}</p>
                                <div class="mt-1.5 inline-flex items-center gap-1 text-[10px] font-medium text-success">
                                    <CircleCheck class="h-3 w-3" aria-hidden="true" />
                                    Parsed
                                </div>
                            </div>

                            <div data-cv-menu class="relative shrink-0">
                                <button type="button" class="grid h-8 w-8 place-items-center rounded-full text-muted-foreground transition hover:bg-surface-2 hover:text-foreground" aria-label="More" :aria-expanded="openMenuId === item.id" aria-haspopup="menu" @click.stop="toggleCvMenu(item.id, $event)">
                                    <Ellipsis class="h-4 w-4" aria-hidden="true" />
                                </button>

                                <div v-if="openMenuId === item.id" class="absolute top-9 z-20 w-44 overflow-hidden rounded-xl border border-hairline bg-background shadow-lg" :class="openMenuHorizontal === 'right' ? 'right-0' : 'left-0'" role="menu" @click.stop>
                                    <button type="button" role="menuitem" class="flex w-full items-center gap-2 px-3 py-2.5 text-left text-xs font-medium transition hover:bg-surface-2 disabled:opacity-40" :disabled="item.isActive" @click="setActiveCv(item.id)">
                                        <Star class="h-3.5 w-3.5" aria-hidden="true" />
                                        Active CV
                                    </button>

                                    <button type="button" role="menuitem" class="flex w-full items-center gap-2 px-3 py-2.5 text-left text-xs font-medium transition hover:bg-surface-2" @click="closeCvMenu">
                                        <Download class="h-3.5 w-3.5" aria-hidden="true" />
                                        Download
                                    </button>

                                    <button type="button" role="menuitem" class="flex w-full items-center gap-2 border-t border-hairline px-3 py-2.5 text-left text-xs font-medium text-destructive transition hover:bg-surface-2" @click="closeCvMenu">
                                        <Trash2 class="h-3.5 w-3.5" aria-hidden="true" />
                                        Remove
                                    </button>
                                </div>
                            </div>
                        </div>
                    </li>
                </ul>
            </section>
        </main>
    </AppLayout>
</template>
