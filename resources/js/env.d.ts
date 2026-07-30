declare module '*.vue' {
    import type { DefineComponent } from 'vue';

    const component: DefineComponent<Record<string, unknown>, Record<string, unknown>, any>;
    export default component;
}

declare module '../../vendor/tightenco/ziggy' {
    import type { Plugin } from 'vue';

    export const ZiggyVue: Plugin;
}
