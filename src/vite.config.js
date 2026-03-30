import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
    // Thêm cấu hình server dành cho mạng nội bộ
    server: {
        host: '0.0.0.0', 
        hmr: {
            host: '172.16.0.229', 
        },
    },
});
