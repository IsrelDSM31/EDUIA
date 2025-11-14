import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.jsx'],
            refresh: true,
        }),
        react(),
    ],
    server: {
        hmr: {
            host: 'localhost',
        },
        host: 'localhost',
        port: 5173,
        strictPort: true,
        watch: {
            usePolling: true,
        },
    },
    resolve: {
        alias: {
            '@': '/resources/js',
        },
    },
    build: {
        outDir: 'public/build',
        emptyOutDir: true,
        manifest: 'manifest.json', // Generar manifest en la raíz de build
        rollupOptions: {
            input: {
                app: 'resources/js/app.jsx',
            },
        },
    },
});

