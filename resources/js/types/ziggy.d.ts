import type { Config } from 'ziggy-js';

export interface ZiggyConfig extends Config {
    location: string;
}

declare global {
    function route(name?: string, params?: any, absolute?: boolean): string;
}

declare module 'vue' {
    interface ComponentCustomProperties {
        $ziggy: ZiggyConfig;
        route: typeof route;
    }
}

declare module '@inertiajs/vue3' {
    interface PageProps {
        ziggy?: ZiggyConfig;
    }
}
