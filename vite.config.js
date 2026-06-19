import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { visualizer } from 'rollup-plugin-visualizer';
import { resolve, dirname } from 'path';
import { fileURLToPath } from 'url';
import { readFileSync, writeFileSync, existsSync, readdirSync } from 'fs';

const __dirname = dirname(fileURLToPath(import.meta.url));

const jqueryQfFixPlugin = {
    name: 'vite-jquery-qf-fix',
    closeBundle() {
        const assetsDir = resolve(__dirname, 'public/build/assets');
        if (!existsSync(assetsDir)) return;
        const files = readdirSync(assetsDir).filter(f => f.startsWith('app-') && f.endsWith('.js'));
        for (const file of files) {
            const filePath = resolve(assetsDir, file);
            let code = readFileSync(filePath, 'utf-8');
            const prev = code;
            code = code.replace(/(\w+)\s*=\s*qf\((\w+)\)/, '$1 = $2.default || $2');
            if (code !== prev) {
                writeFileSync(filePath, code, 'utf-8');
            }
        }
    },
};

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        visualizer({
            open: true,
            filename: 'bundle-analysis.html',
        }),
        jqueryQfFixPlugin,
    ],
    resolve: {
        alias: {
            jquery: resolve(__dirname, 'resources/js/jquery-shim.js'),
        },
    },
    build: {
        rollupOptions: {
            output: {
                manualChunks: {
                    datatables: [
                        'datatables.net',
                        'datatables.net-bs5',
                        'datatables.net-responsive-bs5',
                    ],
                    charts: ['chart.js'],
                    alerts: ['sweetalert2'],
                },
            },
        },
    },
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
