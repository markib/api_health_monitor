import './bootstrap';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3'; 
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';

import '../css/app.css';
// Import your global App.vue layout component
import AppLayout from './Pages/App.vue'; // Assuming App.vue is located at the root of your JS directory

createInertiaApp({
    resolve: (name) => {
        // Dynamically resolve the page component
        const pages = import.meta.glob('./Pages/**/*.vue', { eager: true });
        const page = resolvePageComponent(`./Pages/${name}.vue`, pages);

        // After the page component is resolved, assign your AppLayout as its default layout.
        // This ensures all pages loaded via Inertia will use App.vue as their wrapper.
        page.then((module) => {
            module.default.layout = AppLayout;
        });

        return page;
    },
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .mount(el);
    },
    // You can also add a title resolver here if you want dynamic page titles
    title: title => `${title ? `${title} - ` : ''}API Health Monitor`,
});