import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import { VitePWA } from 'vite-plugin-pwa';
import { fileURLToPath, URL } from 'node:url';

export default defineConfig({
    server: {
        host: '127.0.0.1',
        port: 5173,
    },
    plugins: [
        laravel({
            input: 'resources/js/app.js',
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
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
            workbox: {
                globPatterns: ['**/*.{js,css,html,ico,png,svg,woff2}'],
                runtimeCaching: [
                    {
                        urlPattern: /^https?:\/\/127\.0\.0\.1:8000\/api\/.*/i,
                        handler: 'NetworkFirst',
                        options: {
                            cacheName: 'api-cache',
                            expiration: {
                                maxEntries: 100,
                                maxAgeSeconds: 60 * 60 * 24,
                            },
                        },
                    },
                ],
            },
        }),
    ],
    resolve: {
        alias: {
            '@': fileURLToPath(new URL('./resources/js', import.meta.url)),
            'vue': 'vue/dist/vue.esm-bundler.js',
        },
    },
});
