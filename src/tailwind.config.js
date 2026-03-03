import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                primary: {
                    50:  '#EEF4FF',
                    100: '#DBE8FE',
                    200: '#BFD5FE',
                    300: '#93B8FD',
                    400: '#6090FA',
                    500: '#4366F6',
                    600: '#2D4AEB',
                    700: '#2438D8',
                    800: '#2330AF',
                    900: '#222E8A',
                },
                secondary: {
                    50:  '#F5F3FF',
                    100: '#EDE9FE',
                    200: '#DDD6FE',
                    300: '#C4B5FD',
                    400: '#A78BFA',
                    500: '#7C3AED',
                    600: '#6D28D9',
                    700: '#5B21B6',
                    800: '#4C1D95',
                    900: '#3B0F7A',
                },
                sidebar: {
                    DEFAULT: '#1E293B',
                    light:   '#334155',
                    dark:    '#0F172A',
                    text:    '#94A3B8',
                    active:  '#4366F6',
                },
                surface: {
                    DEFAULT: '#FFFFFF',
                    muted:   '#F8FAFC',
                    hover:   '#F1F5F9',
                },
                border: {
                    DEFAULT: '#E2E8F0',
                    dark:    '#CBD5E1',
                },
                background: '#F1F5F9',
                success: {
                    50:  '#ECFDF5',
                    500: '#10B981',
                    600: '#059669',
                },
                warning: {
                    50:  '#FFFBEB',
                    500: '#F59E0B',
                    600: '#D97706',
                },
                danger: {
                    50:  '#FEF2F2',
                    500: '#EF4444',
                    600: '#DC2626',
                },
            },
            boxShadow: {
                'card': '0 1px 3px 0 rgba(0, 0, 0, 0.08), 0 1px 2px -1px rgba(0, 0, 0, 0.04)',
                'card-hover': '0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -2px rgba(0, 0, 0, 0.06)',
                'sidebar': '2px 0 8px rgba(0, 0, 0, 0.12)',
            },
            borderRadius: {
                'card': '0.75rem',
            },
        },
    },

    plugins: [forms],
};
