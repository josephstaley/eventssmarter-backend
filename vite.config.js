import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import vue from "@vitejs/plugin-vue";
import path from 'path'

export default defineConfig({
    plugins: [
        vue(),
        laravel({
            input: ["resources/css/app.css", "resources/js/app.js"],
            resolve: {
                alias: {
                    '~bootstrap': path.resolve(__dirname, 'node_modules/bootstrap'),
                    '$': 'jQuery'
                }
            },
            refresh: true,
        }),
    ],
});
