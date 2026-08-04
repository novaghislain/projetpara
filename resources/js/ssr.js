/* ============================================================
 * Fichier : ssr.js
 * Description : Point d'entree pour le rendu cote serveur (SSR)
 * Configure Inertia.js avec Vue 3 pour le rendu SSR
 * Utilise Ziggy pour la generation d'URLs cote serveur
 * ============================================================ */

import { createInertiaApp } from '@inertiajs/vue3';
import createServer from '@inertiajs/vue3/server';
import { renderToString } from '@vue/server-renderer';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createSSRApp, h } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy/dist/index.esm.js';

// Nom de l'application defini dans les variables d'environnement Vite
const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

// Cree le serveur SSR Inertia avec la configuration complete
createServer((page) =>
    createInertiaApp({
        page,
        // Utilise le rendu SSR via renderToString de Vue
        render: renderToString,
        // Definit le titre de chaque page : "Titre - NomApp"
        title: (title) => `${title} - ${appName}`,
        // Resolution dynamique des composants de pages via Vite glob import
        resolve: (name) =>
            resolvePageComponent(
                `./Pages/${name}.vue`,
                import.meta.glob('./Pages/**/*.vue'),
            ),
        // Configuration de l'application Vue pour le SSR avec Ziggy
        setup({ App, props, plugin }) {
            return createSSRApp({ render: () => h(App, props) })
                .use(plugin)
                .use(ZiggyVue, {
                    ...page.props.ziggy,
                    location: new URL(page.props.ziggy.location),
                });
        },
    }),
);
