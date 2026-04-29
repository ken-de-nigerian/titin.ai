import '../css/app.css';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { MotionPlugin } from 'motion-v';
import type { DefineComponent } from 'vue';
import { createApp, createSSRApp, h } from 'vue';
import { ZiggyVue } from 'ziggy-js';
import type { ZiggyConfig } from '@/types/ziggy';

const appName = import.meta.env.VITE_APP_NAME || 'VoiceFlow AI';

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: (name) => resolvePageComponent(`./pages/${name}.vue`, import.meta.glob<DefineComponent>('./pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        const appFactory = import.meta.env.SSR ? createSSRApp : createApp;
        const app = appFactory({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .use(MotionPlugin, {
                presets: {
                    'fade-up': {
                        initial: { opacity: 0, y: 10 },
                        animate: { opacity: 1, y: 0 },
                        transition: { duration: 0.5, ease: 'easeOut' },
                    },
                    'fade-in': {
                        initial: { opacity: 0 },
                        animate: { opacity: 1 },
                        transition: { duration: 0.45, ease: 'easeOut' },
                    },
                    'slide-up': {
                        initial: { opacity: 0, y: 24 },
                        animate: { opacity: 1, y: 0 },
                        transition: { duration: 0.55, ease: [0.22, 1, 0.36, 1] },
                    },
                },
            });

        app.config.globalProperties.$ziggy = props.initialPage.props.ziggy as ZiggyConfig;

        if (!import.meta.env.SSR) {
            app.mount(el);
        }

        return app;
    },
    progress: {
        color: '#1b1b18',
    },
});
