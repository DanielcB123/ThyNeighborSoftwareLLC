import '../css/app.css';
import './bootstrap';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import { registerUrlBuilder } from '@/core/url/urlBuilderPlugin';
import type { FrontendRuntimeContext, InertiaSharedProps } from '@/core/runtime/frontendContext';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

function resolveRuntimeContext(props: unknown): FrontendRuntimeContext {
    const sharedProps = props as Partial<InertiaSharedProps>;

    if (!sharedProps.frontendRuntime) {
        throw new Error('Missing "frontendRuntime" shared Inertia prop.');
    }

    return sharedProps.frontendRuntime;
}

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue);

        registerUrlBuilder(app, resolveRuntimeContext(props.initialPage.props));

        app.mount(el);
        return app;
    },
    progress: {
        color: '#4B5563',
    },
});
