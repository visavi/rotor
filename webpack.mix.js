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
    .js('public/themes/default/src/js/app.js', 'public/themes/default/dist')
    .js('public/themes/default/src/js/messages.js', 'public/themes/default/dist')
    .sass('public/themes/default/src/sass/app.scss', 'public/themes/default/dist')

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
