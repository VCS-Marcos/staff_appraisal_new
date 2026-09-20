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
            // School brand colours, taken from the logo. "indigo" is remapped to the
            // logo's blue so every existing indigo-* utility picks up the brand theme.
            colors: {
                indigo: {
                    50: '#eff4fc',
                    100: '#dbe6f8',
                    200: '#bfd1f1',
                    300: '#94b0e6',
                    400: '#6288d7',
                    500: '#3c65c3',
                    600: '#244aa5',
                    700: '#1d3d87',
                    800: '#1b336d',
                    900: '#1a2d5a',
                    950: '#111c3a',
                },
                accent: {
                    300: '#f7ee7a',
                    400: '#f2df2f',
                    500: '#e0cc10',
                },
            },
        },
    },

    plugins: [forms],
};
