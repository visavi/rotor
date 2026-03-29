import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import path from 'path';

export default defineConfig({
    optimizeDeps: {
        include: ['jquery', 'bootstrap'],
    },
    plugins: [
        laravel({
            input: [
                'public/assets/js/jquery.js',
                'resources/themes/default/js/app.js',
                'resources/themes/default/js/messages.js',
                'resources/themes/default/sass/app.scss',
                'resources/themes/mobile/js/app.js',
                'resources/themes/mobile/sass/app.scss',
                'resources/themes/motor/js/app.js',
                'resources/themes/motor/sass/app.scss',
                'resources/themes/fresh/js/app.js',
                'resources/themes/fresh/sass/app.scss',
                'resources/themes/matrix/js/app.js',
                'resources/themes/matrix/sass/app.scss',
                'resources/themes/waphack/js/app.js',
                'resources/themes/waphack/sass/app.scss',
                'public/assets/css/chartist.css',
                'public/assets/js/chartist.js',
            ],
            refresh: true,
        }),
    ],
    resolve: {
        alias: {
            js: path.resolve(__dirname, 'public/assets/js'),
            css: path.resolve(__dirname, 'public/assets/css'),
            themes: path.resolve(__dirname, 'resources/themes'),
            fa: path.resolve(__dirname, 'node_modules/@fortawesome/fontawesome-free'),
        },
    },
    css: {
        preprocessorOptions: {
            scss: {
                silenceDeprecations: ['import'],
                quietDeps: true,
            },
        },
    },
    build: {
        rollupOptions: {
            output: {
                assetFileNames: (assetInfo) => {
                    if (assetInfo.name.match(/\.(woff|woff2|ttf)$/)) {
                        return 'fonts/[name][extname]';
                    }
                    return 'assets/[name]-[hash][extname]';
                },
            },
        },
    },
    esbuild: {
        legalComments: 'none'
    },
});
