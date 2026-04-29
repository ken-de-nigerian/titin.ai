import type { Directive } from 'vue';

declare module 'vue' {
    interface GlobalDirectives {
        fadeUp: Directive;
        fadeIn: Directive;
        slideUp: Directive;
    }
}

declare module '@vue/runtime-core' {
    interface GlobalDirectives {
        fadeUp: Directive;
        fadeIn: Directive;
        slideUp: Directive;
    }
}

export {};

