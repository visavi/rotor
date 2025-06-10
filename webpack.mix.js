const mix = require('laravel-mix');
const path = require('path');
/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel applications. By default, we are compiling the CSS
 | file for the application as well as bundling up all the JS files.
 |
 */
mix.setPublicPath('./public');

mix
    /* default */
    .js('public/themes/default/src/js/app.js', 'public/assets/dist/js/default.js')
    .js('public/themes/default/src/js/messages.js', 'public/assets/dist/js/messages.js')
    .sass('public/themes/default/src/sass/app.scss', 'public/assets/dist/css/default.css')

    /* mobile */
    .js('public/themes/mobile/src/js/app.js', 'public/assets/dist/js/mobile.js')
    .sass('public/themes/mobile/src/sass/app.scss', 'public/assets/dist/css/mobile.css')

    /* motor */
    .js('public/themes/motor/src/js/app.js', 'public/assets/dist/js/motor.js')
    .sass('public/themes/motor/src/sass/app.scss', 'public/assets/dist/css/motor.css')

    /* lang */
    .js('resources/lang/*/main.js', 'public/assets/dist/js/lang.js')

    /* chartist */
    .styles([
        'public/assets/css/chartist.min.css',
        'public/assets/css/chartist-plugin-tooltip.css'
    ], 'public/assets/dist/css/chartist-bundle.css')

    .combine([
        'public/assets/js/chartist.min.js',
        'public/assets/js/chartist-plugin-tooltip.min.js'
    ], 'public/assets/dist/js/chartist-bundle.js')

    /* fontawesome */
    .copy('node_modules/@fortawesome/fontawesome-free/webfonts/*', 'public/assets/dist/fonts')

    .extract()
    .version();

mix.webpackConfig({
    resolve: {
        alias: {
            'js': path.resolve(__dirname, 'public/assets/js'),
            'css': path.resolve(__dirname, 'public/assets/css'),
            'themes': path.resolve(__dirname, 'public/themes'),
        }
    }
});

mix.options({
    cssNano: {
        discardComments: {
            removeAll: true,
        },
    },
    terser: {
        terserOptions: {
            format: {
                comments: false,
            },
        },
        extractComments: false,
    }
});
