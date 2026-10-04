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
                'sf-bg': '#384B70',
                'sf-surface': '#507687',
                'sf-surface-light': '#64889A',
                'sf-border': '#718C99',
                'sf-blue': { DEFAULT: '#B8001F', dark: '#940019' },
                'sf-text': '#FCFAEE',
                'sf-muted': '#D4DFDF',
            },
            boxShadow: {
                'glow-blue': '0 0 24px rgba(184, 0, 31, 0.25)',
                'glow-blue-lg': '0 0 40px rgba(184, 0, 31, 0.3)',
            },
        },
    },
    plugins: [forms],
};
