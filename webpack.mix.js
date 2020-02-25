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

mix.setPublicPath('./public');

mix.js('public/themes/default/src/js/app.js', 'public/themes/default/assets')
    .js('resources/lang/ru/main.js', 'public/assets/js/lang/ru.js')
    .js('resources/lang/en/main.js', 'public/assets/js/lang/en.js')
    .sass('public/themes/default/src/sass/app.scss', 'public/themes/default/assets')
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
        extractComments: '',
    }
});
