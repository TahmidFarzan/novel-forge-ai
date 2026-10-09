import { fileURLToPath, URL } from 'node:url';
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import vue from '@vitejs/plugin-vue';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
            fonts: [
                bunny('Instrument Sans', {
                    weights: [400, 500, 600],
                }),
            ],
        }),
        vue(),
        tailwindcss(),
    ],
    resolve: {
        alias: {
            '@': fileURLToPath(new URL('./resources/js', import.meta.url)),
        },
    },
    server: {
        host: '0.0.0.0',
        port: 5173,
        strictPort: true,
        allowedHosts: true,
        cors: true,
        origin:
            process.env.VITE_DEV_SERVER_ORIGIN ||
            'http://novel-forge-ai.localhost',
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
        ws: {
            host: process.env.VITE_HMR_HOST || 'novel-forge-ai.localhost',
            protocol: 'ws',
            clientPort: Number(process.env.VITE_HMR_CLIENT_PORT || 80),
            path: '/vite-hmr',
        },
    },
});
