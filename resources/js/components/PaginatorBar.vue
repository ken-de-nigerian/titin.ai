<script setup lang="ts">
    import { Link } from '@inertiajs/vue3';
    import { computed } from 'vue';

    export interface PaginatorBarLink {
        url: string | null;
        label: string;
        active: boolean;
    }

    const props = withDefaults(
        defineProps<{
            links: PaginatorBarLink[];
            navigation?: 'inertia' | 'emit';
            preserveScroll?: boolean;
        }>(),
        {
            navigation: 'inertia',
            preserveScroll: true,
        },
    );

    const emit = defineEmits<{
        navigate: [url: string];
    }>();

    /**
     * Laravel paginator `links`: [Previous, …page numbers…, Next]. More than one page iff the middle has 2+ entries.
     */
    const showPagination = computed(() => {
        const { links } = props;

        if (links.length < 3) {
            return false;
        }

        return links.slice(1, -1).length > 1;
    });

    const pageLinks = computed(() => {
        const { links } = props;

        if (links.length < 3) {
            return [];
        }

        const all = links.slice(1, -1);
        const current = all.findIndex(l => l.active);

        if (all.length <= 7) {
            return all;
        }

        const first = all[0];
        const last = all[all.length - 1];
        const ellipsis = (label = '...'): PaginatorBarLink => ({
            url: null,
            label,
            active: false,
        });

        if (current <= 3) {
            return [
                ...all.slice(0, 5),
                ellipsis(),
                last,
            ];
        }

        if (current >= all.length - 4) {
            return [
                first,
                ellipsis(),
                ...all.slice(all.length - 5),
            ];
        }

        return [
            first,
            ellipsis(),
            ...all.slice(current - 1, current + 2),
            ellipsis(),
            last,
        ];
    });

    function stripLabel(label: string): string {
        return label.replace(/<[^>]*>/g, '').trim();
    }

    function isEllipsis(link: PaginatorBarLink): boolean {
        const t = stripLabel(link.label);

        return t === '...' || t === '…' || t === '&hellip;' || /^\.{3}$/.test(t);
    }

    function go(url: string | null): void {
        if (!url || props.navigation !== 'emit') {
            return;
        }

        emit('navigate', url);
    }
</script>

<template>
    <nav v-if="showPagination" class="pagination" aria-label="Pagination">
        <ul>
            <li v-for="(link, i) in pageLinks" :key="'p-' + i">
                <template v-if="isEllipsis(link)">
                    <span class="pagination-pager pagination-pager-inactive cursor-default" role="presentation" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h.01M12 12h.01M19 12h.01M6 12a1 1 0 1 1-2 0 1 1 0 0 1 2 0zm7 0a1 1 0 1 1-2 0 1 1 0 0 1 2 0zm7 0a1 1 0 1 1-2 0 1 1 0 0 1 2 0z" />
                        </svg>
                    </span>
                </template>

                <template v-else-if="navigation === 'inertia'">
                    <Link
                        v-if="link.url && !link.active"
                        :href="link.url"
                        class="pagination-pager pagination-pager-inactive"
                        :aria-label="`Go to page ${stripLabel(link.label)}`"
                        :preserve-scroll="preserveScroll">
                        {{ stripLabel(link.label) }}
                    </Link>

                    <span
                        v-else-if="link.active"
                        class="pagination-pager pagination-pager-active"
                        aria-current="page">
                        {{ stripLabel(link.label) }}
                    </span>

                    <span v-else class="pagination-pager pagination-pager-inactive cursor-default">
                        {{ stripLabel(link.label) }}
                    </span>
                </template>

                <template v-else>
                    <button
                        v-if="link.url && !link.active"
                        type="button"
                        class="pagination-pager pagination-pager-inactive"
                        :aria-label="`Go to page ${stripLabel(link.label)}`"
                        @click="go(link.url)">
                        {{ stripLabel(link.label) }}
                    </button>

                    <span
                        v-else-if="link.active"
                        class="pagination-pager pagination-pager-active"
                        aria-current="page">
                        {{ stripLabel(link.label) }}
                    </span>

                    <span v-else class="pagination-pager pagination-pager-inactive cursor-default">
                        {{ stripLabel(link.label) }}
                    </span>
                </template>
            </li>
        </ul>
    </nav>
</template>

<style scoped>
    ul {
        align-items: center;
        display: inline-flex;
        list-style: none;
        margin: 0;
        padding: 0;
    }

    nav button.pagination-pager {
        appearance: none;
        background: transparent;
        font: inherit;
    }
</style>
