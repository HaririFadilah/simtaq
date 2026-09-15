import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import { quasar, transformAssetUrls } from '@quasar/vite-plugin';
import { VitePWA } from 'vite-plugin-pwa';
import path from 'path';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        vue({
            template: { transformAssetUrls }
        }),
        quasar({
            sassVariables: path.resolve(__dirname, 'resources/css/quasar-variables.sass')
        }),
        VitePWA({
            registerType: 'autoUpdate',
            manifest: {
                name: 'SIMTAQ - Yayasan Al Mukhlisin',
                short_name: 'SIMTAQ',
                description: 'Sistem Informasi Manajemen Santri dan Tahfiz Al-Qur\'an',
                theme_color: '#0D7C66',
                background_color: '#F4F7F6',
                display: 'standalone',
                start_url: '/',
                icons: [
                    {
                        src: '/assets/logo-header.jpeg',
                        sizes: '192x192',
                        type: 'image/jpeg'
                    },
                    {
                        src: '/assets/logo-inti.jpeg',
                        sizes: '512x512',
                        type: 'image/jpeg'
                    }
                ]
            }
        })
    ],
    resolve: {
        alias: {
            '@': path.resolve(__dirname, 'resources/js'),
        }
    }
});
