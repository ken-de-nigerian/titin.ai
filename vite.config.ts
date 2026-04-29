import inertia from '@inertiajs/vite';
import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';
import { defineConfig } from 'vite';
import { VitePWA } from 'vite-plugin-pwa';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.ts'],
            refresh: true,
        }),
        inertia(),
        tailwindcss(),
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
            includeAssets: ['favicon.ico', 'icons/*.png'],
            manifest: {
                name: 'VoiceFlow AI',
                short_name: 'VoiceFlow',
                description: 'AI-powered voice interview practice',
                theme_color: '#0A0A0A',
                background_color: '#fafaf9',
                display: 'standalone',
                orientation: 'portrait-primary',
                // App routes live under /user/* in this codebase (no route changes here by request).
                start_url: '/user/dashboard',
                scope: '/',
                icons: [
                    {
                        src: '/icons/icon-192.png',
                        sizes: '192x192',
                        type: 'image/png'
                    },
                    {
                        src: '/icons/icon-512.png',
                        sizes: '512x512',
                        type: 'image/png'
                    },
                    {
                        src: '/icons/icon-512.png',
                        sizes: '512x512',
                        type: 'image/png',
                        purpose: 'maskable'
                    }
                ]
            },
            workbox: {
                navigateFallback: null,
                globPatterns: ['**/*.{js,css,html,ico,png,svg,woff2}']
            }
        }),
    ],
    server: {
        proxy: {
            '/api': {
                target: process.env.VITE_TOKEN_SERVER_URL || 'http://localhost:5001',
                changeOrigin: true,
                rewrite: (path) => path.replace(/^\/api/, ''),
            },
        },
    },
});
