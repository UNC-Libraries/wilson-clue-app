import laravel from 'laravel-vite-plugin';
import * as path from "node:path";
import {defineConfig} from "vite";

const paths = {
    "node": path.resolve(__dirname, 'node_modules')
};

export default defineConfig({
    resolve: {
        alias: {
            '~bootstrap': path.resolve(__dirname, 'node_modules/bootstrap'),
            '~tempus-dominus': path.resolve(__dirname, 'node_modules/tempus-dominus'),
        }
    },
    plugins: [
        laravel({
            input: [
                'resources/assets/sass/ui.scss',
                'resources/assets/sass/web.scss',
                'resources/assets/sass/admin.scss',
                `${paths.node}/bootstrap/dist/js/bootstrap.bundle.min.js`,
                'resources/assets/js/app.js',
                'resources/assets/js/router.js'
            ],
            refresh: true,
        })
    ]
});