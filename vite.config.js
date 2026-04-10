import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import path from 'path';
import { compression } from 'vite-plugin-compression2';

export default defineConfig({
    plugins: [
        ...(process.env.BROTLI ? [compression({ algorithm: 'brotliCompress' })] : []),
        laravel({
            input: [
                'public/assets/js/jquery.js',
                'resources/themes/vendor.scss',
                'resources/themes/default/js/app.js',
                'resources/themes/mobile/js/app.js',
                'resources/themes/motor/js/app.js',
                'resources/themes/fresh/js/app.js',
                'resources/themes/matrix/js/app.js',
                'resources/themes/waphack/js/app.js',
                'resources/themes/cyberpunk/js/app.js',
                'resources/themes/nordic/js/app.js',
                'resources/themes/newspaper/js/app.js',
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
        emptyOutDir: true,
        rollupOptions: {
            checks: {
                pluginTimings: false,
            },
            output: {
                assetFileNames: (assetInfo) => {
                    if (assetInfo.name?.match(/\.(woff|woff2|ttf)$/)) {
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
