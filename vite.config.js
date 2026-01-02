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
                'public/assets/themes/default/js/app.js',
                'public/assets/themes/default/js/messages.js',
                'public/assets/themes/default/sass/app.scss',
                'public/assets/themes/mobile/js/app.js',
                'public/assets/themes/mobile/sass/app.scss',
                'public/assets/themes/motor/js/app.js',
                'public/assets/themes/motor/sass/app.scss',
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
            themes: path.resolve(__dirname, 'public/assets/themes'),
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
