export * from './auth';

import type { Config } from 'ziggy-js';

export type AppPageProps<T extends Record<string, unknown> = Record<string, unknown>> = T & {
    name: string;
    quote: { message: string; author: string };
    ziggy: Config & { location: string };
};
