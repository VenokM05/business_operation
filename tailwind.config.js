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
                canvas: 'hsl(var(--canvas) / <alpha-value>)',
                surface: 'hsl(var(--surface) / <alpha-value>)',
                ink: 'hsl(var(--ink) / <alpha-value>)',
                muted: 'hsl(var(--muted) / <alpha-value>)',
                line: 'hsl(var(--line) / <alpha-value>)',
                brand: {
                    DEFAULT: 'hsl(var(--brand) / <alpha-value>)',
                    dark: 'hsl(var(--brand-dark) / <alpha-value>)',
                    light: 'hsl(var(--brand-light) / <alpha-value>)',
                },
                accent: 'hsl(var(--accent) / <alpha-value>)',
                success: 'hsl(var(--success) / <alpha-value>)',
                warning: 'hsl(var(--warning) / <alpha-value>)',
                danger: 'hsl(var(--danger) / <alpha-value>)',
            },
            boxShadow: {
                card: '0 1px 2px 0 rgb(16 24 40 / 0.04), 0 1px 3px 1px rgb(16 24 40 / 0.05)',
                'card-hover': '0 10px 30px -8px rgb(16 24 40 / 0.16), 0 4px 10px -6px rgb(16 24 40 / 0.10)',
                popover: '0 12px 36px -10px rgb(16 24 40 / 0.22)',
            },
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
        },
    },

    plugins: [forms],
};
