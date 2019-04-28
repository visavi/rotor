let mix = require('laravel-mix');

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

mix.scripts([
    'public/assets/js/jquery-3.3.1.min.js',
    'public/assets/js/bootstrap.bundle.min.js',
    'public/assets/js/bootstrap-colorpicker.min.js',
    'public/assets/js/prettify.js',
    'public/assets/js/bootbox.min.js',
    'public/assets/js/toastr.min.js',
    'public/assets/js/markitup/jquery.markitup.js',
    'public/assets/js/markitup/markitup.set.js',
    'public/assets/js/mediaelement/mediaelement-and-player.min.js',
    'public/assets/js/colorbox/jquery.colorbox-min.js',
    'public/assets/js/jquery.mask.min.js',
    'public/assets/js/app.js'
], 'public/assets/modules/compiled.js');

mix.styles([
    'public/assets/css/bootstrap.min.css',
    'public/assets/css/bootstrap-colorpicker.min.css',
    'public/assets/css/fontawesome.min.css',
    'public/assets/css/prettify.css',
    'public/assets/css/toastr.min.css',
    'public/assets/js/markitup/markitup.css',
    'public/assets/js/mediaelement/mediaelementplayer.min.css',
    'public/assets/js/colorbox/colorbox.css',
    'public/assets/css/app.css'
], 'public/assets/modules/compiled.css');
