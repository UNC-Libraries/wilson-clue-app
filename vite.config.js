import laravel from 'laravel-vite-plugin';
import * as path from "node:path";
import {defineConfig} from "vite";
import inject from '@rollup/plugin-inject';

export default defineConfig({
    base: './',
    resolve: {
        alias: {
            '~bootstrap': path.resolve(__dirname, 'node_modules/bootstrap'),
            '~tempus-dominus': path.resolve(__dirname, 'node_modules/tempus-dominus'),
        }
    },
    plugins: [
        // Injects jquery as a global variable
        inject({
            $: 'jquery',
            jQuery: 'jquery'
        }),
        laravel({
            input: [
                'resources/assets/sass/ui.scss',
                'resources/assets/sass/web.scss',
                'resources/assets/sass/admin.scss',
                'resources/assets/js/app.js'
            ],
            refresh: true,
        })
    ]
});