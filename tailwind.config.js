import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['var(--font-body)', ...defaultTheme.fontFamily.sans],
                display: ['var(--font-display)', ...defaultTheme.fontFamily.sans],
                mono: ['var(--font-mono)', ...defaultTheme.fontFamily.mono],
            },
        },
    },

    plugins: [forms],
};
