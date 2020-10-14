var elixir = require('laravel-elixir');

/*
 |--------------------------------------------------------------------------
 | Elixir Asset Management
 |--------------------------------------------------------------------------
 |
 | Elixir provides a clean, fluent API for defining some basic Gulp tasks
 | for your Laravel application. By default, we are compiling the Sass
 | file for our application, as well as publishing vendor resources.
 |
 */

var paths = {
    "node": "./node_modules/"
}

elixir(function(mix) {
    mix
        //game
        .sass('ui.scss')
        //web
        .sass('web.scss')

        //admin scripts
        .sass(['admin.scss'],'./resources/assets/css/admin.css')
        .styles([
            'admin.css',
            paths.node + 'eonasdan-bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.css',
        ],
        'public/css/admin.css')

        //scripts
        .scripts([
            paths.node + 'jquery/dist/jquery.js',
            paths.node + 'bootstrap-sass/assets/javascripts/bootstrap.js',
            paths.node + 'moment/moment.js',
            paths.node + 'eonasdan-bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js',
            paths.node + 'sortablejs/Sortable.min.js',
            paths.node + 'clipboard/dist/clipboard.min.js',
            'app.js',
            'router.js',
            'chat-widget.js'
        ],
        'public/js/app.js')

        //fonts
        .copy(paths.node+'bootstrap-sass/assets/fonts/bootstrap','public/fonts/bootstrap')
        .copy(paths.node+'font-awesome/fonts/','public/fonts/font-awesome');
});
