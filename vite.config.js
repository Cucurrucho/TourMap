import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import { VitePWA } from 'vite-plugin-pwa'

export default defineConfig({
    plugins: [
        laravel({
            input: 'resources/js/app.js',
            refresh: true,
        }),
        VitePWA({
            strategies: "injectManifest",
            injectRegister: null,
            manifestFilename: 'manifest.json',
            srcDir: "resources/js",
            filename: "service-worker.js",
            start_url: "../",
            display: "standalone",
            orientation: "portrait",
            background_color: "#363636",
            theme_color: "#209CEE",
            manifest: {
                name: "Easy Tours",
                short_name: "Easy Tours"
            }
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
    optimizeDeps: {
        include: [
            "@fawmi/vue-google-maps",
            "fast-deep-equal",
        ],
    },
});
