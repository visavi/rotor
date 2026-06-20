import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import path from 'path';
import fs from 'fs';
import { createHash } from 'crypto';
import { compression } from 'vite-plugin-compression2';

// Собирает переводы для JS из resources/lang/{locale}/main.json
// в public/build/lang/{locale}-{hash}.js как window.translations = {...}
// и дописывает в manifest.json стабильный ключ lang/{locale}.js.
function langPlugin() {
    return {
        name: 'build-lang',
        closeBundle() {
            const srcDir = path.resolve(__dirname, 'resources/lang');
            const outDir = path.resolve(__dirname, 'public/build/lang');
            fs.mkdirSync(outDir, { recursive: true });

            const manifestPath = path.resolve(__dirname, 'public/build/manifest.json');
            const manifest = JSON.parse(fs.readFileSync(manifestPath, 'utf-8'));

            for (const locale of fs.readdirSync(srcDir)) {
                const file = path.join(srcDir, locale, 'main.json');
                if (!fs.existsSync(file)) continue;

                const json = fs.readFileSync(file, 'utf-8').trim();
                const hash = createHash('sha256').update(json).digest('hex').slice(0, 8);

                fs.writeFileSync(path.join(outDir, `${locale}-${hash}.js`), `window.translations = ${json}`);
                manifest[`lang/${locale}.js`] = { file: `lang/${locale}-${hash}.js` };
            }

            fs.writeFileSync(manifestPath, JSON.stringify(manifest, null, 4));
        },
    };
}

export default defineConfig({
    plugins: [
        ...(process.env.BROTLI ? [compression({ algorithm: 'brotliCompress' })] : []),
        laravel({
            input: [
                'resources/themes/vendor.scss',
                'resources/themes/default/js/app.js',
                'resources/themes/mobile/js/app.js',
                'resources/themes/motor/js/app.js',
                'resources/themes/fresh/js/app.js',
                'resources/themes/nordic/js/app.js',
                'resources/themes/newspaper/js/app.js',
                'public/assets/css/chartist.css',
                'public/assets/js/chartist.js',
            ],
            refresh: true,
        }),
        langPlugin(),
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
        // Фикс для старых устройств, чтобы в CSS остался min-width/max-width.
        cssTarget: ['chrome87', 'edge88', 'firefox78', 'safari13'],
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
