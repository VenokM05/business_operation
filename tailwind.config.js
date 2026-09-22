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
            colors: {
                surface: 'hsl(var(--surface) / <alpha-value>)',
                ink: 'hsl(var(--ink) / <alpha-value>)',
                muted: 'hsl(var(--muted) / <alpha-value>)',
                line: 'hsl(var(--line) / <alpha-value>)',
                brand: 'hsl(var(--brand) / <alpha-value>)',
                success: 'hsl(var(--success) / <alpha-value>)',
                warning: 'hsl(var(--warning) / <alpha-value>)',
                danger: 'hsl(var(--danger) / <alpha-value>)',
            },
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
        },
    },

    plugins: [forms],
};
