<script setup lang="ts">
    import { Head, router, usePoll } from '@inertiajs/vue3';
    import { CircleCheck, Download, Ellipsis, Star, Trash2 } from 'lucide-vue-next';
    import { computed, onMounted, onUnmounted, ref, watch, withDefaults } from 'vue';
    import { useRoute } from '@/composables/useRoute';
    import AppLayout from "@/layouts/AppLayout.vue";

    const route = useRoute();

    type CvItemFromServer = {
        id: number;
        name: string;
        size: number;
        status: string;
        is_active: boolean;
        created_at: string | null;
    };

    const props = withDefaults(
        defineProps<{
            cvItems: CvItemFromServer[];
        }>(),
        {
            cvItems: () => [],
        },
    );

    const resume = ref<File | null>(null);
    const pendingPipelineCvId = ref<number | null>(null);
    const isUploading = ref(false);
    const uploadError = ref<string | null>(null);

    type CvListItem = {
        id: number;
        name: string;
        meta: string;
        isActive: boolean;
        status: string;
    };

    function formatBytes(bytes: number): string {
        if (bytes < 1024) {
            return `${bytes} B`;
        }

        if (bytes < 1024 * 1024) {
            return `${(bytes / 1024).toFixed(0)} KB`;
        }

        return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
    }

    function formatTimestamp(iso: string | null): string {
        if (!iso) {
            return 'Unknown date';
        }

        const date = new Date(iso);

        return date.toLocaleString();
    }

    const uploadedCvs = computed<CvListItem[]>(() =>
        props.cvItems.map((item) => ({
            id: Number(item.id),
            name: String(item.name ?? 'Untitled'),
            meta: `${formatBytes(Number(item.size ?? 0))} · ${formatTimestamp(item.created_at ?? null)}`,
            isActive: Boolean(item.is_active),
            status: String(item.status ?? 'uploaded'),
        })),
    );

    const hasProcessingCv = computed(() =>
        uploadedCvs.value.some((item) => item.status === 'uploaded' || item.status === 'processing'),
    );

    const resumeDisplayName = computed(() => resume.value?.name ?? '');
    const showPipelinePanel = computed(
        () => resume.value !== null || pendingPipelineCvId.value !== null,
    );

    const pipelineDisplayName = computed(() => {
        if (resume.value) {
            return resumeDisplayName.value;
        }

        if (pendingPipelineCvId.value !== null) {
            const item = uploadedCvs.value.find((i) => i.id === pendingPipelineCvId.value);

            return item?.name ?? 'Your CV';
        }

        return '';
    });

    const isResumeAnalyzing = computed(() => {
        if (isUploading.value) {
            return true;
        }

        if (pendingPipelineCvId.value === null) {
            return false;
        }

        const item = uploadedCvs.value.find((i) => i.id === pendingPipelineCvId.value);

        if (!item) {
            return true;
        }

        return item.status === 'uploaded' || item.status === 'processing';
    });

    const pipelineStatusLine = computed(() => {
        if (isUploading.value) {
            return 'Uploading...';
        }

        if (isResumeAnalyzing.value) {
            return 'Analyzing your CV...';
        }

        return 'Ready';
    });

    watch(
        uploadedCvs,
        (items) => {
            const id = pendingPipelineCvId.value;

            if (id === null) {
                return;
            }

            const item = items.find((i) => i.id === id);

            if (item && (item.status === 'parsed' || item.status === 'failed')) {
                pendingPipelineCvId.value = null;
            }
        },
        { deep: true },
    );

    const { start, stop } = usePoll(
        2000,
        {
            only: ['cvItems'],
        },
        {
            autoStart: false,
        },
    );

    const shouldPoll = computed(() => hasProcessingCv.value || pendingPipelineCvId.value !== null);

    watch(
        shouldPoll,
        (run) => {
            if (run) {
                start();
            } else {
                stop();
            }
        },
        { immediate: true },
    );

    function reloadCvItems(): Promise<void> {
        return new Promise((resolve) => {
            router.reload({
                only: ['cvItems'],
                onFinish: () => resolve(),
            });
        });
    }

    function statusLabel(status: string): string {
        if (status === 'parsed') {
            return 'Parsed';
        }

        if (status === 'failed') {
            return 'Failed';
        }

        if (status === 'processing' || status === 'uploaded') {
            return 'Processing';
        }

        return 'Pending';
    }

    async function uploadResume(): Promise<void> {
        if (!resume.value || isUploading.value) {
            return;
        }

        isUploading.value = true;
        uploadError.value = null;

        try {
            const formData = new FormData();
            formData.append('resume', resume.value);

            const response = await fetch(route('user.cv.items.store'), {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: formData,
            });

            const payload = await response.json().catch(() => ({}));

            if (!response.ok) {
                const errorMessage = payload?.errors?.resume?.[0] ?? payload?.message ?? 'Upload failed.';
                uploadError.value = String(errorMessage);

                return;
            }

            const item = payload?.item as { id?: number } | undefined;

            if (item?.id !== undefined) {
                pendingPipelineCvId.value = Number(item.id);
            }

            await reloadCvItems();
            resume.value = null;
        } catch (error) {
            uploadError.value = error instanceof Error ? error.message : 'Upload failed.';
        } finally {
            isUploading.value = false;
        }
    }

    const handleResumeChange = (event: Event): void => {
        const input = event.target as HTMLInputElement;
        const selectedFile = input.files?.[0] ?? null;
        resume.value = selectedFile;

        if (!selectedFile) {
            return;
        }

        void uploadResume().finally(() => {
            input.value = '';
        });
    };

    const clearResume = (): void => {
        resume.value = null;
        pendingPipelineCvId.value = null;
        uploadError.value = null;
    };

    const CV_MENU_WIDTH_PX = 176;
    const CV_MENU_EDGE_PAD_PX = 8;

    const openMenuId = ref<number | null>(null);
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

    const toggleCvMenu = (id: number, event: MouseEvent): void => {
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

    const setActiveCv = async (id: number): Promise<void> => {
        await fetch(route('user.cv.items.activate', { cv: id }), {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });
        await reloadCvItems();
        closeCvMenu();
    };

    const removeCv = async (id: number): Promise<void> => {
        await fetch(route('user.cv.items.destroy', { cv: id }), {
            method: 'DELETE',
            credentials: 'same-origin',
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        if (id === pendingPipelineCvId.value) {
            pendingPipelineCvId.value = null;
        }

        await reloadCvItems();
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
                        CV / Resume
                    </h3>
                    <p class="text-sm text-muted-foreground">Upload your CV for personalized interview questions</p>
                </div>

                <div class="p-6 pt-0">
                    <label v-if="!showPipelinePanel" class="flex cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed border-hairline bg-surface p-8 transition-all hover:border-brand/40 hover:bg-brand-soft/40">
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
                                <p class="break-all font-medium text-foreground sm:truncate" :title="pipelineDisplayName">
                                    {{ pipelineDisplayName }}
                                </p>

                                <p class="truncate text-sm text-muted-foreground">
                                    {{ pipelineStatusLine }}
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
                    <p v-if="uploadError" class="mt-3 text-xs text-destructive">{{ uploadError }}</p>
                </div>
            </div>

            <section class="mt-6">
                <div class="mb-3 px-1">
                    <h2 class="text-sm font-semibold tracking-tight text-foreground">
                        Uploaded Cv / Resume ({{ uploadedCvs.length }})
                    </h2>
                    <p class="mt-0.5 text-xs text-muted-foreground">Active marked with ★</p>
                    <p v-if="hasProcessingCv" class="mt-1 text-xs text-muted-foreground animate-pulse">
                        Updating status...
                    </p>
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
                                    {{ statusLabel(item.status) }}
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

                                    <button type="button" role="menuitem" class="flex w-full items-center gap-2 border-t border-hairline px-3 py-2.5 text-left text-xs font-medium text-destructive transition hover:bg-surface-2" @click="removeCv(item.id)">
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
