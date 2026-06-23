// =============================================================================
// FICHIER : vite.config.js
// RÔLE    : Configuration du bundler Vite (compilation JS/CSS, PWA, build prod)
// ÉQUIPE  : GEL Cabinet — Équipe Dev Frontend
// =============================================================================
// Vite est le bundler de l'application. Il compile :
//   - Le JavaScript (Vue 3 + Composables) depuis resources/js/app.js
//   - Le CSS (Tailwind + Bootstrap personnalisé)
//   - Génère le Service Worker PWA pour le mode hors-ligne partiel
//
// Architecture des builds :
//   - Dev :  Vite HMR → http://127.0.0.1:5173 (proxy vers Laravel sur :8000)
//   - Prod :  npm run build → public/build/assets/ (fichiers versionnés)
//
// ⚠️  Ne JAMAIS commit les fichiers dans public/build/ — ils sont
//     générés à chaque build et changent de hash à chaque version.
// =============================================================================

import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import { VitePWA } from 'vite-plugin-pwa';
import { fileURLToPath, URL } from 'node:url';

export default defineConfig({

    // ─── Build ─────────────────────────────────────────────────────────────
    // Options de compilation pour la production.
    build: {
        // Désactive les sourcemaps en production :
        // Les sourcemaps en dev aident au débogage, mais en prod elles
        // exposent le code source complet dans les DevTools du navigateur.
        sourcemap: process.env.APP_ENV !== 'production',
    },

    // ─── Serveur de développement ──────────────────────────────────────────
    // Hôte et port du serveur Vite HMR (utilisé uniquement en dev).
    // Laravel Herd / Valet / Sail utilisent des configurations différentes.
    server: {
        host: '127.0.0.1',
        port: 5173,
    },

    // ─── Plugins ──────────────────────────────────────────────────────────
    plugins: [
        // Plugin Laravel officiel : injecte les balises <script>/<link>
        // vers les bons fichiers compilés (HMR en dev, versionnés en prod).
        laravel({
            input: 'resources/js/app.js',
            refresh: true,
        }),

        // Plugin Vue 3 : support du SFC (.vue), templates, Composition API.
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),

        // Plugin PWA : génère un Service Worker (Workbox) pour la mise
        // en cache des assets et le fonctionnement hors-ligne partiel.
        VitePWA({
            registerType: 'autoUpdate',
            includeAssets: ['favicon.ico'],
            manifest: {
                name: 'GEL Cabinet',
                short_name: 'GEL',
                description: 'Cabinet de gestion et d\'expertise — GEL',
                theme_color: '#163A5E',
                background_color: '#f0f4f8',
                display: 'standalone',
                orientation: 'portrait',
                start_url: '/',
                icons: [
                    {
                        src: '/images/icons/icon-192x192.svg',
                        sizes: '192x192',
                        type: 'image/svg+xml',
                    },
                    {
                        src: '/images/icons/icon-512x512.svg',
                        sizes: '512x512',
                        type: 'image/svg+xml',
                    },
                    {
                        src: '/images/icons/icon-512x512.svg',
                        sizes: '512x512',
                        type: 'image/svg+xml',
                        purpose: 'maskable',
                    },
                ],
            },
            // Configuration Workbox : stratégies de cache pour les assets
            // et les appels API.
            workbox: {
                globPatterns: ['**/*.{js,css,html,ico,png,svg,woff2}'],
                runtimeCaching: [
                    {
                        // Cache NetworkFirst pour les appels API :
                        // tente le réseau d'abord, sinon sert le cache.
                        urlPattern: /^https?:\/\/127\.0\.0\.1:8000\/api\/.*/i,
                        handler: 'NetworkFirst',
                        options: {
                            cacheName: 'api-cache',
                            expiration: {
                                maxEntries: 100,
                                maxAgeSeconds: 60 * 60 * 24, // 24h
                            },
                        },
                    },
                ],
            },
        }),
    ],

    // ─── Résolution d'imports ─────────────────────────────────────────────
    resolve: {
        alias: {
            // Alias @ → resources/js/ pour des imports plus courts :
            //   import { machin } from '@/utils/monModule'
            '@': fileURLToPath(new URL('./resources/js', import.meta.url)),
            // Résolution explicite de Vue pour éviter l'avertissement
            // "You are using the runtime-only build of Vue"
            'vue': 'vue/dist/vue.esm-bundler.js',
        },
    },
});
