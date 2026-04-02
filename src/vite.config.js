import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/question.js',
                'resources/js/pages/common/welcome.js',
                'resources/js/pages/lecturer/classes-show.js',
                'resources/js/pages/lecturer/complaints-index.js',
                'resources/js/pages/lecturer/exams-create-form.js',
                'resources/js/pages/lecturer/exams-toggle-late.js',
                'resources/js/pages/lecturer/schedules-create.js',
                'resources/js/pages/lecturer/schedules-index.js',
                'resources/js/pages/student/classes-show.js',
                'resources/js/pages/student/exams-room.js'
            ],
            refresh: true,
        }),
    ],
});
