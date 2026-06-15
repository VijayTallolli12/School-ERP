import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { visualizer } from 'rollup-plugin-visualizer';

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
    ],
    build: {
        rollupOptions: {
            output: {
                manualChunks: {
                    jquery: ['jquery'],
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
