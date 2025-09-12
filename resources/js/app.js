import '../css/app.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/inertia-vue3';
import { InertiaProgress } from '@inertiajs/progress';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy/dist/vue.m';
import { createI18n } from 'vue-i18n';

const appName = window.document.getElementsByTagName('title')[0]?.innerText || 'Laravel';
import en from './lang/en.json';
import ar from './lang/ar.json';
import kr from './lang/kr.json';

const i18n = createI18n({
  legacy: false,
  locale: 'ar', // Set the default locale
  messages: {
    en, // English translations
    ar, // Arabic translations
    kr
  },
});

// التحقق من وجود عنصر #app مع data-page صحيح قبل تهيئة Inertia
const appElement = document.getElementById('app');
if (appElement && appElement.dataset.page) {
    try {
        const pageData = JSON.parse(appElement.dataset.page);
        if (pageData && pageData.component) {
            createInertiaApp({
                title: (title) => `${title} - ${appName}`,
                resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
                setup({ el, app, props, plugin }) {
                    return createApp({ render: () => h(app, props) })
                        .use(plugin)
                        .use(i18n)
                        .use(ZiggyVue, Ziggy)
                        .mount(el);
                },
            });

            InertiaProgress.init({ color: '#4B5563' });
        } else {
            console.log('No valid Inertia page data found, skipping Inertia initialization');
        }
    } catch (error) {
        console.log('Invalid Inertia page data, skipping Inertia initialization:', error.message);
    }
} else {
    console.log('Inertia app element not found or no page data, skipping Inertia initialization');
}
