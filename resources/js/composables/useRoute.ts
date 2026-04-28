import {usePage} from '@inertiajs/vue3';
import { route as ziggyRoute } from 'ziggy-js';
import type {Config, Router} from 'ziggy-js';

type RouteFunction = {
    (name: string, params?: any, absolute?: boolean): string;
    (): Router;
};

export function useRoute(): RouteFunction {
    const page = usePage();

    return ((name?: string, params?: any, absolute?: boolean) => {
        const config = page.props.ziggy as Config | undefined;

        if (name === undefined) {
            return ziggyRoute(undefined, undefined, absolute, config);
        }

        return ziggyRoute(name, params, absolute, config);
    }) as RouteFunction;
}
