import { usePage } from '@inertiajs/vue3';

export type UseSidebarNavActiveReturn = {
    isNavActive: (href: string) => boolean;
    isNavActiveAny: (hrefs: string[]) => boolean;
    pathOnly: (raw: string) => string;
    isResourceSectionNavActive: (indexHref: string) => boolean;
    isResourceListNavActive: (indexHref: string) => boolean;
};

function escapeRegexSegment(path: string): string {
    return path.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
}

/**
 * True when `current` is a child of `base` with at least one path segment after `base`
 * (e.g. show, edit, download), but not the literal `create` route or create-wizard subpaths.
 */
function matchesResourceIdChildPath(current: string, base: string): boolean {
    return new RegExp(`^${escapeRegexSegment(base)}/[^/]+(?:/.*)?$`).test(current);
}

/**
 * Compares the current Inertia URL path to a nav link href (path or full URL).
 */
export function useSidebarNavActive(): UseSidebarNavActiveReturn {
    const page = usePage();

    function pathOnly(raw: string): string {
        if (!raw) {
            return '/';
        }

        try {
            if (/^https?:\/\//i.test(raw)) {
                return new URL(raw).pathname || '/';
            }
        } catch {
            /* treat as a path */
        }

        let path = raw.split('#')[0] ?? raw;
        path = path.split('?')[0] ?? path;

        if (path.length > 1) {
            path = path.replace(/\/+$/, '');
        }

        return path || '/';
    }

    function isNavActive(href: string): boolean {
        return pathOnly(href) === pathOnly(page.url as string);
    }

    function isNavActiveAny(hrefs: string[]): boolean {
        const current = pathOnly(page.url as string);

        return hrefs.some((href) => pathOnly(href) === current);
    }

    function isResourceListNavActive(indexHref: string): boolean {
        const current = pathOnly(page.url as string);
        const base = pathOnly(indexHref);

        if (current === base) {
            return true;
        }

        if (current === `${base}/create` || current.startsWith(`${base}/create/`)) {
            return false;
        }

        return matchesResourceIdChildPath(current, base);
    }

    function isResourceSectionNavActive(indexHref: string): boolean {
        const current = pathOnly(page.url as string);
        const base = pathOnly(indexHref);

        if (current === base) {
            return true;
        }

        if (current === `${base}/create` || current.startsWith(`${base}/create/`)) {
            return true;
        }

        return matchesResourceIdChildPath(current, base);
    }

    return { isNavActive, isNavActiveAny, pathOnly, isResourceSectionNavActive, isResourceListNavActive };
}
