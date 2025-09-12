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
                'public/themes/default/src/js/app.js',
                'public/themes/default/src/js/messages.js',
                'public/themes/default/src/sass/app.scss',
                'public/themes/mobile/src/js/app.js',
                'public/themes/mobile/src/sass/app.scss',
                'public/themes/motor/src/js/app.js',
                'public/themes/motor/src/sass/app.scss',
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
            themes: path.resolve(__dirname, 'public/themes'),
            resources: path.resolve(__dirname, 'resources'),
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
