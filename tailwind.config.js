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
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                'sf-bg': '#0A0E17',
                'sf-surface': '#131B2E',
                'sf-surface-light': '#1A2540',
                'sf-border': '#1F2937',
                'sf-blue': { DEFAULT: '#3B5CFC', dark: '#2E4FE0' },
                'sf-text': '#F3F4F6',
                'sf-muted': '#8B95A8',
            },
            boxShadow: {
                'glow-blue': '0 0 24px rgba(59, 92, 252, 0.25)',
                'glow-blue-lg': '0 0 40px rgba(59, 92, 252, 0.35)',
            },
        },
    },
    plugins: [forms],
};
