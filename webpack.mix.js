const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

var paths = {
    "node": "./node_modules/"
};

mix.setResourceRoot('../')
    //game
    .sass('resources/assets/sass/ui.scss', 'public/css')
    //web
    .sass('resources/assets/sass/web.scss', 'public/css')
    //admin scripts
    .sass('resources/assets/sass/admin.scss', 'public/css')
    .styles([
            'public/css/admin.css',
            paths.node + '@eonasdan/tempus-dominus/dist/css/tempus-dominus.min.css',
        ],
        'public/css/all-admin.css')

    //scripts
    .scripts([
            paths.node + 'jquery/dist/jquery.min.js',
            paths.node + 'bootstrap/dist/js/bootstrap.bundle.min.js',
            paths.node + '@popperjs/core/dist/umd/popper.min.js',
            paths.node + '@eonasdan/tempus-dominus/dist/js/tempus-dominus.min.js',
            paths.node + '@eonasdan/tempus-dominus/dist/js/jQuery-provider.min.js',
            paths.node + 'sortablejs/Sortable.min.js',
            paths.node + 'clipboard/dist/clipboard.min.js',
            'resources/assets/js/app.js',
            'resources/assets/js/router.js'
        ],
        'public/js/app.js');