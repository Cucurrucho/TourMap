import './bootstrap';
import '../css/app.css';

import {createApp, h} from 'vue';
import {createInertiaApp} from '@inertiajs/vue3';
import {resolvePageComponent} from 'laravel-vite-plugin/inertia-helpers';
import {ZiggyVue} from '../../vendor/tightenco/ziggy/dist/vue.m';
import VueGoogleMaps from '@fawmi/vue-google-maps'
import ToastPlugin from 'vue-toast-notification';
import 'vue-toast-notification/dist/theme-sugar.css';


const appName = window.document.getElementsByTagName('title')[0]?.innerText || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({el, App, props, plugin}) {
        return createApp({render: () => h(App, props)})
            .use(plugin)
            .use(ZiggyVue, Ziggy)
            .use(ToastPlugin)
            .use(VueGoogleMaps, {
                load: {
                    key: 'AIzaSyCYv-72Jwz1NIrWHmomWZsjsmoMSPdNnGs',
                },
                autobindAllEvents: true,
            })
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});
