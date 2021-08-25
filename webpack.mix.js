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
    .js('public/themes/default/src/js/app.js', 'public/themes/default/dist')
    .js('public/themes/default/src/js/messages.js', 'public/themes/default/dist')
    .sass('public/themes/default/src/sass/app.scss', 'public/themes/default/dist')

    /* mobile */
    .js('public/themes/mobile/src/js/app.js', 'public/themes/mobile/dist')
    .sass('public/themes/mobile/src/sass/app.scss', 'public/themes/mobile/dist')

    /* motor */
    .js('public/themes/motor/src/js/app.js', 'public/themes/motor/dist')
    .sass('public/themes/motor/src/sass/app.scss', 'public/themes/motor/dist')

    /* default-dark */
    .css('public/themes/default-dark/src/dark.css', 'public/themes/default-dark/dist')

    /* lang */
    .js('resources/lang/*/main.js', 'public/assets/js/dist/lang.js')

    .extract()
    .version();

mix.copy('node_modules/@fortawesome/fontawesome-free/webfonts/*', 'public/assets/fonts/');

mix.webpackConfig({
    resolve: {
        alias: {
            'js': path.resolve(__dirname, 'public/assets/js'),
            'css': path.resolve(__dirname, 'public/assets/css'),
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
